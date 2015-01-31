<?php
    session_start();
    if (isset($_SESSION["id"])) {
        header("Location: index.php");
            die();
    } else if (!empty($_POST)) {
        $email = htmlspecialchars($_POST["email"]);
        $password = htmlspecialchars($_POST["password"]);
        // Find user in database
        if (True) {
            $_SESSION["id"] = 0; // Get id from database
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
