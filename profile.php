<?php
    session_start();
	// Check for session
    if (!isset($_SESSION["id"])) {
        header("Location: login.php");
        die();
    }
	$id = $_SESSION["id"];
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
            <div id="profile">
                <?php
					$errorMessage = "";
					// Connect to database
					try {
						$conn = new PDO("mysql:host=structhubdb.db.11405843.hostedresource.com;dbname=structhubdb", "structhubdb", "Cx!ak#Unm6Bknn54");
						$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					} catch(PDOException $e) {
						$errorMessage = "<p id=\"error\">Could not connect to database.</p>";
					}
					
					// Get user information
					$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
					$stmt->bindParam(":id", $id);
					$stmt->execute();
					$result = $stmt->fetchAll();
					$row = $result[0];
					
					$firstname = $row["firstname"];
					$lastname = $row["lastname"];
					$email = $row["email"];
					
					// Display user information
					echo("<h2>" . $firstname . " " . $lastname . "</h2>");
					echo("<p id=\"label\">Email: " . $email . "</p>");
					
					if (!empty($errorMessage)) {
						echo($errorMessage);
					}
				?>
            </div>
        </div>
        
	</body>
</html>
