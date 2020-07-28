<?php
    // Set db credential information in here
    include('credentials.php');
    include('dbHandle.php');
    $db = new dbHandle($db_host,$db_username, $db_password,$db_database);
    $db->openDB();

    if(isset($_GET['brand']))
    {
        $select_multiple_brand=$_GET['brand'];
        $matches_brand = str_replace(',','|',$select_multiple_brand);
     }
    if(isset($_GET['releaseDateFrom']))
    {
        $data_from = $_GET['releaseDateFrom'];
        if($data_from == "")
            $data_from = 0;
    }
    if(isset($_GET['releaseDateTo']))
    {
        $data_to = $_GET['releaseDateTo'];
        if($data_to == "")
            $data_to = 2100;
    }

    if(isset($_GET['price_min']))
    {
        $min_price = $_GET['price_min'];
        if($min_price == "")
            $min_price = 0;
    }
    if(isset($_GET['price_max']))
    {
        $max_price = $_GET['price_max'];
        if($max_price == "")
            $max_price = 100000000;
    }
    //                                      AND ReleaseDate >= '$data_from'   sever seems not work for this
    //                                          AND ReleaseDate <= '$data_to'
    $sql = "SELECT *FROM dataset WHERE Model REGEXP '$matches_brand'
                                      AND Price >= $min_price
                                      AND Price <= $max_price
                                      AND ReleaseDate >= $data_from
                                      AND ReleaseDate <= $data_to";

    $result = $db->selectDataReturnArray($sql);

    /****** Remove the first column(id) ******/
    // Offset is the start position
    $offset = 0;
    array_walk($result, function (&$v) use ($offset)
    {
        array_splice($v, $offset, 1);
    });

    /****    Save current array in session   ****/
    session_start();
    header('Content-type:text/html;charset=utf-8');
    $_SESSION['user_data']=$result;

    include('debugClusterData.php');


