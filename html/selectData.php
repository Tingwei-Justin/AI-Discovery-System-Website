<?php
    session_start();
    header('Content-type:text/html;charset=utf-8');
    // Set db credential information in here
    include('credentials.php');
    include('dbHandle.php');
    $db = new dbHandle($db_host,$db_username, $db_password,$db_database);
    $db->openDB();
    $db->createTable('dataset');
    $db->insertData('dataset');
    $db->createTable('car');
    $db->insertData('car');
    if($_GET['item'] == "camera")
    {
        $sql = "SELECT *FROM dataset";
        $_SESSION['item_type']="camera";
    }
    else if($_GET['item'] == "car")
    {
        $sql = "SELECT *FROM car";
        $_SESSION['item_type']="car";
    }


    $_SESSION['data_type']=$_GET['item'];
    $result = $db->selectDataReturnArray($sql);
    
    /****** Remove the first column(id) ******/
    // Offset is the start position
    $offset = 0;
    array_walk($result, function (&$v) use ($offset) {
        array_splice($v, $offset, 1);
    });
    
    /****    Save current array in session   ****/

    $_SESSION['user_data']=$result;

    include('clusterData.php');