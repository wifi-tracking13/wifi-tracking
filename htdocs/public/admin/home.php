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

// Include config file
require_once "../../config.php";
// Processing form data when form is submitted
$email = $_SESSION["email"];

?>

<!DOCTYPE html>
<html>
<head>
	<title>Wi-Fi Tracking in Smart Buildings</title>
	<script src="fabric.js"></script>
</head>
<body>
	<h1>Welcome Admin</h1>
	<h2>Real Time Data!</h2>
	<form action="../logout.php">
		<input type="submit" value="Logout" />
	</form>
	<canvas id ="canvas" width="1070" height="1050"></canvas>
	<div id="location">
		<p>Log:</p>
		<!--response inserted here-->
	</div>
</body>
<script>
	var x = 0;
	var y = 0;
	var text = 'ID: 100';
	
	var staticCanvas = new fabric.StaticCanvas('canvas');
	
	var circle = new fabric.Circle({
		radius: 10,
		fill: 'red',
		originX: 'center',
		originY: 'top'
	});
	
	var textObject = new fabric.Text(text, {
		fontSize: 20,
		originX: 'center',
		originY: 'bottom'
	});
	
	var group = new fabric.Group([circle, textObject], {
		left: x,
		top: y,
	});
	staticCanvas.add(group);
	

	
	//group.set({left: x, top: y});
	//var canvas = document.getElementById('canvas');
	//var ctx = canvas.getContext('2d');
	//ctx.fillStyle = 'rgb(200, 0, 0)';
	var source = new EventSource("push_location.php");
	source.addEventListener('location', function(e) {
		var data = JSON.parse(e.data);
		// document.getElementById("location").innerHTML += "<br>" + event.data + ".";
		document.getElementById("location").innerHTML += data.device + ' is now at x: ' + data.x_val +', y: ' + data.y_val + "<br>";
		console.log(data.device + ' is now at x: ' + data.x_val +', y: ' + data.y_val);
		//ctx.clearRect(x, y, width, height);
		// Round to nearest integer for canvas mapping
		x = Math.round(data.x_val);
		y = Math.round(data.y_val);
		text = data.device;
		render();
	}, false);
	function render() {	
		//ctx.fillRect(x, y, width, height);
		textObject.set({text: 'ID: ' + text});
		group.set({left: x, top: y});
		staticCanvas.renderAll();
	}
	
	//source.onmessage = function(event){
	//	document.getElementById("location").innerHTML += "<br>" + event.data + ".";
	//};
</script>
</html>