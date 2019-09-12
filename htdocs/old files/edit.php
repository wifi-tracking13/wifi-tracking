<?php
// Initialize the session
session_start();
$email = $_SESSION["email"];

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "../config.php";
$id=$_REQUEST['id'];
if($_SERVER["REQUEST_METHOD"] == "POST"){
	$deviceId = $_POST["deviceId"];
	$mac = $_POST["mac"];
	$status = $_POST["status"];
	$device_name = $_POST["device_name"];
	
	// Remove hyphens and colons for database storage
	$mac = str_replace(":","",$mac);
	$mac = str_replace("-","",$mac);
	$mac = strtoupper($mac);
	
	$edit_sql = "UPDATE registered_macs SET device_name = '$device_name', enabled = '$status'  WHERE id = '$deviceId'";
	if($mysqli->query($edit_sql) === true){
		header("Location: welcome.php");
	} elseif($mysqli->errno == 1062) {
		$display = "MAC address is already in database, try again.";
	} else {
		echo "ERROR: Could not able to execute $sql. " . $mysqli->error;
	}
}
?>

<!DOCTYPE html>
<head>
    <style>
    </style>
<script>
	// Regex function to ensure a correct MAC address format
	function validateMacAddress() {
		var regexMac = /^((([0-9A-F]{2}:){5})|(([0-9A-F]{2}-){5})|([0-9A-F]{10}))([0-9A-F]{2})$/i
		if(document.getElementById("mac").value.match(regexMac)) {
		return true;
		} else {
			alert("You have entered an invalid MAC address!");
			return false;
		}
	}
</script>
</head>
<body>
<p>Hi, <b><?php echo $id; ?></b>.</p>
<form id="formID" onSubmit="return validateMacAddress()" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
<input type="hidden" id="deviceId" name="deviceId" value="<?php echo $id; ?>">
<label for="device_name"><b>Device Name: </b></label>
<input type="text" id="d_name" name="device_name" required>
<label for="mac"><b>MAC Address: </b></label>
<input type="text" id="mac" placeholder="00:00:00:00:00:00" name="mac" required>
<label for="status"><b>Status: </b></label>
<input type="radio" id="enable" name="status" value="1" checked>
<label for="enable">Enable</label>
<input type="radio" id="disable" name="status" value="0">
<label for="disable">Disable</label>
<button type="submit">Submit</button>
</form>

</body>
</html>