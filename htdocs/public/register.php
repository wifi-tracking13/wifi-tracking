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
$password_1 = "";
$errors = array(); 

if($_SERVER["REQUEST_METHOD"] == "POST"){
	// Receive all input values from the form
	$email = mysqli_real_escape_string($mysqli, $_POST['email']);
	$password_1 = mysqli_real_escape_string($mysqli, $_POST['password']);
	$password_2 = mysqli_real_escape_string($mysqli, $_POST['confirmpassword']);

	// Form validation: ensure that the form is correctly filled ...
	// by adding (array_push()) corresponding error unto $errors array
	if (empty($email)) { array_push($errors, "email is required"); }
	if (empty($password_1)) { array_push($errors, "password is required"); }
	if ($password_1 != $password_2) {
		array_push($errors, "password do not match");
	}
	
	// Ensure password is not too short/long
	if (strlen($password_1) < 6) {
		array_push($errors, "password should be atleast 6 characters");
	}
	if (strlen($password_1) > 50) {
		array_push($errors, "password should be less than 50 characters");
	}
	
	// Built in PHP function to validate correct email format
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { array_push($errors, "invalid email"); }
	
	// Check the database to make sure 
	// a user does not already exist with the same email
	$email_check_query = "SELECT * FROM users WHERE email='$email' LIMIT 1";
	$result = mysqli_query($mysqli, $email_check_query);
	$user = mysqli_fetch_assoc($result);

    if ($user['email'] === $email) {
      array_push($errors, "email already exists");
    }

	// Finally, register user if there are no errors in the form
	if (count($errors) == 0) {
		// Encrypt the password before saving in the database
		$hash = password_hash($password_1, PASSWORD_BCRYPT);
		$query = "INSERT INTO users (email, password) VALUES('$email', '$hash')";
		if($mysqli->query($query) === true){
			// Redirect to login page if query is successful
			header("location: login.php");
			exit();
		} else {
			array_push($errors, "Unable to execute insert statement"); 
		}
	}
}
$mysqli->close();
?>
<html>
<head>
	<title>Wi-Fi Tracking in Smart Buildings</title>
	<link href="//db.onlinewebfonts.com/c/a4e256ed67403c6ad5d43937ed48a77b?family=Core+Sans+N+W01+35+Light" rel="stylesheet" type="text/css"/>
	<link rel="stylesheet" href="form.css" type="text/css">
</head>
<body>
	<div class="body-content">
		<div class="module">
			<h1>Create an account</h1>
			<form class="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" autocomplete="off">
			<div class="alert alert-error"><?php echo implode(", ", $errors); ?></div>
			<label>Email</label>
			<input type="email" name="email" required />
			<label>Password</label>
			<input type="password" name="password" autocomplete="new-password" required/>
			<label>Confirm Password</label>
			<input type="password" name="confirmpassword" autocomplete="new-password" required/>
			<input type="submit" value="Register" name="register" class="btn btn-block btn-primary" />
				<div class="container signin">
					<p>Already have an account? <a href="login.php">Sign in</a>.</p>
				</div>
			</form>
		</div>
	</div>
</body>
</html>
