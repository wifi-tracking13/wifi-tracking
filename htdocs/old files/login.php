<?php
// Initialize the session
session_start();
 
// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: welcome.php");
    exit;
}
 
// Include config file
require_once "../config.php";
 
// Define variables and initialize with empty values
$email = $password = "";
$email_err = $password_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    
    // Prepare a select statement
    $sql = "SELECT userid, email, password FROM users WHERE email = ?";
        
    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
		$stmt->bind_param("s", $param_email);
            
		// Set parameters
		$param_email = $email;
		
		// Attempt to execute the prepared statement
		if($stmt->execute()){
			// Store result
			$stmt->store_result();
                
			// Check if email exists, if yes then verify password
			if($stmt->num_rows == 1){                    
				// Bind result variables
				$stmt->bind_result($id, $email, $hashed_password);
				if($stmt->fetch()){
					if(password_verify($password, $hashed_password)){
						// Password is correct, so start a new session
						session_start();
						
						// Store data in session variables
						$_SESSION["loggedin"] = true;
						$_SESSION["id"] = $id;
						$_SESSION["email"] = $email;                            
						
						// Redirect user to welcome page
						header("location: Welcome.php");
					} else{
						// Display an error message if password is not valid
						$password_err = "The password you entered was not valid.";
					}
				}
			} else{
				// Display an error message if email doesn't exist
				$email_err = "No account found with that email.";
			}
		} else{
			echo "Oops! Something went wrong. Please try again later.";
		}
	}
        
	// Close statement
	$stmt->close();

	// Close connection
	$mysqli->close();
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body {
  font-family: Arial, Helvetica, sans-serif;
  background-color: black;
}

* {
  box-sizing: border-box;
}

/* Add padding to containers */
.container {
  padding: 16px;
  background-color: white;
}

/* Full-width input fields */
input[type=text], input[type=password] {
  width: 100%;
  padding: 15px;
  margin: 5px 0 22px 0;
  display: inline-block;
  border: none;
  background: #f1f1f1;
}

input[type=text]:focus, input[type=password]:focus {
  background-color: #ddd;
  outline: none;
}

/* Overwrite default styles of hr */
hr {
  border: 1px solid #f1f1f1;
  margin-bottom: 25px;
}

/* Set a style for the submit button */
.registerbtn {
  background-color: #4CAF50;
  color: white;
  padding: 16px 20px;
  margin: 8px 0;
  border: none;
  cursor: pointer;
  width: 100%;
  opacity: 0.9;
}

.registerbtn:hover {
  opacity: 1;
}

/* Add a blue text color to links */
a {
  color: dodgerblue;
}

/* Set a grey background color and center the text of the "sign in" section */
.signin {
  background-color: #f1f1f1;
  text-align: center;
}
</style>
</head>
<body>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
	<div class="container">
	<h1>Login</h1>
	<p>Please fill in this form to log into an account. </p>
	<hr>

	<label for="email"><b>Email</b></label>
	<input type="text" id="email" placeholder="Enter Email" name="email" required>
	
	<label for="psw"><b>Password</b></label>
	<input type="password" id="password" placeholder="Enter Password" name="password" required>
	
	<hr>
	<p>By creating an account you agree to our <a href="#">Terms & Privacy</a>
	</p>
	<button type="submit" class="registerbtn">Login</button>
</div>
<div class="container signin">
	<p>Don't have an account? <a href="registration.html">Create one</a>.</p>
</div>
</form>
</body>
</html>