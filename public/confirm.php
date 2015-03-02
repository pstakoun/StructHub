<?php
	session_start();
	// Check for session
	if (isset($_SESSION["id"])) {
		header("Location: index.php");
		die();
	}
	
	ob_start();
	
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
	
	$id = null;
	// Check if email set
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
			$errorMessage = "<p id=\"error\">Email not found.</p>";
		} else if ($result[0]["confirmed"] == 1) {
			$errorMessage = "<p id=\"error\">Account already confirmed.</p>";
		} else {
			$id = $result[0]["id"];
			
			$stmt = $conn->prepare("SELECT * FROM confirmations WHERE id = :id");
			$stmt->bindParam(":id", $id);
			$stmt->execute();
			$result = $stmt->fetchAll();
			$key = "";
			if (!empty($result)) {
				$key = $result[0]["confirmkey"];
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
			$headers = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type:text/html; charset=UTF-8" . "\r\n";
			$headers .= "From: donotreply@structhub.com";
			mail($email, $subject, $message, $headers);
			$_SESSION["successMessage"] = "<p id=\"label\">Confirmation email sent.</p>";
		}
	}
	// Check if confirmation key set
	else if (isset($_GET["id"])) {
		$key = $_GET["id"];
		$stmt = $conn->prepare("SELECT * FROM confirmations WHERE confirmkey = :key");
		$stmt->bindParam(":key", $key);
		$stmt->execute();
		$result = $stmt->fetchAll();
		
		// Check if id valid
		if (!empty($result)) {
			$id = htmlspecialchars($result[0]["id"]);
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
				} else if (!empty($id)) {
					// Confirm account
					$stmt = $conn->prepare("UPDATE users SET confirmed = 1 WHERE id = :id");
					$stmt->bindParam(":id", $id);
					$stmt->execute();
					
					// PRG
					$_SESSION["successMessage"] = "<p id=\"label\">Confirmation successful. You can now log in to your account.</p>";
					header("Location: " . $_SERVER["PHP_SELF"]);
					die();
				} else { ?>
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
