<?php
// Include config file
include "../../config.php";

header("Content-Type: text/event-stream");
header("Cache-Control: no-cache");

$query = "SELECT * from locations WHERE seen = '0' ORDER BY id ASC";
if ($result = $mysqli->query($query)) {
	while($row = $result->fetch_assoc()) {
		$current_id = $row['id'];
		$arr = array('device' => $row['deviceID'], 'x_val' => $row['x_coord'], 'y_val' => $row['y_coord']);
		// Reconnection time to every 1 second 
		echo "retry: 1000\n";
		echo "event: location\n";
		echo "data: ", json_encode($arr), "\n\n";
		//echo "data: Device {$row['deviceID']} is now at ({$row['x_coord']}, {$row['y_coord']})\n\n";
		// Updating record so as not to be fetched again
		$query2 = "UPDATE locations SET seen = '1' WHERE id = '$current_id'";
		$mysqli->query($query2);
	}	
}
//$time = date('Y-m-d H:i:s');
//echo "retry: 1000\n";
//echo "data:{$time}\n\n";
flush();
?>