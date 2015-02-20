<?php
	session_start();
	// Check for session
	if (isset($_SESSION["id"])) {
		header("Location: index.php");
		die();
	}
	
	// Import library for backwards compatibility
	require("lib/password.php");
	
	$errorMessage = "";
	// Connect to database
	try {
		$conn = new PDO("mysql:host=structhubdb.db.11405843.hostedresource.com;dbname=structhubdb", "structhubdb", "Cx!ak#Unm6Bknn54");
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch(PDOException $e) {
		$errorMessage = "<p id=\"error\">Could not connect to database.</p>";
	}
	
	// Initialize registration validity
	$postValid = True;
	if (empty($_POST)) {
		$postValid = False;
	} else {
		// Get submitted information
		$firstname = htmlspecialchars($_POST["firstname"]);
		$lastname = htmlspecialchars($_POST["lastname"]);
		$email = htmlspecialchars($_POST["email"]);
		$password = htmlspecialchars($_POST["password"]);
		$confirmpassword = htmlspecialchars($_POST["confirmpassword"]);
		
		// Check for email in database
		$stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
		$stmt->bindParam(":email", $email);
		$stmt->execute();
		$result = $stmt->fetchAll();
		
		// Validate name
		if (empty($firstname) || empty($lastname) || ctype_space($firstname) || ctype_space($lastname)) {
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
		// Check for email availability
		} else if (!empty($result)) {
			$postValid = False;
			$errorMessage = "<p id=\"error\">Email in use.</p>";
		}
	}
?>

<!DOCTYPE HTML>
<html>
	<head>
		<meta charset="UTF-8">
		<title>StructHub</title>
		<link rel="stylesheet" href="style.css">
	</head>
	
	<body>
		<div id="titleBar">
			<div id="titleBarWrap">
				<div id="titleBarLogo">
					<a href="index.php"><img src="images/logo.png" width=32px height=32px></a>
				</div>
				<div>
                    <h1>StructHub</h1>
                </div>
			</div>
		</div>
		
		<div id="form">
			<?php
				// Display registration form if submitted details are invalid
				if (!$postValid) {
			?>
				<form method="post" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
					<table style="margin: 0 auto;">
						<?php if (!empty($errorMessage)) { echo("<tr><td colspan=\"2\">" . $errorMessage . "</td></tr>"); } ?>
						<tr>
							<td id="label" align="right">First Name: </td>
							<td id="label"><input type="text" name="firstname" value="<?php if (!empty($firstname)) { echo($firstname); } ?>" /></td>
						</tr>
						<tr>
							<td id="label" align="right">Last Name: </td>
							<td id="label"><input type="text" name="lastname" value="<?php if (!empty($lastname)) { echo($lastname); } ?>" /></td>
						</tr>
						<tr>
							<td id="label" align="right">Email: </td>
							<td id="label"><input type="email" name="email" value="<?php if (!empty($email)) { echo($email); } ?>" /></td>
						</tr>
						<tr>
							<td id="label" align="right">Password: </td>
							<td id="label"><input type="password" name="password" /></td>
						</tr>
						<tr>
							<td id="label" align="right">Confirm Password: </td>
							<td id="label"><input type="password" name="confirmpassword" /></td>
						</tr>
						<tr>
							<td id="label" align="right"><input type="submit" name="register" value="Register" /></td>
						</tr>
					</table>
				</form>

			<?php } else {
				// Capitalize name
				$firstname = ucfirst($firstname);
				$lastname = ucfirst($lastname);
				
				// Ensure id is unique
				$id = uniqid("", true);
				$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
				$stmt->bindParam(":id", $id);
				$stmt->execute();
				$result = $stmt->fetchAll();
				while (!empty($result)) {
					$id = uniqid("", true);
					$stmt->execute();
					$result = $stmt->fetchAll();
				}
				
				// Ensure username is unique
				$fn = strtolower(preg_replace("/[^a-z]/i", "", $firstname));
				$ln = strtolower(preg_replace("/[^a-z]/i", "", $lastname));
				$username = $fn . "." . $ln;
				$stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
				$stmt->bindParam(":username", $username);
				$stmt->execute();
				$result = $stmt->fetchAll();
				$i = 0;
				while (!empty($result)) {
					$username = $fn . "." . $ln . ++$i;
					$stmt->execute();
					$result = $stmt->fetchAll();
				}
				
				// Hash password
				$passwordhash = password_hash($password, PASSWORD_DEFAULT);
				
				// Create user
				$stmt = $conn->prepare("INSERT INTO users (id, username, firstname, lastname, email, password) VALUES (:id, :username, :firstname, :lastname, :email, :password)");
				$stmt->bindParam(":id", $id);
				$stmt->bindParam(":username", $username);
				$stmt->bindParam(":firstname", $firstname);
				$stmt->bindParam(":lastname", $lastname);
				$stmt->bindParam(":email", $email);
				$stmt->bindParam(":password", $passwordhash);
				$stmt->execute();
				
				if (!empty($errorMessage)) {
					echo($errorMessage);
				} else {
					// TODO: send confirmation email
					echo("<p id=\"label\">Registration successful! A confirmation email has been sent to " . $email . ".</p>");
				}
			} ?>
		</div>
	</body>
</html>
