<?php

    /****   Save current array in session   ****/
    function cluster()
    {
        try
        {
            $kmeans = new KMeans\KMeans($GLOBALS['array']);
            $kmeans->cluster(9);
            $clustered_data = $kmeans->getClusteredData();
            $centroids = $kmeans->getCentroids();
            $GLOBALS['kmeans'] = $kmeans;
            $GLOBALS['j']++;
            $GLOBALS['clustered_data'] = $clustered_data;
            $GLOBALS['centroids'] = $centroids;
            $GLOBALS['percentagesArray'] = array();
            foreach ($clustered_data as $currCluster)
            {
                $currPercentage = $kmeans->returnClusterPercentage($currCluster, $clustered_data, 2);
                array_push($GLOBALS['percentagesArray'], $currPercentage);
            }
            arsort($GLOBALS['percentagesArray']);
            $temp = $GLOBALS['percentagesArray'];
            $temp = array_slice($temp, 0, 3);
            foreach ($temp as $key => $percentage)
            {
                if ($percentage < 1 && $GLOBALS['j'] < 10)
                    cluster();
            }
        }
        catch(Exception $e)
        {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }

    function xucwords($string)
    {
        $words = explode(" ", $string);
        $newString = array();

        foreach ($words as $word)
        {
            if(!preg_match("/^m{0,4}(cm|cd|d?c{0,3})(xc|xl|l?x{0,3})(ix|iv|v?i{0,3})$/", $word))
                $word = ucfirst($word);
            else
                $word = strtoupper($word);

            array_push($newString, $word);
        }

        return join(" ", $newString);
    }

    if(!isset($_SESSION))
        session_start();
    header('Content-type:text/html;charset=utf-8');
    while(!isset($_SESSION['user_data']))
    {
        // Wait for data
    }

    // If no previous cluster data
    if(!isset($_GET['id']))
        $array = $_SESSION['user_data'];
    else
    {
        $array_index = $_GET['id']-1;
        // Choose the selected cluster as the user_data
        $_SESSION['user_data'] = $_SESSION['cluster_data'][$array_index];
        foreach ($_SESSION['recluster_data'] as $reclusterIndex)
        {
            foreach ($reclusterIndex as $reclusterData)
                array_push($_SESSION['user_data'], $reclusterData);
        }
        $array = $_SESSION['user_data'];
    }
    include('KMeans.php');

    if(count($array)>=9)
    {
        /*******    CLUSTERING & FETCHING RESULTS   ******/
        $j = 0;
        $kmeans = new KMeans\KMeans($array);
        $clustered_data = array();
        $percentagesArray = array();
        $originalClusteredData = array();
        $centroids = array();
        $tempClusterArray = array();
        $tempReClusterArray = array();
        $tempCentroidArray = array();
        $tempReCentroidArray = array();
        cluster();
        $originalClusteredData = $clustered_data;
        $originalCentroids = $centroids;
        $i = 0;
        foreach ($percentagesArray as $key => $percentage)
        {
            if ($i < 3)
            {
                array_push($tempClusterArray, $originalClusteredData[$key]);
                if ($percentage != 0)
                    array_push($tempCentroidArray, $originalCentroids[$key]);
                else
                    array_push($tempCentroidArray, 0);
            }
            else
            {
                array_push($tempReClusterArray, $originalClusteredData[$key]);
                if ($percentage != 0)
                    array_push($tempReCentroidArray, $originalCentroids[$key]);
                else
                    array_push($tempReCentroidArray, 0);
            }
            $i++;
        }

        $clustered_data = $tempClusterArray;
        $centroids = $tempCentroidArray;
        $first = $kmeans->calculateClosestValuesToCentroids($centroids, $clustered_data);
        updateClusterImgName($first);

        // Save current cluster array in session
        $_SESSION['cluster_data'] = $tempClusterArray;
        $_SESSION['recluster_data'] = $tempReClusterArray;
    }
    else
    {
            if(count($array) ==1 )
            {
                $_SESSION['cluster1']= $array[0];
                $_SESSION['cluster2']= "";
                $_SESSION['cluster3']= "";
                echo "Final: ".xucwords($array[0][0])."        ";
            }
            else if(count($array) ==2)
            {
                $_SESSION['cluster1']= $array[0];
                $_SESSION['cluster2']= $array[1];
                $_SESSION['cluster3']= "";
                echo "Final: ".xucwords($array[0][0])."        ,Final: ".xucwords($array[1][0])."        ";
            }
            else
            {
                $_SESSION['cluster1']= $array[0];
                $_SESSION['cluster2']= $array[1];
                $_SESSION['cluster3']= $array[2];
                echo "Final: ".xucwords($array[0][0])."        ,Final: ".xucwords($array[1][0])."        ,Final: ".xucwords($array[2][0])."        ";
            }

    }


    function updateClusterImgName($array)
    {
        $_SESSION['cluster1']= $array[0];
        $_SESSION['cluster2']= $array[1];
        $_SESSION['cluster3']= $array[2];
        if($_SESSION['item_type'] == "camera")
            echo xucwords($_SESSION['cluster1'][0])." camera ,". xucwords($_SESSION['cluster2'][0])." camera ,". xucwords($_SESSION['cluster3'][0])." camera ";
        else if($_SESSION['item_type'] == "car")
            echo xucwords($_SESSION['cluster1'][0]." ".$_SESSION['cluster1'][7])." car,". xucwords($_SESSION['cluster2'][0]." ".$_SESSION['cluster1'][7])." car,". xucwords($_SESSION['cluster3'][0]." ".$_SESSION['cluster1'][7])."car";
    }
