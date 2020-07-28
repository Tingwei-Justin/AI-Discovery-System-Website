<?php

    // For debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    include("credentials.php");
    // Check whether the user clicks submit
    if(!isset($_POST["submit"]))
    {
        echo "<script>alert('Illegal registration or Account information saved!'); history.go(-1);</script>";
        die();
    }
    if (isset($_POST["Username"]) and isset($_POST["Password1"]) and isset($_POST["Password2"]))
    {
        // Ensure username length >= 3 and < 30
        $name = filter_var($_POST["Username"], FILTER_SANITIZE_STRING);
        if(!(strlen($name) > 2 and strlen($name) < 30)){
            echo "<script>alert('Username length must be between 3 and 30 characters.'); history.go(-1);</script>";
            exit();
        }

        // Ensure password length >= 6 and < 30
        $password1 = $_POST["Password1"];
        $password2 = $_POST["Password2"];
        if($password1!=$password2)
        {
            echo "<script>alert('Provided passwords do not match.'); history.go(-1);</script>";
            exit();
        }
        else if(!(strlen($password1) > 5 and strlen($password1) < 30)){
            echo "<script>alert('Password length must be between 6 and 30 characters.'); history.go(-1);</script>";
            exit();
        }

        try
        {
            // Our cs-linux specific connection string
            $dsn = 'mysql:dbname='.$db_database.';host='.$db_host;
            // Open a connection using our credentials
            $pdo = new PDO($dsn,$db_username,$db_password);
            // Enable exceptions (so we can identify failures)
            $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $stmt = $pdo->prepare('SELECT * FROM Users WHERE binary UserName = :username');

            $stmt->bindParam(':username', $name);
            $stmt->execute();
            $user = $stmt->fetch();
            if($user)
            {
                echo "<script>alert('Username entered already exists!'); history.go(-1);</script>";
                exit(1);
            }

            // Our prepared statement for inserting Users
            $stmt = $pdo->prepare("INSERT INTO Users (UserName, Password)VALUES (:name, :password)");
            // Bind our values to the specified parameters
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':password', $password1);
            // Execute query
            $stmt->execute();
            // If there are no exceptions, then the code will reach here - success
            echo "<script>alert('Successfully Registered!'); history.go(-1);</script>";
        }
        catch (Exception $e)
        {
            echo $e;
            exit(1);
        }
    }