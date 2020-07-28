<?php

namespace KMeans;

use Exception;

class KMeans
{

    // initial, unmodified data field
    protected $data;

    // array of modified data, multi-dimensional based on cluster_count
    protected $clustered_data;

    // array of centroids based on cluster_count
    protected $centroids;

    // array of centroid distance, useful for testing different cluster_counts
    protected $centroid_distance;

    // acceptable methods for clustering
    protected static $ACCEPTED_CLUSTERING_METHODS =
    [
        'random',
        'forgy',
    ];

    /**
     * basic construct that accepts the initial list of observations
     * exception thrown if data is not large enough for clustering
     *
     * @param  $data  array  list of observations, each observation a same-length list of numeric values
     */
    public function __construct(array $data)
    {
        if (count($data) < 2)
            throw new Exception('Data must have more than one row');

        $this->data = $data;
    }

    /*
     * primary worker for the clustering logic
     * broken out from construct due to processing concerns
     * hydrates the important parameters (clustered data, centroids, etc)
     *
     * @param   $cluster_count  integer  how many clusters to break the data into
     * @param   $method         string   the preferred method for clustering ('random' or 'forgy')
     * @return                  array    clustered data from process (getClusteredData)
     */
    public function cluster($cluster_count, $method = 'forgy')
    {
        if ($cluster_count < 2)
            throw new Exception('Cluster count must be greater than 1');
        if ($cluster_count > count($this->data))
            throw new Exception('Cluster count must be greater than the number of data points');
        if (!in_array($method, self::$ACCEPTED_CLUSTERING_METHODS))
            throw new Exception("Unrecognized method passed into cluster: {$method}");

        do
        {
            if (empty($centroids))
                $centroids = $this->getInitialCentroids($cluster_count, $method);
            else
                $centroids = $this->calculateCentroids($this->clustered_data);

            $new_clustered_data = array_fill(0, $cluster_count, []);
            foreach ($this->data as $observation)
            {
                $closest_centroid = $this->calculateClosestCentroid($observation, $centroids);
                array_push($new_clustered_data[$closest_centroid], $observation);
            }
        } while ($this->assignmentConvergenceCheck((array)$this->clustered_data, $new_clustered_data) === false);

        $this->centroids = $centroids;
        // todo calculate centroid distances

        return $this->getClusteredData();
    }

    /**
     * simple getter to fetch the centroids
     * will throw an exception if centroids have not been set yet
     *
     * @return  array  list of centroids
     */
    public function getCentroids()
    {
        if (empty($this->centroids))
            throw new Exception('Centroids have not been hydrated yet - run cluster method first');

        return $this->centroids;
    }

    /**
     * simple getter to fetch the clustered data
     * will throw an exception if clustered data have not been set yet
     *
     * @return  array  multi-dimensional array of clustered data
     */
    public function getClusteredData()
    {
        if (empty($this->clustered_data))
            throw new Exception('Clustered data have not been hydrated yet - run cluster method first');

        return $this->clustered_data;
    }

    /**
     * simple getter to fetch centroid distance
     * this number is helpful for determining cluster count for repeat runs
     * will throw an exception if cluster has not been run yet
     *
     * @return  array  list of centroid distances
     */
    /*
        public function getCentroidDistance()
        {
            if (empty($this->centroid_distance)) {
                throw new Exception('Centroid distance has not been hydrated yet - run cluster method first');
            }

            return $this->centroid_distance;
        }
    */

    /**
     * contained switch for initialization method
     *
     * @param   $cluster_count  integer  how manu clusters are requested
     * @param   $method         string   type of initialization requested
     * @return                  array    list of centroids for initialization
     */
    protected function getInitialCentroids($cluster_count, $method)
    {
        if ($method == 'forgy')
            return $this->getForgyInitialization($cluster_count);
        if ($method == 'random')
            return $this->getRandomInitialization($cluster_count);
    }

    /**
     * get initialization points from random selection
     * try to lean towards center of data set
     *
     * @param   $cluster_count  integer  number of points to fetch
     * @return                  array    list of initialization points
     */
    protected function getRandomInitialization($cluster_count)
    {
        $random_keys = array_rand($this->data, $cluster_count);
        $random_keys = array_flip($random_keys);
        return array_intersect_key($this->data, $random_keys);
    }

    /**
     * get initialization points from random points in data set
     * tends to spread out points more
     *
     * @param   $cluster_count  integer  number of points to fetch
     * @return                  array    list of initialization points
     */
    protected function getForgyInitialization($cluster_count)
    {
        $data_range = $this->calculateRange($this->data);
        $random_points = [];

        for ($i = 0; $i < $cluster_count; $i++)
        {
            $random_points[$i] = array_fill(0, count(current($this->data))-1, null);  //change from below

            //$random_points[$i] = array_fill(0, count($this->data), null);
            foreach ($data_range as $key => $range)
            {
                $random_points[$i][$key] = ($range['min'] + lcg_value() * ($range['max'] - $range['min']));
            }
        }
        return $random_points;
    }

    /**
     * calculate centroids based on clustered data
     *
     * @param   $clustered_data  array  multi-dimensional array of clustered data
     * @return                   array  list of centroids
     */
    protected function calculateCentroids(array $clustered_data)
    {
        $centroids = [];
        foreach ($clustered_data as $cluster)
        {
            //avoid current cluster is empty, else use count to calculate the number
            if($cluster == null)
            {
                $length = 0;
                continue;
            }
            else
                $length = count(current($cluster))-1;

            $cluster_sum = array_fill(0, $length, 0);
            foreach ($cluster as $observation)
            {
                $count = 0;
                foreach ($observation as $key => $value)
                {
                    if($count == 0)
                    {
                        $count++;
                        continue;
                    }
                    $cluster_sum[$key-1] += $value;  //keep key begin with 0
                }
            }
            $centroid = array_fill(0, $length, 0);
            foreach ($cluster_sum as $key => $value)
            {
                $centroid[$key] = $value / count($cluster); //what if count($cluster == 0)
            }
            array_push($centroids, $centroid);
        }
        return $centroids;
    }

    /**
     * calculate the closest centroid to an observation
     *
     * @param   $observation  array    observation from data set
     * @param   $centroids    array    list of centroids
     * @return                integer  index that observation should be clustered into
     */

    protected function calculateClosestCentroid(array $observation, array $centroids)
    {
        $centroid_distance = [];
        foreach ($centroids as $centroid)
        {
            array_push($centroid_distance, $this->calculateDistance($observation, $centroid));
        }
        asort($centroid_distance);
        $centroid_distance = array_keys($centroid_distance);
        return array_shift($centroid_distance);
    }

    /**
     * check to see if clustered data has converged yet
     * if not, reassing new data to internal holder and return false to re-run script
     *
     * @param   $clustered_data      array    the old holder of clustered_data
     * @param   $new_clustered_data  array    new clustered_data to check against
     * @return                       boolean  whether or not convergence has occurred
     */
    protected function assignmentConvergenceCheck(array $clustered_data, array $new_clustered_data)
    {
        if (empty($clustered_data))
        {
            $this->clustered_data = $new_clustered_data;
            return false;
        }

        foreach ($clustered_data as $key => $cluster)
        {
            foreach ($cluster as $observation)
            {
                if (!in_array($observation, $new_clustered_data[$key]))
                {
                    $this->clustered_data = $new_clustered_data;
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * helper method to get the range of a set of data
     *
     * @param   $data  array  list of points to determine range of
     * @return         array  formatted return of range based on the data
     */
    protected function calculateRange($data)
    {
        $data_range = array_fill(0, count(current($data))-1, ['min' => null, 'max' => null]); //count data: return the number of column
        foreach ($data as $observation)
        {
            $key = 0;
            $count = 0;
            foreach ($observation as $value)
            {
                if($count == 0)  //ignore the first column
                {
                    $count++;
                    continue;
                }
                if ($data_range[$key]['min'] === null || $data_range[$key]['min'] > $value)
                    $data_range[$key]['min'] = $value;
                if ($data_range[$key]['max'] === null || $data_range[$key]['max'] < $value)
                    $data_range[$key]['max'] = $value;
                $key++;
            }
        }

        return $data_range;//return the min and max of each column. e.g.[[1,2], [4,5], [3,9]]  then return [[1,4], [2,9]]
    }

    /**
     * helper method to determine the euclidean distance between two n-dimensional points
     * well, sum of squares, as the actual distance is unneeded - just the relative distance
     *
     * @param   $point_a  array  list of numeric values that determine a point
     * @param   $point_b  array  list of numeric values that determine a point
     * @return            float  distance between the points
     */
    protected function calculateDistance($point_a, $point_b)
    {
        $distance = 0;
        $count = count($point_a)-1;
        for ($i = 0; $i < $count; $i++)
        {
            $difference = $point_a[$i+1] - $point_b[$i];
            $distance += pow($difference, 2);
        }
        return $distance;
    }

    /**
     * determines actual distance between two points
     *
     * @param   $point_a  array  list of numeric values that determine a point
     * @param   $point_b  array  list of numeric values that determine a point
     * @return            float  distance between the points
     */
    protected function calculateActualDistance($point_a, $point_b)
    {
        $distance = 0;
        for ($i = 0, $count = count($point_a)-1; $i < $count; $i++){
            $difference = $point_a[$i+1] - $point_b[$i];
            if ($difference < 0)
                $difference = -($difference);
            $distance += $difference;
        }
    }

    /**
     * method to return a list of the closest values inside each cluster to each cluster centroids,
     * used to display actual values from the data as opposed to a mean value the centroids display.
     *
     * @param   $centroids array list of centroid values that determine cluster centroids
     * @param   $data       array list of all the clustered data
     * @return            array list of the closest values within the clustered data to the centroids
     */
    public function calculateClosestValuesToCentroids($centroids, $data)
    {
        $arraylength = count($data);
        $closestClusterValues = [];

        for ($clusterNumber = 0; $clusterNumber < $arraylength; $clusterNumber++)
        {
            $distance = 100;
            $currentLowestDistance = 100;

            foreach ($data[$clusterNumber] as $clusterToCheck)
            {
                $distance = $this->calculateActualDistance($clusterToCheck, $centroids[$clusterNumber]);
                if ($distance < $currentLowestDistance)
                {
                    $currentLowestDistance = $distance;
                    $closestClusterValue = $clusterToCheck;
                }
            }
            array_push($closestClusterValues, $closestClusterValue);
        }
        return $closestClusterValues;
    }

    /**
     * computes the difference between the 2 input multidimensional arrays and returns a new array with the result
     *
     * @param   $array1   array list of source array to check the difference of
     * @param   $array2   array list of a secondary array to check the difference of
     * @return            array result of the absolute distance
     */
    public function check_diff_multi($array1, $array2){
        $result = array();
        foreach($array1 as $key => $val)
        {
            if(isset($array2[$key]))
                if(is_array($val) && $array2[$key])
                    $result[$key] = $this->check_diff_multi($val, $array2[$key]);
            else
                $result[$key] = $val;
        }

        return $result;
    }

    /**
     * Re-indexes the array to avoid 'gaps' in the array where there are null values from previous processing of the array
     *
     * @param   $arr      array to be reindexed
     * @return            array of reindexed input array
     */
    function array_purge_empty($arr)
    {
        $newarr = array();
        foreach ($arr as $key => $val)
        {
            if (is_array($val))
            {
                $val = $this->array_purge_empty($val);
                if (count($val) != 0)
                    $newarr[$key] = $val;
            }
            else
            {
                if (trim($val) != '')
                    $newarr[$key] = $val;
            }
        }
        return $newarr;
    }

    /**
     * removes array entries from another array
     *
     * @param   $clusters   array of clusters to have entries removed from
     * @param   $clustersNotChosen   array of clusters to remove from the main clusters
     * @return            array result of the difference of the arrays
     */
    public function removeClustersNotChosen($clusters, $clustersNotChosen)
    {
        $diff = $this->check_diff_multi($clusters, $clustersNotChosen);
        $diff = array_filter($diff);
        $diff = $this->array_purge_empty($diff);
        return $diff;
    }

    /**
     * calculates the percentage that a cluster occupies of the whole dataset
     *
     * @param   $cluster  array - cluster to be checked against the dataset
     * @param   $clusteredData array - complete clustered data for this iteration, used to check against the $cluster parameter
     * @param   $precision  int - the number of decimal points to return
     * @return            float/double result of the percentage calculated (not in decimal for such as 0.3 for 30%, but 30 for 30%)
     */
    public function returnClusterPercentage($cluster, $clusteredData, $precision)
    {
        $clusterCount = count($cluster);
        $dataCount = 0;
        foreach ($clusteredData as $dataCluster)
        {
            $dataCount += count($dataCluster);
        }
        return round((($clusterCount / $dataCount) * 100), $precision);
    }
}

