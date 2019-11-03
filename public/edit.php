<?php
// Initialize the session
session_start();
$email = $_SESSION["email"];
$userid = $_SESSION['id'];

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
$errors = array(); 
$id=$_REQUEST['id'] ?? $_SESSION["getID"];
$_SESSION["getID"] = $id;
if(isset($_POST['submit'])){
	$deviceId = $_POST["deviceId"];
	$userId = $_POST["userId"];
	$mac = $_POST["mac"];
	$status = $_POST["status"];
	$device_name = $_POST["device_name"];
	
	// Ensure no missing fields
	if (empty($mac)) { array_push($errors, "mac address is required"); }
	if (empty($device_name)) { array_push($errors, "device name is required"); }
	
	// Check for MAC address validness
	$regexMac = "/^((([0-9A-F]{2}:){5})|(([0-9A-F]{2}-){5})|([0-9A-F]{10}))([0-9A-F]{2})$/i";
	if (!preg_match($regexMac, $mac)) { array_push($errors, "mac address is invalid"); }
	
	// Prepare MAC address for database
	$mac = str_replace(":","",$mac);
	$mac = str_replace("-","",$mac);
	$mac = strtoupper($mac);
	
	// Check for any matching MAC addresses in database except for device where user is modifying
	if($deviceId == 'new') {
		$query = "SELECT * FROM registered_macs";
	} else {
		$query = "SELECT * FROM registered_macs WHERE NOT id = $deviceId";
	}
	
	$results = mysqli_query($mysqli, $query);
	
	while($row = mysqli_fetch_assoc($results)) {
		$rowMacAddress = $row['mac_address'];
		if (password_verify($mac, $rowMacAddress)) {
			array_push($errors, "found matching MAC address in database");
			break;
		}
	}
		
	// Register mac if there are no errors in the form
	if (count($errors) == 0) {
		$hashedMac = password_hash($mac, PASSWORD_BCRYPT);
		if ($deviceId == 'new') {
			// New devices
			$newDeviceQuery = "INSERT INTO registered_macs SET mac_address = '$hashedMac', enabled = '$status', device_name = '$device_name', userid = $userId";
			if($mysqli->query($newDeviceQuery) === TRUE){
				header("location: home.php");
				exit;
			} else {
				echo "ERROR: Could not able to execute $sql. " . $mysqli->error;
			}
		} else {
			// Edited devices (does not currently update MAC)
			$sql = "UPDATE registered_macs SET device_name = '$device_name',mac_address = '$hashedMac', enabled = '$status'  WHERE id = '$deviceId'";
			if($mysqli->query($sql) === true){
				header("location: home.php");
				exit;
			} else {
				echo "ERROR: Could not able to execute $sql. " . $mysqli->error;
			}
		}
	}
}
$mysqli->close();
?>

<!DOCTYPE html>
<head>
	<title>Wi-Fi Tracking in Smart Buildings</title>
	<link rel="stylesheet" href="form.css" type="text/css">
</head>
<body>
	<div class="body-content">
		<div class="module">
			<h1>Welcome <?php echo htmlspecialchars($_SESSION["email"]); ?>.</h1>
			<div class="alert alert-error"><?php echo implode(", ", $errors); ?></div>
			<form id="formID" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
				<input type="hidden" id="deviceId" name="deviceId" value="<?php echo htmlspecialchars($_REQUEST['id'] ?? $_SESSION["getID"]); ?>">
				<input type="hidden" id="userId" name="userId" value="<?php echo $userid; ?>">
				<label for="device_name"><b>Device Name: </b></label>
				<input type="text" id="d_name" name="device_name" required>
				<label for="mac"><b>MAC Address: </b></label>
				<input type="text" id="mac" name="mac" >
				<label for="status"><b>Status: </b></label>
				<input type="radio" id="enable" name="status" value="1" checked>
				<label for="enable">Enable</label>
				<input type="radio" id="disable" name="status" value="0">
				<label for="disable">Disable</label>
				<input type="submit" value="Submit" name="submit" class="btn btn-block btn-primary" />
			</form>
		</div>
	</div>
</body>
</html>