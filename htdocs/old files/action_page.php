<?php
// Initialize the session
session_start();
 
// Unset all of the session variables
$_SESSION = array();
 
// Destroy the session.
session_destroy();

//connect to MySQL server
$mysqli = new mysqli("localhost", "meg", "password", "wifi_tracking");

// Check connection
if($mysqli === false){
    die("ERROR: Could not connect. " . $mysqli->connect_error);
}

$email = $_REQUEST['email'];
$password = $_REQUEST['psw'];
$hash = password_hash($password, PASSWORD_BCRYPT);

$sql = "INSERT INTO users (email, password) VALUES ('$email', '$hash')";
if($mysqli->query($sql) === true){
    header("location: Login.php"); /* Redirect browser */
	exit();
} else{
    echo "ERROR: Could not able to execute $sql. " . $mysqli->error;
}

// Close connection
$mysqli->close();
?>