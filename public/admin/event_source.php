<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    if ($_SESSION["admin"] == false) {
		header("location: ../home.php");
		exit;
	}
}

// Include database connection access
include "../../config.php";

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache'); //to prevent caching of event data

$time = date('Y-m-d H:i:s');

//outputs message with a time interval of .9 second (900 milliseconds
echo "event:server_time\n";
echo "data:$time\n";
echo "retry: 900\n\n";
$query = "SELECT * FROM predicted_rssi WHERE displayed = 0 ORDER BY last_seen asc";
if ($result = $mysqli->query($query)) {
	while($row = $result->fetch_assoc()) {
		$arr = array('device_id' => $row['device_id'], 'last_seen' => $row['last_seen'], 'x_coord' => $row['x_coord'], 'y_coord' => $row['y_coord']);
		echo "event: log\n";
		echo "data: ", json_encode($arr), "\n\n";
		$current_id = $row['id'];
		$innerQuery = "UPDATE predicted_rssi SET displayed = '1' WHERE id = '$current_id'";
		$mysqli->query($innerQuery);
	}
} 

//sends output data to the browser
ob_flush();
flush(); 