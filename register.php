<!DOCTYPE HTML>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Social Network</title>
		<link rel="stylesheet" href="style.css">
		<?php
			session_start();
			
			// Connect to database
			$connection = new mysqli("localhost", "pstakoun", "yJcRNzpSaEXatKqc", "socialnetwork");
			if ($connection->connect_error) {
				$errorMessage = "<p id=\"error\">Could not connect to database.</p>";
			}
			
			if (isset($_SESSION["id"])) {
				header("Location: index.php");
				die();
			} else {
				$postValid = True;
				$errorMessage = "";
				
				if (empty($_POST)) {
					$postValid = False;
				} else {
					$firstname = htmlspecialchars($_POST["firstname"]);
					$lastname = htmlspecialchars($_POST["lastname"]);
					$email = htmlspecialchars($_POST["email"]);
					$password = htmlspecialchars($_POST["password"]);
					$confirmpassword = htmlspecialchars($_POST["confirmpassword"]);
					
					$sql = "SELECT * FROM users WHERE email = \"" . $email . "\"";
					$result = $connection->query($sql);
					
					// Validate name
					if (!(ctype_alpha($firstname) && ctype_alpha($lastname))) {
						$postValid = False;
						$errorMessage = "<p id=\"error\">Name invalid.</p>";
					// Validate email
					} else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
						$postValid = False;
						$errorMessage = "<p id=\"error\">Email invalid.</p>";
					// Validate password
					} else if (strlen($password) < 6) {
						$postValid = False;
						$errorMessage = "<p id=\"error\">Password too short.</p>";
					// Validate password confirmation
					} else if ($password != $confirmpassword) {
						$postValid = False;
						$errorMessage = "<p id=\"error\">Passwords do not match.</p>";
					} else if ($result->num_rows != 0) {
						$postValid = False;
						$errorMessage = "<p id=\"error\">Email in use.</p>";
					}
				}
			}
		?>
	</head>
	
	<body>
		<div id="titleBar">
			<div id="titleBarWrap">
				<div id="titleBarLogo">
					<a href="index.php"><img src="images/logo.png" width=48px height=48px></a>
				</div>
				<div>
					<h1>Social Network</h1>
				</div>
			</div>
		</div>
		
		<div id="register">
			<?php
				if (!$postValid) {
			?>
				<?php echo($errorMessage); ?>
				<form method="post" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
					<p id="label">First Name: <input type="text" name="firstname" value="<?php if (!empty($firstname)) { echo($firstname); } ?>" /></p>
					<p id="label">Last Name: <input type="text" name="lastname" value="<?php if (!empty($lastname)) { echo($lastname); } ?>" /></p>
					<p id="label">Email: <input type="email" name="email" value="<?php if (!empty($email)) { echo($email); } ?>" /></p>
					<p id="label">Password: <input type="password" name="password" /></p>
					<p id="label">Confirm Password: <input type="password" name="confirmpassword" /></p>
					<p id="label"><input type="submit" name="register" value="Register" /></p>
				</form>

			<?php } else {
				// Capitalize name
				$firstname = ucfirst($firstname);
				$lastname = ucfirst($lastname);
				
				// Ensure id is unique
				$id = uniqid("", true);
				$sql = "SELECT * FROM users WHERE id = \"" . $id . "\"";
				$result = $connection->query($sql);
				while ($result->num_rows != 0) {
					$id = uniqid("", true);
					$sql = "SELECT * FROM users WHERE id = \"" . $id . "\"";
					$result = $connection->query($sql);
				}
				
				// Ensure username is unique
				$fn = strtolower(preg_replace("/[^a-z]/i", "", $firstname));
				$ln = strtolower(preg_replace("/[^a-z]/i", "", $lastname));
				$username = $fn . "." . $ln;
				$sql = "SELECT * FROM users WHERE username = \"" . $username . "\"";
				$result = $connection->query($sql);
				$i = 0;
				while ($result->num_rows != 0) {
					$username = $fn . "." . $ln . ++$i;
					$sql = "SELECT * FROM users WHERE username = \"" . $username . "\"";
					$result = $connection->query($sql);
				}
				
				$passwordhash = password_hash($password, PASSWORD_DEFAULT);
				
				// Create query
				$sql = "INSERT INTO users (id, username, firstname, lastname, email, password) VALUES (\"" . $id . "\", \"" . $username . "\", \"" . $firstname . "\", \"" . $lastname . "\", \"" . $email . "\", \"" . $passwordhash . "\")";

				if (!$connection->query($sql)) {
					if (empty($errorMessage)) { $errorMessage = "<p id=\"error\">Database error.</p>"; }
				}
				if (!empty($errorMessage)) {
					echo($errorMessage);
				} else {
					echo("<p id=\"label\">Registration successful! A confirmation email has been sent to " . $email . ".</p>");
				}
			} ?>
		</div>
	</body>
</html>
