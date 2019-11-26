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
	<style>
        body { margin:0px; padding:0px; width: 100vw; height:100vh; font: 12px/20px 'Helvetica Neue', Arial, Helvetica, sans-serif }
        #controls{
            position: absolute;
            box-sizing: border-box;
            padding: 10px;
            top: 10px;
            left: 10px;
            width: auto;
            height: auto;
        }
        #controls button{
            margin-top: 10px;
            background-color: rgb(31, 175, 252);
            padding: 0px 10px;
            border-radius: 4px;
            color: rgb(255, 255, 255);
            width: auto;
            border: 0;
            display: inline-block;
        }
    </style>
</head>
<body>
<div id="map" class="canvas"></div>
<div id="controls" class="mapboxgl-ctrl-group">
<h1>Wi-Fi Tracking in Smart Buildings</h1>
<h2 hidden>Server-Time: <span id='ss_time'></span></h2>
<h2>Number of devices in the room: <span id='p_counter'>0</span></h2>
<canvas id ="canvas" width="666" height="566"></canvas>
<form action="../logout.php">
<input type="submit" value="Logout" />
</form>
<script>
var ss_time = document.getElementById('ss_time');
var p_counter = document.getElementById('p_counter');
var deviceMap = new Map();
var staticCanvas = new fabric.StaticCanvas('canvas');
//var line = makeLine([1, 1, 1, 828]);
//	line2 = makeLine([1, 1, 640, 1]),
//	line3 = makeLine([639, 1, 639, 828]),
//	line4 = makeLine([1, 827, 641, 827]);

//Adds pi representation to the map
fabric.Image.fromURL('https://raw.githubusercontent.com/iiiypuk/rpi-icon/master/256.png', function(myImg) {
 var img1 = myImg.set({ width: 250, height: 250, left: 290, top: 20, scaleX: .20, scaleY: .20});
 staticCanvas.add(img1); 
});

fabric.Image.fromURL('https://raw.githubusercontent.com/iiiypuk/rpi-icon/master/256.png', function(myImg) {
 var img2 = myImg.set({ width: 250, height: 250, left: 5, top: 510, scaleX: .20, scaleY: .20});
 staticCanvas.add(img2); 
});

fabric.Image.fromURL('https://raw.githubusercontent.com/iiiypuk/rpi-icon/master/256.png', function(myImg) {
 var img3 = myImg.set({ width: 250, height: 250, left: 530, top: 510, scaleX: .20, scaleY: .20});
 staticCanvas.add(img3); 
});

staticCanvas.backgroundColor = "#D3D3D3";	
//staticCanvas.add(line, line2, line3, line4);
//staticCanvas.remove(createdDevice);
//device = device(140, 0, 50);
//console.log(device.timeRemaining);
//deviceMap2.set(140, device);
staticCanvas.renderAll();

// timer to countdown the seconds (1000ms) for object to remain on screen
setInterval(function(){
	count = 0;
	for (let [k, v] of deviceMap) {
		v.timeRemaining--;
		if(v.x_pixel >= 0 && v.x_pixel <= 666 && v.y_pixel >= 0 && v.y_pixel <= 566) {
			count++;
		} else {
			staticCanvas.remove(v.display);
			deviceMap.delete(k);
		}
		// device has not been seen by pi for some time, delete from canvas, counter, and hashmap
		if (v.timeRemaining == 0) {
			console.log("Device " + k + " has timed out, removing device from map.");
			staticCanvas.remove(v.display);
			deviceMap.delete(k);
			if(count < 0) {
				count--;
			}
			
		}
	}
	p_counter.innerHTML = count;
}, 1000);

function makeLine(coords) {
	return new fabric.Line(coords, {
		fill: 'black',
		stroke: 'black',
		strokeWidth: 2,
	});
}

// create fabric.js object to put device on map
function initialize(email, device_name, x, y) {
	var rect = new fabric.Rect({
	fill: 'blue',
	width: 10,
	height: 10
	});
	var textEmail = new fabric.Text('User: ' + email , {
			fontSize: 14,
			left: 12,
		});
	var textDevice = new fabric.Text('Device: ' + device_name , {
			fontSize: 14,
			left: 12,
			top: 15
		});
		
	var group = new fabric.Group([rect, textEmail, textDevice], {
		left: x,
		top: y,
	});
	staticCanvas.add(group);
	return group;
}


// create device object with properties of display and time remaining
function device(device_id, x_pixel, y_pixel, email, device_name) {
	createdDevice = initialize(email, device_name, x_pixel, y_pixel);
	var device = new Object();
	device.id = device_id;
	device.x_pixel = x_pixel;
	device.y_pixel = y_pixel;
	device.email = email;
	device.name = device_name;
	//how long in seconds for device to remain active on screen if being recieved by raspberry pi
	device.timeRemaining = 15;
	//.display property is the fabric.js object
	device.display = createdDevice;
	return device;
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
	//var t0 = performance.now();
	
	let log_data = JSON.parse(ev.data);
	if (log_data.x_coord != null && log_data.y_coord != null) {
		if(deviceMap.has(log_data.device_id)) {
			// if device is in hashmap, update coordinates and reset timer
			currentDevice = deviceMap.get(log_data.device_id);
			currentDevice.x_pixel = parseFloat(log_data.x_coord)*200;
			currentDevice.y_pixel = parseFloat(log_data.y_coord)*200;
			currentDevice.display.set({left: parseFloat(log_data.x_coord)*200, top: parseFloat(log_data.y_coord)*200});
			currentDevice.timeRemaining = 10;
		} else {
			// else create device object and store in hashmap
			currentDevice = device(log_data.device_id, parseFloat(log_data.x_coord)*200, parseFloat(log_data.y_coord)*200, log_data.email, log_data.device_name);
			deviceMap.set(log_data.device_id, currentDevice);		
		}
		staticCanvas.renderAll();
	}
	//var t1 = performance.now();
	//console.log((t1 - t0));
	console.log("ID: " + log_data.device_id + " Email: " + log_data.email + " Device Name: " + log_data.device_name +" Timestamp: " + log_data.last_seen + " X-Coordinate: " + log_data.x_coord + " Y-Coordinate: " + log_data.y_coord);
});
</script>
</body>
</html>