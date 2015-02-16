<?php
    session_start();
    if (!isset($_SESSION["id"])) {
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
					<a href="index.php"><img src="images/logo.png" width=32px height=32px></a>
				</div>
                <?php include_once("menu.php"); ?>
			</div>
		</div>
		
        <div id="content">
            <div id = "teams">
                <!-- Display teams -->
            </div>
        </div>
            
	</body>
</html>
