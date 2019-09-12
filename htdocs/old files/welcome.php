<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include config file
require_once "../config.php";
// Processing form data when form is submitted
$email = $_SESSION["email"];
$display = "Please enter your personal device's MAC address to opt in.";


if($_SERVER["REQUEST_METHOD"] == "POST"){
	$mac = $_POST["mac"];
	$status = $_POST["status"];
	$device_name = $_POST["device_name"];
	
	// Remove hyphens and colons for database storage
	$mac = str_replace(":","",$mac);
	$mac = str_replace("-","",$mac);
	$mac = strtoupper($mac);
	
	$sql = "INSERT INTO registered_macs SET mac_address = '$mac',
	enabled = '$status', device_name = '$device_name', userid = (
	SELECT userid FROM users WHERE email = '$email')";
	if($mysqli->query($sql) === true){
		$display = "MAC address has been stored.";
	} elseif($mysqli->errno == 1062) {
		$display = "MAC address is already in database, try again.";
	} else {
		echo "ERROR: Could not able to execute $sql. " . $mysqli->error;
	}
}

// Check if there is a stored MAC address, show opt out link
/*$sql = "SELECT mac_address FROM accounts WHERE email = '$email';";
$result = $mysqli->query($sql);
$row = $result->fetch_assoc();

if($row["mac_address"] != null) {
	$optout = "Opt Out";
	$hide = "none";
	$status = "MAC address has been stored.";
}*/


?>

<!DOCTYPE html>
<html>
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
<p>Hi, <b><?php echo htmlspecialchars($_SESSION["email"]); ?></b>.</p>
<p><?php echo $display ?></p>
<form id="formID" onSubmit="return validateMacAddress()" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

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

<table width="100% border="1" style="border-collapse:collapse;";>
<thead>
<tr>
<th>ID</th>
<th>MAC Address</th>
<th>Device Name</th>
<th>Status</th>
<th>Modify</th>
</tr>
<tbody>
<?php
$count = 1;
$sel_query = "select * from registered_macs inner join users on registered_macs.userid = users.userid where email = '$email';";
$result = mysqli_query($mysqli, $sel_query);
while($row = mysqli_fetch_assoc($result)) { ?>
<tr><td align="center"><?php echo $count; ?></td>
<td align="center"><?php echo $row["mac_address"]; ?></td>
<td align="center"><?php echo $row["device_name"]; ?></td>
<td align="center"><?php if ($row["enabled"] == 1) {echo "Enabled";} else{echo "Disabled";} ?></td>
<td align="center"><a href="edit.php?id=<?php echo $row["id"]; ?>">Modify</a>
<a href="delete.php?id=<?php echo $row["id"]; ?>">Delete</a></td>
</tr>
<?php $count++; }
$mysqli->close(); ?>
</tbody>
</table>
<p>
<a href="logout.php">Sign Out</a>
</p>
</body>
</html>