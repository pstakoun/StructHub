<?php
    session_start();
    if (!isset($_SESSION["id"])) {
        header("Location: login.php");
        die();
    }
    if (isset($_POST["logout"])) {
        session_unset();
        session_destroy(); 
        header("Location: login.php");
        die();
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
		
        <div id="content">
            <div id = "settings">
                <form method="post" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
                    <p id="label"><input type="submit" name="logout" value="Logout" /></p>
                </form>
            </div>

            <div id = "sidebar">
                <a id ="nav" href="index.php">Home</a><br>
				<a id ="nav" href="profile.php">Profile</a><br>
                <a id ="nav" href="contacts.php">Contacts</a><br>
                <a id ="nav" href="messaging.php">Messaging</a><br>
                <a id ="nav" href="#">Settings</a>
            </div>
        </div>
        
	</body>
</html>
