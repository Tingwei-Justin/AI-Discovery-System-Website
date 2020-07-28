<?php

class dbHandle
{

    private $db_host;
    private $db_username;
    private $db_password;
    private $db_database;

    /**
     * @var PDO
     */
    var $pdo;

    /**
     * dbHandle constructor.
     * @param $host: database host name
     * @param $name: username
     * @param $password: password
     * @param $database: database name
     */
    public function __construct($host, $name, $password, $database)
    {
        $this->db_host = $host;
        $this->db_username = $name;
        $this->db_password = $password;
        $this->db_database = $database;
    }


    /**
     *
     */
    function openDB()
    {
        try
        {
            // Setup the Connection
            $this->pdo = new PDO("mysql:host=$this->db_host;dbname=$this->db_database", $this->db_username, $this->db_password);
            // Setup the error reporting service
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * @param $tablename: mysql table name
     *
     */
    public function createTable($tablename)
    {
        try
        {
            if($tablename == "dataset")
            {
                $sql ="CREATE TABLE IF NOT EXISTS $tablename(
                 ID INT( 11 ) AUTO_INCREMENT PRIMARY KEY,
                 Model VARCHAR( 255 ) NOT NULL,
                 ReleaseDate YEAR NOT NULL,
                 MaxResolution DOUBLE NOT NULL,
                 LowResolution DOUBLE NOT NULL,
                 EffectivePixels DOUBLE NOT NULL,
                 ZoomWide DOUBLE NOT NULL,
                 ZoomTele DOUBLE NOT NULL,
                 NormalFocusRange DOUBLE NOT NULL,
                 MacroFocusRange DOUBLE NOT NULL,
                 StorageIncluded DOUBLE NOT NULL,
                 Weight DOUBLE NOT NULL,
                 Dimensions DOUBLE NOT NULL,
                 Price DOUBLE NOT NULL);" ;
                $this->pdo->exec($sql);
            }
            else if($tablename == "car")
            {
                $sql ="CREATE TABLE IF NOT EXISTS $tablename(
                 ID INT( 11 ) AUTO_INCREMENT PRIMARY KEY,
                 Model VARCHAR( 255 ) NOT NULL,
                 MPG DOUBLE NOT NULL,
                 Cylinders DOUBLE NOT NULL,
                 Displacement DOUBLE NOT NULL,
                 Horsepower DOUBLE NOT NULL,
                 Weight DOUBLE NOT NULL,
                 Acceleration DOUBLE NOT NULL,
                 Year YEAR NOT NULL);" ;
                $this->pdo->exec($sql);
            }
            $sql="CREATE TABLE IF NOT EXISTS Users
            (
                UserName VARCHAR(30) NOT NULL PRIMARY KEY,
                Password VARCHAR(30) NOT NULL
            );";
            $this->pdo->exec($sql);

        }
        catch(PDOException $e) 
        {
            echo $e->getMessage();
        }
    }
    public function insertData($tablename)
    {
        try
        {
            $result= $this->pdo->query("SELECT * FROM $tablename");
            // If there is no data in table then load data from file
            if(!$result->rowCount()) 
            {
                if($tablename == "dataset")
                {
                    $sql = "LOAD DATA INFILE '/var/lib/mysql-files/Camera.csv'
                        INTO TABLE dataset
                        FIELDS TERMINATED BY ';'
                        LINES TERMINATED BY '\n'
                        (Model, ReleaseDate, MaxResolution, LowResolution, LowResolution, 
                        ZoomWide, ZoomTele, NormalFocusRange, MacroFocusRange, StorageIncluded, Weight, Dimensions, Price)";
                }
                else if($tablename == "car")
                {
                    $sql = "LOAD DATA INFILE 'D:/Documents/School/Uni/Year 2/G52GRP/Git/AI-Discovery-System-Website/html/Cars.csv'
                        INTO TABLE car
                        FIELDS TERMINATED BY ','
                        LINES TERMINATED BY '\n'
                        IGNORE 1 LINES
                        (Model, MPG, Cylinders, Displacement, Horsepower, Weight, Acceleration, Year)";
                }
                $this->pdo->exec($sql);
                //print("Inserted csv file to table successfully!.\n");
            }
        }
        catch(PDOException $e) 
        {
            echo $e->getMessage();
        }
    }

    public function selectData($sql)
    {
        try
        {
            $result= $this->pdo->query($sql);
            return $result;
        }
        catch(PDOException $e)
        {
            echo  $e->getMessage();
        }
    }

    public function selectDataReturnArray($sql)
    {
        try
        {
            $sth= $this->pdo->prepare($sql);
            $sth->execute();
            $result = $sth->fetchAll(PDO::FETCH_NUM);
            return $result;
        }
        catch(PDOException $e)
        {
            echo  $e->getMessage();
        }
    }
}