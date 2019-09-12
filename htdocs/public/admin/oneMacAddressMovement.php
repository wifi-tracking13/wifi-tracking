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
</head>
<body>
	<h1>Welcome Admin</h1>
	<h2>Real Time Data!</h2>
	<form action="../logout.php">
		<input type="submit" value="Logout" />
	</form>
	<canvas id ="canvas" width="1000" height="1000"></canvas>
	<div id="location">
			<!--response inserted here-->
	</div>
</body>
<script>
	var width = 10;
	var height = 10;
	var x;
	var y;
	var canvas = document.getElementById('canvas');
	var ctx = canvas.getContext('2d');
	ctx.fillStyle = 'rgb(200, 0, 0)';
	var source = new EventSource("push_location.php");
	source.addEventListener('location', function(e) {
		var data = JSON.parse(e.data);
		// document.getElementById("location").innerHTML += "<br>" + event.data + ".";
		document.getElementById("location").innerHTML += "<br>" + data.device + ' is now at x: ' + data.x_val +', y: ' + data.y_val;
		console.log(data.device + ' is now at x: ' + data.x_val +', y: ' + data.y_val);
		ctx.clearRect(x, y, width, height);
		// Round to nearest integer to prevent ghost borders
		x = Math.round(data.x_val);
		y = Math.round(data.y_val);
		render();
	}, false);
	function render() {	
		ctx.fillRect(x, y, width, height);
	}
	
	//source.onmessage = function(event){
	//	document.getElementById("location").innerHTML += "<br>" + event.data + ".";
	//};
</script>
</html>