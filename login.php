<?php
	// Import library for backwards compatibility
	require("lib/password.php");

    session_start();
	// Check for session
    if (isset($_SESSION["id"])) {
        header("Location: index.php");
		die();
    }
	else if (!empty($_POST)) {
		// Get submitted email and password
        $email = htmlspecialchars($_POST["email"]);
        $password = htmlspecialchars($_POST["password"]);
		
		$errorMessage = "";
		// Connect to database
		try {
			$conn = new PDO("mysql:host=structhubdb.db.11405843.hostedresource.com;dbname=structhubdb", "structhubdb", "Cx!ak#Unm6Bknn54");
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(PDOException $e) {
			$errorMessage = "<p id=\"error\">Could not connect to database.</p>";
		}
		
		// Attempt to get user id from given email
		$stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
		$stmt->bindParam(":email", $email);
		$stmt->execute();
		$result = $stmt->fetchAll();
		
		// Display error if user not found
		if (empty($result)) {
			if (empty($errorMessage)) { $errorMessage = "<p id=\"error\">Email or password invalid.</p>"; }
		} else {
			$row = $result[0];
			// Verify password
			if (password_verify($password, $row["password"])) {
				if ($result[0]["confirmed"] == 0) {
					$_SESSION["errorMessage"] = "<p id=\"error\">Confirm your account before logging in.</p>";
					header("Location: confirm.php");
					die();
				} else {
					// Create session
					$_SESSION["id"] = $row["id"];
					header("Location: index.php");
					die();
				}
			// Display error if password incorrect
			} else {
				if (empty($errorMessage)) { $errorMessage = "<p id=\"error\">Email or password invalid.</p>"; }
			}
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
                <div>
                    <h1>StructHub</h1>
                </div>
			</div>
		</div>
		
		<div id="form">
            <form method="post" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
				<table style="margin: 0 auto;">
					<?php if (!empty($errorMessage)) { echo("<tr><td colspan=\"2\">" . $errorMessage . "</td></tr>"); } ?>
					<tr>
						<td id="label" align="right">Email: </td>
						<td id="label"><input type="email" name="email" /></td>
					</tr>
					<tr>
						<td id="label" align="right">Password: </td>
						<td id="label"><input type="password" name="password" /></td>
					</tr>
					<tr>
						<td id="label" align="right"><input type="submit" name="login" value="Login" /></td>
						<td id="link"><a href="recover.php">Forgot your password?</a></td>
					</tr>
					<tr>
						<td id="link" colspan="2"><a href="register.php">Don't have an account? Register now!</a></td>
					</tr>
				</table>
                
            </form>
		</div>
	</body>
</html>
