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

	$passwordValid = True;
	if (isset($_POST["id"]) && isset($_POST["password"]) && isset($_POST["confirmpassword"])) {
		$id = htmlspecialchars($_POST["id"]);
		$password = htmlspecialchars($_POST["password"]);
		$confirmpassword = htmlspecialchars($_POST["confirmpassword"]);

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

		$result = userFromEmail($email);

		// Check if email exists
		if (empty($result)) {
			$emailValid = False;
			$errorMessage = "<p id=\"error\">Email not found.</p>";
		} else {
			$emailValid = True;
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
					// Check for reset id
					if (isset($_GET["id"])) {
						$key = htmlspecialchars($_GET["id"]);
						$stmt = $conn->prepare("SELECT * FROM passwordresets WHERE resetkey = :key");
						$stmt->bindParam(":key", $key);
						$stmt->execute();
						$result = $stmt->fetch();

						// Check if id valid
						if (!empty($result)) {
							$id = htmlspecialchars($result["id"]);
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
						$result = userFromEmail($email);
						$id = $result["id"];

						// Ensure key is unique
						$key = uniqid();
						$stmt = $conn->prepare("SELECT * FROM passwordresets WHERE resetkey = :key");
						$stmt->bindParam(":key", $key);
						$stmt->execute();
						$result = $stmt->fetch();
						while (!empty($result)) {
							$key = uniqid();
							$stmt->execute();
							$result = $stmt->fetch();
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
