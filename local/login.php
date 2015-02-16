<?php
    session_start();
    if (isset($_SESSION["id"])) {
        header("Location: index.php");
		die();
    } else if (!empty($_POST)) {
        $email = htmlspecialchars($_POST["email"]);
        $password = htmlspecialchars($_POST["password"]);
		
		// Connect to database
		try {
			$conn = new PDO("mysql:host=localhost;dbname=socialnetwork", "pstakoun", "yJcRNzpSaEXatKqc");
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
		
		<div id="login">
            <?php if (!empty($errorMessage)) { echo($errorMessage); } ?>
            <form method="post" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
                <p id="label">Email: <input type="email" name="email" /></p>
                <p id="label">Password: <input type="password" name="password" /></p>
                <p id="label"><input type="submit" name="login" value="Login" /> <a id="link" href="register.php">Don't have an account? Register now!</a></p>
            </form>
		</div>
	</body>
</html>