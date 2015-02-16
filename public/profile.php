<?php
    session_start();
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
					<a href="index.php"><img src="images/logo.png" width=48px height=48px></a>
				</div>
                <div>
                    <h1>StructHub</h1>
                </div>
			</div>
		</div>
		
        <div id="content">
            <div id = "profile">
                <?php
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
					
					if (!empty($errorMessage)) {
						echo($errorMessage);
					}
					echo("<h2>" . $firstname . " " . $lastname . "</h2>");
					echo("<p id=\"label\">Email: " . $email . "</p>");
				?>
            </div>
            
            <?php include_once("sidebar.php"); ?>
        </div>
        
	</body>
</html>
