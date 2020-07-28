<?php
session_start();

$dictionaryArray = array();
if($_SESSION['data_type'] == "camera")
{
    if (($handle = fopen("CameraDictionary.csv", "r")) !== FALSE)
    {
        $dictionaryArray[] = fgetcsv($handle, 2000, ";");
        fclose($handle);
        $elementCount = count($dictionaryArray[0]);
    }
}
else if($_SESSION['data_type'] == "car")
{
    if (($handle = fopen("Cars.csv", "r")) !== FALSE)
    {
        $dictionaryArray[] = fgetcsv($handle, 2000, ",");
        fclose($handle);
        $elementCount = count($dictionaryArray[0]);
    }
}

echo '<br> <br> <table class="text-center table-bordered" border=1 width=100%>';
echo '<thead class="thead-dark">';
echo '<tr class="table-active">';

for($i = 0; $i < count($dictionaryArray[0]); $i++)
{
    echo '<th class="text-center">'.$dictionaryArray[0][$i].'</th>';
}
echo '</tr>';
echo '</thead>';

if(isset($_GET['id']))
{
    $item_array;
    if($_GET['id'] == 1)
        $item_array = $_SESSION['cluster1'];
    else if($_GET['id'] == 2)
        $item_array = $_SESSION['cluster2'];
    else if($_GET['id'] == 3)
        $item_array = $_SESSION['cluster3'];
    else if($_GET['id'] == 4)
    {
        echo '<tr>';
        foreach( $_SESSION['cluster1'] as $key )
        {
            echo '<td>'.$key.'</td>';
        }
        echo '</tr>';


        if($_SESSION['cluster2']!="")
        {
            echo '<tr>';
            foreach( $_SESSION['cluster2'] as $key )
            {
                echo '<td>'.$key.'</td>';
            }
            echo '</tr>';
        }
        if($_SESSION['cluster3']!="")
        {
            echo '<tr>';
            foreach( $_SESSION['cluster3'] as $key )
            {
                echo '<td>'.$key.'</td>';
            }
            echo '</tr>';
        }

        return;
    }


    echo '<tr>';
    foreach( $item_array as $key )
    {
        echo '<td>'.$key.'</td>';
    }
    echo '</tr>';
    echo '</table>';
    echo "<br>";
}
