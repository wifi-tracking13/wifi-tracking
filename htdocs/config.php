<?php

// Database credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'meg');
define('DB_PASSWORD', 'password');
define('DB_NAME', 'wifi_tracking');
 
/* Attempt to connect to MySQL database */
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Check connection
if($mysqli === false){
    die("ERROR: Could not connect. " . $mysqli->connect_error);
}
?>