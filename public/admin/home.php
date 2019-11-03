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
?>

<!DOCTYPE html>
<html>
<head>
	<title>Wi-Fi Tracking in Smart Buildings</title>
	<script src="fabric.js"></script>
</head>
<body>
<h1>Example server sent events with EventSource</h1>
<h2>Server-Time: <span id='ss_time'></span></h2>
<canvas id ="canvas" width="500" height="500"></canvas>
<form action="../logout.php">
	<input type="submit" value="Logout" />
</form>
<script>
var ss_time = document.getElementById('ss_time');
var deviceMap = new Map();
var staticCanvas = new fabric.StaticCanvas('canvas');
staticCanvas.backgroundColor = "grey";
staticCanvas.renderAll();

// create device object to put on map
function initialize(device_id, x, y) {
	var rect = new fabric.Rect({
	originX: 'center',
	orginY: 'bottom',
	fill: 'blue',
	width: 5,
	height: 5
	});
	var textObject = new fabric.Text('ID:' + device_id , {
			fontSize: 10,
			originX: 'center',
			originY: 'bottom'
		});
		
	var group = new fabric.Group([rect, textObject], {
		left: x,
		top: y,
	});
	staticCanvas.add(group);
	return group;
}

//if the browser supports EventSource
//defines an EventSource object to receive data from sse_ex.php file
var evsource = new EventSource('event_source.php');

//detects when it is received message with data from server
evsource.addEventListener('server_time', (ev)=>{
	//gets and adds data in #ss_time
	let time = ev.data;
	ss_time.innerHTML = time;
});

evsource.addEventListener('log', (ev)=>{
	let log_data = JSON.parse(ev.data);
	if (log_data.x_coord != null && log_data.y_coord != null) {
		if(deviceMap.has(log_data.device_id)) {
			// if device is in hashmap, grab and update coordinates to UI
			currentDevice = deviceMap.get(log_data.device_id);
			currentDevice.set({left: parseInt(log_data.x_coord), top: parseInt(log_data.y_coord)});
		} else {
			// else initialize canvas object and store in hashmap
			createdDevice = initialize(log_data.device_id, parseInt(log_data.x_coord), parseInt(log_data.y_coord));
			deviceMap.set(log_data.device_id, createdDevice);
			console.log(deviceMap);
		}
		staticCanvas.renderAll();
	}
	console.log("Device ID:" + log_data.device_id + " Timestamp: " + log_data.last_seen + " X-Coordinate: " + log_data.x_coord + " Y-Coordinate: " + log_data.y_coord);
});
</script>
</body>
</html>