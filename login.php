<?php
    session_start();
    if (isset($_SESSION["id"])) {
        header("Location: index.php");
		die();
    } else if (!empty($_POST)) {
        $email = htmlspecialchars($_POST["email"]);
        $password = htmlspecialchars($_POST["password"]);
		// Connect to database
		$connection = new mysqli("localhost", "pstakoun", "yJcRNzpSaEXatKqc", "socialnetwork");
		if ($connection->connect_error) {
			$errorMessage = "<p id=\"error\">Could not connect to database.<p>";
		}
		// Get user id from database
		$sql = "SELECT id FROM users WHERE email = \"" + $email + "\" AND password = \"" + $password + "\"";
		$result = $connection->query($sql);
		if ($result->num_rows == 0) {
			$errorMessage = "<p id=\"error\">Email or password invalid.<p>";
		// Set user id
		} else {
			$row = $result->fetch_assoc();
            $_SESSION["id"] = $row["id"];
            header("Location: index.php");
            die();
        }
    }
?>

<!DOCTYPE HTML>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Social Network</title>
		<link rel="stylesheet" href="style.css">
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
