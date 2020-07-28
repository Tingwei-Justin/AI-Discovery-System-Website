<?php

function var_debug($variable,$strlen=100,$width=25,$depth=10,$i=0,&$objects = array())
{
    $search = array("\0", "\a", "\b", "\f", "\n", "\r", "\t", "\v");
    $replace = array('\0', '\a', '\b', '\f', '\n', '\r', '\t', '\v');

    $string = '';

    switch(gettype($variable)) {
        case 'boolean':      $string.= $variable?'true':'false'; break;
        case 'integer':      $string.= $variable;                break;
        case 'double':       $string.= $variable;                break;
        case 'resource':     $string.= '[resource]';             break;
        case 'NULL':         $string.= "null";                   break;
        case 'unknown type': $string.= '???';                    break;
        case 'string':
            $len = strlen($variable);
            $variable = str_replace($search,$replace,substr($variable,0,$strlen),$count);
            $variable = substr($variable,0,$strlen);
            if ($len<$strlen) $string.= '"'.$variable.'"';
            else $string.= 'string('.$len.'): "'.$variable.'"...';
            break;
        case 'array':
            $len = count($variable);
            if ($i==$depth) $string.= 'array('.$len.') {...}';
            elseif(!$len) $string.= 'array(0) {}';
            else
            {
                $keys = array_keys($variable);
                $spaces = str_repeat(' ',$i*2);
                $string.= "array($len)\n".$spaces.'{';
                $count=0;
                foreach($keys as $key)
                {
                    if ($count==$width)
                    {
                        $string.= "\n".$spaces."  ...";
                        break;
                    }
                    $string.= "\n".$spaces."  [$key] => ";
                    $string.= var_debug($variable[$key],$strlen,$width,$depth,$i+1,$objects);
                    $count++;
                }
                $string.="\n".$spaces.'}';
            }
            break;
        case 'object':
            $id = array_search($variable,$objects,true);
            if ($id!==false)
                $string.=get_class($variable).'#'.($id+1).' {...}';
            else if($i==$depth)
                $string.=get_class($variable).' {...}';
            else
            {
                $id = array_push($objects,$variable);
                $array = (array)$variable;
                $spaces = str_repeat(' ',$i*2);
                $string.= get_class($variable)."#$id\n".$spaces.'{';
                $properties = array_keys($array);
                foreach($properties as $property)
                {
                    $name = str_replace("\0",':',trim($property));
                    $string.= "\n".$spaces."  [$name] => ";
                    $string.= var_debug($array[$property],$strlen,$width,$depth,$i+1,$objects);
                }
                $string.= "\n".$spaces.'}';
            }
            break;
    }

    if ($i>0) return $string;

    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    do $caller = array_shift($backtrace); while ($caller && !isset($caller['file']));
    if ($caller) $string = $caller['file'].':'.$caller['line']."\n".$string;

    echo nl2br(str_replace(' ','&nbsp;',htmlentities($string)));
}

function cluster()
{
    try
    {
        $kmeans = new KMeans\KMeans($GLOBALS['array']);
        $kmeans->cluster(9);
        $clustered_data = $kmeans->getClusteredData();
        $centroids = $kmeans->getCentroids();
        echo '<br><br>'.'Iteration '.$GLOBALS['j'].'<br>';
        $GLOBALS['kmeans'] = $kmeans;
        $GLOBALS['j']++;
        $GLOBALS['clustered_data'] = $clustered_data;
        $GLOBALS['centroids'] = $centroids;
        $GLOBALS['percentagesArray'] = array();
        foreach ($clustered_data as $currCluster)
        {
            $currPercentage = $kmeans->returnClusterPercentage($currCluster, $clustered_data, 2);
            echo 'Cluster Percentage: ' . $currPercentage . '<br>';
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

/****   Save current array in session   ****/

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
    //array_push($_SESSION['user_data'], $_SESSION['recluster_data'][0][0]);
    foreach ($_SESSION['recluster_data'] as $reclusterIndex)
    {
        foreach ($reclusterIndex as $reclusterData)
            array_push($_SESSION['user_data'], $reclusterData);
    }
    $array = $_SESSION['user_data'];
    echo "Final Result = ".$_GET['id'];
}
include('KMeans.php');

/****** Print all possible data ******/
echo '<br>';
echo '<h3>Printing Chosen/All Data</h3>';
$dictionaryArray = array();
if (($handle = fopen("CameraDictionary.csv", "r")) !== FALSE)
{
    $dictionaryArray[] = fgetcsv($handle, 2000, ";");
    fclose($handle);
    $elementCount = count($dictionaryArray[0]);
}
echo '<table border="1">';
echo '<tr>';
for($i = 0; $i < $elementCount; $i++)
{
    echo '<th>'.$dictionaryArray[0][$i].'</th>';
}
echo '</tr>';
foreach( $array as $row )
{
    echo '<tr>';
    foreach( $row as $key )
    {
        echo '<td>'.$key.'</td>';
    }
    echo '</tr>';

}
echo '</table>';
echo "<br>";
echo "<br>";

if(count($array)>=9)
{
    /*******    CLUSTERING & FETCHING RESULTS   ******/
    $j = 0;
    $kmeans;
    $clustered_data = array();
    $percentagesArray = array();
    $originalClusteredData = array();
    $centroids = array();
    $tempClusterArray = array();
    $tempReClusterArray = array();
    $tempCentroidArray = array();
    $tempReCentroidArray = array();
    echo '<br><h3>Printing Algorithm Iterations: </h3>';
    $starttime = microtime(true);
    cluster();
    echo "Elapsed time is: ". (microtime(true) - $starttime) . " seconds";
    echo '<br><h3>Printing Cluster Percentages: </h3>';
    var_debug($percentagesArray);
    echo '<br><br>';
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
    echo '<br> <h4>Cluster Nodes:</h4> ';
    $i = 0;
    foreach ($tempClusterArray as $currCluster)
    {
        $currPercentage = $kmeans->returnClusterPercentage($currCluster, $clustered_data, 2);
        echo '<br>'.'Cluster '.$i.' = '.$currPercentage.'%<br>';
        $i++;
    }
    echo '<br>';

    echo '<br> <h4>Recluster Nodes:</h4> ';

    foreach ($tempReClusterArray as $currCluster)
    {
        $currPercentage = $kmeans->returnClusterPercentage($currCluster, $clustered_data, 2);
        echo '<br>'.'Cluster '.$i.' = '.$currPercentage.'%<br>';
        $i++;
    }
    echo '<br>';
    $clustered_data = $tempClusterArray;
    $centroids = $tempCentroidArray;
    $first = $kmeans->calculateClosestValuesToCentroids($centroids, $clustered_data);

    // Save current cluster array in session
    $_SESSION['cluster_data'] = $tempClusterArray;
    $_SESSION['recluster_data'] = $tempReClusterArray;

    /*****  Print Tables  ******/

    echo '<h3>Printing Centroid Values</h3>';
    echo '<table border="1">';
    echo '<tr>';
    for($i = 1; $i < $elementCount; $i++)
    {
        echo '<th>'.$dictionaryArray[0][$i].'</th>';
    }
    echo '</tr>';

    foreach( $centroids as $centroidData )
    {
        echo '<tr>';
        foreach( $centroidData as $key )
        {
            echo '<td>'.$key.'</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
    echo "<br>";


    echo '<h3>Printing Closest Values to Centroids (User Choices)</h3>';
    echo '<table border="1">';
    echo '<tr>';
    for($i = 0; $i < $elementCount; $i++)
    {
        echo '<th>'.$dictionaryArray[0][$i].'</th>';
    }
    echo '</tr>';

    foreach( $first as $closestValues )
    {
        echo '<tr>';
        foreach( $closestValues as $key )
        {
            echo '<td>'.$key.'</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
}

