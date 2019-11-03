<?php
// Initialize the session
session_start();
 
// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    if ($_SESSION["admin"] == true) {
		header("location: admin/home.php");
	} else {
		header("location: home.php");
	}
    exit;
}

// Include config file
require_once "../config.php";

// Initializing variables
$email    = "";
$password = "";
$errors = array(); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$email = mysqli_real_escape_string($mysqli, $_POST['email']);
	$password = mysqli_real_escape_string($mysqli, $_POST['password']);

	if (empty($email)) {
		array_push($errors, "username is required");
	}
	if (empty($password)) {
		array_push($errors, "password is required");
	}

	// Regular expression to validate correct email format
	$regex = "/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/";
	if (!preg_match($regex, $email)) { array_push($errors, "invalid email"); }
	
	// Login user
	if (count($errors) == 0) {
		$query = "SELECT * FROM users WHERE email='$email'";
		$results = mysqli_query($mysqli, $query);
		if(mysqli_num_rows($results) != 0) {
			$row = mysqli_fetch_assoc($results);
			$id = $row["userid"];
			$hash = $row['password'];
			$admin = $row['admin'];
			if(password_verify($password, $hash)) {
				// Password is correct, so start a new session
				session_start();
								
				// Store data in session variables
				$_SESSION["loggedin"] = true;
				$_SESSION["id"] = $id;
				$_SESSION["email"] = $email;
				$_SESSION["admin"] = $admin;
				if ($_SESSION["admin"] == true) {
					header("location: admin/home.php");
				} else {
					header("location: home.php");
				}
			} else {
				array_push($errors, "incorrect password");
			}
		} else {
			array_push($errors, "incorrect email");
		}
	}
}

$mysqli->close();
?>
<html>
<head>
	<title>Wi-Fi Tracking in Smart Buildings</title>
	<link href="//db.onlinewebfonts.com/c/a4e256ed67403c6ad5d43937ed48a77b?family=Core+Sans+N+W01+35+Light" rel="stylesheet" type="text/css"/>
	<link rel="stylesheet" href="form.css">
</head>
<body> 
	<div class="body-content">
		<div class="module">
			<h2>Login</h2>
			<form class="login" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
			<div class="alert alert-error"><?php echo implode(", ", $errors); ?></div>
			<label>Email</label>
			<input type="email" name="email" required>
			<label>Password</label>
			<input type="password" name="password" required>
			<input type="submit" value="Login" name="login" class="btn btn-block btn-primary" />
			<p>Not yet a member? <a href="register.php">Sign up</a></p>
			</form>
		</div>
	</div>
</body>
</html>
