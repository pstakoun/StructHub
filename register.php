<?php
	// Import util functions
	require("util.php");
	// Import library for backwards compatibility
	require("lib/password.php");
	// Import mailer
	require("lib/PHPMailer/PHPMailerAutoload.php");

	checkEmptySession();
	dbConnect();

	if (isset($_SESSION["errorMessage"])) {
		$errorMessage = $_SESSION["errorMessage"];
		unset($_SESSION["errorMessage"]);
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

	echoHeader(0);
?>
		<div id="form">
			<?php
				if (isset($_SESSION["successMessage"])) {
					echo($_SESSION["successMessage"]);
					unset($_SESSION["successMessage"]);
				} else {
					// Display registration form if submitted details are invalid
					if (!$postValid) {	?>
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
			<?php 	} else {
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

						// Ensure key is unique
						$key = uniqid();
						$stmt = $conn->prepare("SELECT * FROM confirmations WHERE confirmkey = :key");
						$stmt->bindParam(":key", $key);
						$stmt->execute();
						$result = $stmt->fetchAll();
						while (!empty($result)) {
							$key = uniqid();
							$stmt->execute();
							$result = $stmt->fetchAll();
						}

						// Create confirmation key
						$stmt = $conn->prepare("INSERT INTO confirmations (id, confirmkey) VALUES (:id, :key)");
						$stmt->bindParam(":id", $id);
						$stmt->bindParam(":key", $key);
						$stmt->execute();

						// PRG
						if (!empty($errorMessage)) {
							$_SESSION["errorMessage"] = $errorMessage;
						} else {
							// Send confirmation email
							$subject = "StructHub Account Confirmation";
							$message = "
								<html>
									<body>
										<p>Use this link to confirm your StructHub account.</p>
										<p><a href=\"http://structhub.com/confirm.php?id=" . $key . "\">" . "http://structhub.com/confirm.php?id=" . $key . "</a></p>
									</body>
								</html>
							";
							$altmessage = "Use this link to confirm your StructHub account: http://structhub.com/confirm.php?id=" . $key;

							$mail = new PHPMailer();
                            //$mail->SMTPAuth = false;
                            //$mail->SMTPSecure = "ssl";
                            //$mail->Port = 995;
							$mail->isSMTP();
							$mail->Host = 'relay-hosting.secureserver.net';
							$mail->SetFrom('donotreply@structhub.com', 'StructHub');
							$mail->AddReplyTo('donotreply@structhub.com', 'StructHub');
							$mail->addAddress($email);
							$mail->isHTML(true);

							$mail->Subject = $subject;
							$mail->Body = $message;
							$mail->AltBody = $altmessage;
							if($mail->send()) {
								$_SESSION["successMessage"] = "<p id=\"label\">Registration successful! A confirmation email has been sent to " . $email . ".</p>";
							} else {
								$_SESSION["successMessage"] = $mail->ErrorInfo;
							}
						}
						// PRG
						header("Location: " . $_SERVER['REQUEST_URI']);
						die();
					}
				} ?>
		</div>
	</body>
</html>
