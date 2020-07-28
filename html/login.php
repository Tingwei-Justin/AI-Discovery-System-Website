<?php
/**
 * Created by PhpStorm.
 * User: Justin_Joe
 * Date: 5/1/2018
 * Time: 2:38 PM
 */

    if(isset($_GET["id"]) && $_GET["id"]=="login")
    {
        session_start();
        if(isset($_SESSION["username"]))
        {
            sleep(1);
            echo "Hello, ".$_SESSION["username"];
        }
        else echo"nothing";

        return;
    }

	//check the illegal user login the website without set submit, username and password
	if(!isset($_POST["submit"]) || !isset($_POST["username"]) || !isset($_POST["password"]))
	{
		echo "<script>alert('Illegal login!'); history.go(-1);</script>";
		die();
	}
	if($_POST["username"] == "")
	{
		echo "<script>alert('The username can not be empty!'); history.go(-1);</script>";
		die();
	}
	if($_POST["password"] == "")
	{
		echo "<script>alert('The password can not be empty!'); history.go(-1);</script>";
		die();
	}

	//filter the data to protect the database
	$username = filter_var($_POST["username"], FILTER_SANITIZE_STRING);
	$password = filter_var($_POST["password"], FILTER_SANITIZE_STRING);

	include("credentials.php");

	$dsn = 'mysql:dbname='.$db_database.';host='.$db_host;
	$pdo = new PDO($dsn,$db_username,$db_password);
	$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

	$stmt = $pdo->prepare('SELECT UserName FROM Users 
									WHERE binary UserName = :username AND binary Password = :password');
	$stmt->bindParam(':username', $username);
	$stmt->bindParam(':password', $password);
	$stmt->execute();
	$user = $stmt->fetch();

	if($user) //find the username and password in the database Users table
	{
		session_start();
		$_SESSION['username']=$username;
		header("Location:index.html#what%20in%20view");
        echo '<script type="text/javascript">var name = "<?php echo $username ?>";login(name);</script>';
	}
	else
	{
		/*To check the reason is password error or not exist username*/
		$stmt1 = $pdo->prepare('SELECT UserName FROM Users WHERE binary UserName = :username');
		$stmt1->bindParam(':username', $username);
		$stmt1->execute();
		$username = $stmt1->fetch();
		if(!$username)
		{
			echo "<script>alert('The username not exists!'); history.go(-1);</script>";
			die();
		}
		else
		{
			echo "<script>alert('Error password!'); history.go(-1);</script>";
			die();
		}
	}

