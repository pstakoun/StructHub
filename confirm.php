<?php
	// Import util functions
	require("util.php");
	// Import mailer
	require("lib/PHPMailer/PHPMailerAutoload.php");

	checkEmptySession();
	dbConnect();

	if (isset($_SESSION["errorMessage"])) {
		$errorMessage = $_SESSION["errorMessage"];
		unset($_SESSION["errorMessage"]);
	}

	if (empty($_SESSION["successMessage"]) && !empty($id)) {
		// Confirm account
		$stmt = $conn->prepare("UPDATE users SET confirmed = 1 WHERE id = :id");
		$stmt->bindParam(":id", $id);
		$stmt->execute();

		// PRG
		$_SESSION["successMessage"] = "<p id=\"label\">Confirmation successful. You can now log in to your account.</p>";
		header("Location: " . $_SERVER["PHP_SELF"]);
		die();
	}

	// Check if email set
	if (isset($_POST["email"])) {
		// Get email
		$email = htmlspecialchars($_POST["email"]);

		$result = userFromEmail($email);

		// Check if email exists
		if (empty($result)) {
			$errorMessage = "<p id=\"error\">Email not found.</p>";
		} else if ($result["confirmed"] == 1) {
			$errorMessage = "<p id=\"error\">Account already confirmed.</p>";
		} else {
			$id = $result["id"];

			$stmt = $conn->prepare("SELECT * FROM confirmations WHERE id = :id");
			$stmt->bindParam(":id", $id);
			$stmt->execute();
			$result = $stmt->fetchAll();
			$key;
			if (!empty($result)) {
				$key = $result["confirmkey"];
			} else {
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
			}

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
				$_SESSION["successMessage"] = "<p id=\"label\">A confirmation email has been sent to " . $email . ".</p>";
			} else {
				$_SESSION["successMessage"] = $mail->ErrorInfo;
			}
		}
	}
	// Check if confirmation key set
	else if (isset($_GET["id"])) {
		$key = htmlspecialchars($_GET["id"]);
		$stmt = $conn->prepare("SELECT * FROM confirmations WHERE confirmkey = :key");
		$stmt->bindParam(":key", $key);
		$stmt->execute();
		$result = $stmt->fetch();

		// Check if id valid
		if (!empty($result)) {
			$id = htmlspecialchars($result["id"]);
		}
	}

	echoHeader(0);
?>
		<div id="form">
			<?php
				if (isset($_SESSION["successMessage"])) {
					echo($_SESSION["successMessage"]);
					unset($_SESSION["successMessage"]);
				} else if (empty($id)) { ?>
					<form method="post" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
						<table style="margin: 0 auto;">
							<?php if (!empty($errorMessage)) { echo("<tr><td colspan=\"3\">" . $errorMessage . "</td></tr>"); } ?>
							<tr>
								<td id="label" align="right">Email: </td>
								<td id="label"><input type="email" name="email" /></td>
								<td id="label" align="right"><input type="submit" name="resend" value="Resend Confirmation Link" /></td>
							</tr>
						</table>
					</form>
			<?php
				}
			?>
		</div>
	</body>
</html>
