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
		
        <div id="content">
            <div id = "settings">
                <form method="post" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
                    <p id="label"><input type="submit" name="logout" value="Logout" /></p>
                </form>
            </div>

            <?php include_once("sidebar.php"); ?>
        </div>
        
	</body>
</html>