<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    if ($_SESSION["admin"] == true) {
		header("location: admin/home.php");
		exit;
	}
}

require_once "../config.php";
$id=$_REQUEST['id'];
$query = "DELETE FROM registered_macs WHERE id=$id";
if($mysqli->query($query) === true){
	header("location: home.php");
	exit;
} else {
	echo "ERROR: Could not able to execute $sql. " . $mysqli->error;
}
?>