<?php
	require("lib/password.php");

    session_start();
    if (isset($_SESSION["id"])) {
        header("Location: index.php");
		die();
    }
	else if (!empty($_POST)) {
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
		
		// Get user id from database
		$stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
		$stmt->bindParam(":email", $email);
		$stmt->execute();
		$result = $stmt->fetchAll();
		
		if (empty($result)) {
			if (empty($errorMessage)) { $errorMessage = "<p id=\"error\">Email or password invalid.</p>"; }
		// Set user id
		} else {
			$row = $result[0];
			if (password_verify($password, $row["password"])) {
				$_SESSION["id"] = $row["id"];
				header("Location: index.php");
				die();
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
				<div id="titleBarLogo">
					<a href="index.php"><img src="images/logo.png" width=48px height=48px></a>
				</div>
                <div>
                    <h1>StructHub</h1>
                </div>
			</div>
		</div>
		
		<div id="form">
            <?php if (!empty($errorMessage)) { echo($errorMessage); } ?>
            <form method="post" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
				<table style="margin: 0 auto;">
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
						<td id="link"><a href="register.php">Don't have an account? Register now!</a></td>
					</tr>
				</table>
                
            </form>
		</div>
	</body>
</html>
