<?php
// Initialize the session
session_start();
 
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

// Include config file
require_once "../config.php";
$email = $_SESSION["email"];
$id = $_SESSION["id"];
$errors = array(); 

if (isset($_POST['add'])) {
	header("location: edit.php?id=new");
	exit;
} elseif (isset($_POST['enable'])) {
	// MySQL query to enable all devices
	$query = "SELECT * FROM registered_macs WHERE userid = $id and enabled = 0";
	$result = mysqli_query($mysqli, $query);
	while ($row = mysqli_fetch_assoc($result)) {
		$device = $row['id'];
		$enableAllQuery = "UPDATE registered_macs SET enabled = 1 WHERE id = $device";
		if($mysqli->query($enableAllQuery) === true){
		} else {
			echo "ERROR: Could not able to execute $sql. " . $mysqli->error;
		}
	}
} elseif (isset($_POST['disable'])) {
	// MySQL query to disable all devices
	$query = "SELECT * FROM registered_macs WHERE userid = $id AND enabled = 1";
	$result = mysqli_query($mysqli, $query);
	while ($row = mysqli_fetch_assoc($result)) {
		$device = $row['id'];
		$disableAllQuery = "UPDATE registered_macs SET enabled = 0 WHERE id = $device";
		if($mysqli->query($disableAllQuery) === true){
		} else {
			echo "ERROR: Could not able to execute $sql. " . $mysqli->error;
		}
	}
} elseif (isset($_POST['delete'])) {
	// Delete all devices
	$deleteQuery = "DELETE FROM registered_macs WHERE userid = $id";
	if($mysqli->query($deleteQuery) === true){
	} else {
		echo "ERROR: Could not able to execute $sql. " . $mysqli->error;
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Wi-Fi Tracking in Smart Buildings</title>
	<link rel="stylesheet" href="form.css" type="text/css">
</head>
<body>
	<div class="body-content">
		<div class="module">
			<h1>Welcome <?php echo htmlspecialchars($_SESSION["email"]); ?>.</h1>
			<div class="alert alert-error"><?php echo implode(", ", $errors); ?></div>
			<p><b>Registered Devices</b></p>
			<table width="100%">
				<thead>
					<tr>
					<th align="left">ID</th>
					<th align="left">Device Name</th>
					<th align="left">Status</th>
					<th align="left">Modify</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$enableButton = false;
					$count = 1;
					$sel_query = "SELECT * FROM registered_macs INNER JOIN users ON registered_macs.userid = users.userid WHERE email = '$email';";
					$result = mysqli_query($mysqli, $sel_query);
					if(!($result->num_rows === 0)) {
						$enableButton = true;
					}
					while($row = mysqli_fetch_assoc($result)) { ?>
					<tr><td align="left"><?php echo $count; ?></td>
					<td align="left"><?php echo $row["device_name"]; ?></td>
					<td align="left"><?php if ($row["enabled"] == 1) {echo "Enabled";} else{echo "Disabled";} ?></td>
					<td align="left"><a href="edit.php?id=<?php echo $row["id"]; ?>">Modify</a>
					<a href="delete.php?id=<?php echo $row["id"]; ?>">Delete</a></td>
					</tr>
					<?php $count++; }
					$mysqli->close(); ?>
				</tbody>
			</table>
			<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
				<input type="submit" name="add" value="Add New"/>
				<input type="submit" name="enable" value="Enable All" <?php if (!$enableButton) { echo 'disabled="disabled"'; }?>/>
				<input type="submit" name="disable" value="Disable All" <?php if (!$enableButton) { echo 'disabled="disabled"'; }?>/>
				<input type="submit" name="delete" value="Delete All" <?php if (!$enableButton) { echo 'disabled="disabled"'; }?>/>
			</form>
			<form action="logout.php">
				<input type="submit" value="Logout" class="btn btn-block btn-primary" />
			</form>
		</div>
	</div>
</body>
</html>
