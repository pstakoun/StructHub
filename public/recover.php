<?php
	session_start();
	// Check for session
	if (isset($_SESSION["id"])) {
		header("Location: index.php");
		die();
	}
	
	ob_start();
	
	// Import mailer
	require("lib/PHPMailer/PHPMailerAutoload.php");
	
	$errorMessage = "";
	if (isset($_SESSION["errorMessage"])) {
		$errorMessage = $_SESSION["errorMessage"];
		unset($_SESSION["errorMessage"]);
	}
	// Connect to database
	try {
		$conn = new PDO("mysql:host=structhubdb.db.11405843.hostedresource.com;dbname=structhubdb", "structhubdb", "Cx!ak#Unm6Bknn54");
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch(PDOException $e) {
		$errorMessage = "<p id=\"error\">Could not connect to database.</p>";
	}
	
	$passwordValid = True;
	if (isset($_POST["id"]) && isset($_POST["password"]) && isset($_POST["confirmpassword"])) {
		$id = $_POST["id"];
		$password = $_POST["password"];
		$confirmpassword = $_POST["confirmpassword"];
		
		// Validate password
		if (strlen($password) < 6) {
			$passwordValid = False;
			$errorMessage = "<p id=\"error\">Password too short.</p>";
		// Validate password confirmation
		} else if ($password != $confirmpassword) {
			$passwordValid = False;
			$errorMessage = "<p id=\"error\">Passwords do not match.</p>";
		}
		
		if ($passwordValid) {
			// Hash password
			$passwordhash = password_hash($password, PASSWORD_DEFAULT);
			
			// Update password
			$stmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :id");
			$stmt->bindParam(":password", $passwordhash);
			$stmt->bindParam(":id", $id);
			$stmt->execute();
			
			unset($_SESSION["resetid"]);
			// PRG
			$_SESSION["successMessage"] = "<p id=\"label\">Your password has been changed.</p>";
			header("Location: " . $_SERVER["PHP_SELF"]);
			die();
		}
	}
	
	// Initialize email validity
	$emailValid = False;
	if (isset($_POST["email"])) {
		// Get email
		$email = htmlspecialchars($_POST["email"]);
		
		// Check for email in database
		$stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
		$stmt->bindParam(":email", $email);
		$stmt->execute();
		$result = $stmt->fetchAll();
		
		// Check if email exists
		if (empty($result)) {
			$emailValid = False;
			$errorMessage = "<p id=\"error\">Email not found.</p>";
		} else {
			$emailValid = True;
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
				if (isset($_SESSION["successMessage"])) {
					echo($_SESSION["successMessage"]);
					unset($_SESSION["successMessage"]);
				} else {
					// Check for reset id
					if (isset($_GET["id"])) {
						$key = $_GET["id"];
						$stmt = $conn->prepare("SELECT * FROM passwordresets WHERE resetkey = :key");
						$stmt->bindParam(":key", $key);
						$stmt->execute();
						$result = $stmt->fetchAll();
						
						// Check if id valid
						if (!empty($result)) {
							$id = htmlspecialchars($result[0]["id"]);
							$_SESSION["resetid"] = $id; ?>
							<form method="post" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
								<table style="margin: 0 auto;">
									<?php if (!empty($errorMessage)) { echo("<tr><td colspan=\"2\">" . $errorMessage . "</td></tr>"); } ?>
									<input type="hidden" name="id" value=<?php echo("\"" . $id . "\"") ?> />
									<tr>
										<td id="label" align="right">Password: </td>
										<td id="label"><input type="password" name="password" /></td>
									</tr>
									<tr>
										<td id="label" align="right">Confirm Password: </td>
										<td id="label"><input type="password" name="confirmpassword" /></td>
									</tr>
									<tr>
										<td id="label" align="right"><input type="submit" name="changepassword" value="Change Password" /></td>
									</tr>
								</table>
							</form>
					<?php
						} else { ?>
							<form method="post" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
								<table style="margin: 0 auto;">
									<?php if (!empty($errorMessage)) { echo("<tr><td colspan=\"2\">" . $errorMessage . "</td></tr>"); } ?>
									<tr>
										<td id="label" align="right">Email: </td>
										<td id="label"><input type="email" name="email" /></td>
										<td id="label" align="right"><input type="submit" name="reset" value="Reset Password" /></td>
									</tr>
								</table>
							</form>
					<?php
						}
					}
					// Check if password invalid
					else if (!$passwordValid) {
						$id = $_SESSION["resetid"]; ?>
						<form method="post" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
								<table style="margin: 0 auto;">
									<?php if (!empty($errorMessage)) { echo("<tr><td colspan=\"2\">" . $errorMessage . "</td></tr>"); } ?>
									<input type="hidden" name="id" value=<?php echo("\"" . $id . "\"") ?> />
									<tr>
										<td id="label" align="right">Password: </td>
										<td id="label"><input type="password" name="password" /></td>
									</tr>
									<tr>
										<td id="label" align="right">Confirm Password: </td>
										<td id="label"><input type="password" name="confirmpassword" /></td>
									</tr>
									<tr>
										<td id="label" align="right"><input type="submit" name="changepassword" value="Change Password" /></td>
									</tr>
								</table>
							</form>
					<?php
					}
					// Check if email invalid
					else if (!$emailValid) { ?>
						<form method="post" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
							<table style="margin: 0 auto;">
								<?php if (!empty($errorMessage)) { echo("<tr><td colspan=\"2\">" . $errorMessage . "</td></tr>"); } ?>
								<tr>
									<td id="label" align="right">Email: </td>
									<td id="label"><input type="email" name="email" /></td>
									<td id="label" align="right"><input type="submit" name="reset" value="Reset Password" /></td>
								</tr>
							</table>
						</form>
			<?php 	} else {
						// Get user id from email
						$stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
						$stmt->bindParam(":email", $email);
						$stmt->execute();
						$result = $stmt->fetchAll();
						$row = $result[0];
						$id = $row["id"];
						
						// Ensure key is unique
						$key = uniqid();
						$stmt = $conn->prepare("SELECT * FROM passwordresets WHERE resetkey = :key");
						$stmt->bindParam(":key", $key);
						$stmt->execute();
						$result = $stmt->fetchAll();
						while (!empty($result)) {
							$key = uniqid();
							$stmt->execute();
							$result = $stmt->fetchAll();
						}
						
						// Create reset key
						$stmt = $conn->prepare("INSERT INTO passwordresets (id, resetkey) VALUES (:id, :key)");
						$stmt->bindParam(":id", $id);
						$stmt->bindParam(":key", $key);
						$stmt->execute();
						
						// PRG
						if (!empty($errorMessage)) {
							$_SESSION["errorMessage"] = $errorMessage;
						} else {
							// Send password reset email
							$subject = "StructHub Password Reset";
							$message = "
								<html>
									<body>
										<p>Use this link to reset your StructHub password. It will be valid for 24 hours.</p>
										<p><a href=\"http://structhub.com/recover.php?id=" . $key . "\">" . "http://structhub.com/recover.php?id=" . $key . "</a></p>
									</body>
								</html>
							";
							$altmessage = "Use this link to reset your StructHub password. It will be valid for 24 hours: http://structhub.com/recover.php?id=" . $key;
							
							$mail = new PHPMailer;

							$mail->isSMTP();
							$mail->Host = 'localhost';
							$mail->Port = 25;

							$mail->From = 'donotreply@structhub.com';
							$mail->addAddress($email);
							$mail->isHTML(true);

							$mail->Subject = $subject;
							$mail->Body = $message;
							$mail->AltBody = $altmessage;
							if($mail->send()) {
								$_SESSION["successMessage"] = "<p id=\"label\">A link to change your password has been sent to " . $email . ".</p>";
							} else {
								$_SESSION["successMessage"] = $mail->ErrorInfo;
							}
						}
						// PRG
						header("Location: " . $_SERVER["PHP_SELF"]);
						die();
					}
				} ?>
		</div>
	</body>
</html>
