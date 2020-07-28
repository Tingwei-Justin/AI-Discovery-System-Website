<?php
    session_start();
    header('Content-type:text/html;charset=utf-8');
    if(isset($_SESSION['user']))
        $_SESSION['user']=$_SESSION['user']+1;
    else
        $_SESSION['user']=1;

    if(!isset($_SESSION['username']))
        $_SESSION['username']= "Guest (No Login)";

    // Set database credentials in here
    include('credentials.php');
    include('dbHandle.php');
    $db = new dbHandle($db_host,$db_username, $db_password,$db_database);
    $db->openDB();
    $db->createTable('dataset');
    $db->insertData('dataset');
?>


<html>
<head>
    <title>Car Search</title>
    <link rel="stylesheet" href="stylesheet.css">
    <script src="js/jquery-3.3.1.min.js"></script>
</head>
<body>
    <header>
        <h1>Car Search</h1>
    </header>

    <script src ="dynamicUpdate.js" type="text/javascript"></script>
    <?php
        echo "Username: ". $_SESSION['username'];
        echo "<br>";
        echo "Page view：". $_SESSION['user'];
        ?>
    <button type="button" onclick="destroySession()">Clear previous information!</button>
    <br>
    <br>
    <br>

<!--    <iframe name="myiframe" style="display:none;" onload="showSelectedInfo()(this);"></iframe>-->
    <form name = "user_choice" method="POST">
        <div class="select">
            <span>Brand</span>
            <select style="width: 20%;" id="brand" name="brand[]" multiple size="3">
            <?php

                include('credentials.php');
                $db = new dbHandle($db_host,$db_username, $db_password,$db_database);
                $db->openDB();
                // Pick the brand from model string in the database.
                $sql = "SELECT DISTINCT SUBSTRING_INDEX(Model,' ',1)AS Brand FROM dataset"; 
                $rows = $db->selectData($sql);
                foreach ($rows as $row){
            ?>
                <option value="<?php echo $row['Brand'];?>"><?php print(htmlspecialchars($row['Brand']));?></option>
                <?php } ?>
            </select>

            <span>Release Date Range</span>
            <select style="width: 20%;"id="datefrom" name="releaseDateFrom">
                <option value=""selected disabled hidden>From</option>
                <?php
                    // Pick the release date/year from year string in the database.
                    $sql = "SELECT DISTINCT ReleaseDate AS Year FROM dataset GROUP BY Year"; 
                    $rows = $db->selectData($sql);
                    foreach ($rows as $row)
                    { ?>
                        <option value="<?php echo $row['Year'];?>"><?php print(htmlspecialchars($row['Year']));?></option>
                <?php } ?>
            </select>

            <select style="width: 20%;" id="dateto" name="releaseDateTo">
                <option value=""selected disabled hidden>To</option>
                <?php
                // Pick the release date/year from year string in the database.
                $sql = "SELECT DISTINCT ReleaseDate AS Year FROM dataset GROUP BY Year";
                $rows = $db->selectData($sql);
                foreach ($rows as $row)
                {  ?>
                    <option value="<?php echo $row['Year'];?>"><?php print(htmlspecialchars($row['Year']));?></option>
                <?php } ?>
            </select>

            <span>Price Range</span>
            <input style="width: 10%" type="number" id="price_min" name="price_min" placeholder="Minimum Price (£)" min="1"/>
            <input style="width: 10%" type="number" id="price_max" name="price_max" placeholder="Maximum Price (£)"min="1"/>
        </div>
        <br>
        <input type="button" id="select_button" value="submit" name="submit" onclick="showSelectedInfo()">
    </form>

    <p>
        <div id="showClusterData"><b>Cluster data should be shown here</b></div>
    </p>

    <ul class="options">
        <li>
            <img class="carimage" src="img/redcar.png" alt="example img">
                <br>
                <a> Properties sql select </a>
                <a> e.g. name, price</a>
            <button class="chooseButton" type="button" id ="chooseButton1">1st Choice</button></li>
        <li>
                <img src="img/oldcar.png" alt="example img">
                <br>
                <a> Properties sql select </a>
                <a> e.g. model, year</a>               
            <button class="chooseButton" type="button" id ="chooseButton2">2nd Choice</button></li>
        <li>
                <img src="img/newcar.png" alt="example img">
                <br>
                <a> Properties sql select </a>
                <a> e.g. max speed, fuel efficiency </a>
            <button class="chooseButton" type="button" id ="chooseButton3">3rd Choice</button></li>
    </ul>

<?php include 'footer.html';?>
</body>
</html>