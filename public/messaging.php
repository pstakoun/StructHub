<?php
    session_start();
    if (!isset($_SESSION["id"])) {
        header("Location: login.php");
        die();
    }
	
	$errorMessage = "";
	// Connect to database
	try {
		$conn = new PDO("mysql:host=structhubdb.db.11405843.hostedresource.com;dbname=structhubdb", "structhubdb", "Cx!ak#Unm6Bknn54");
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch(PDOException $e) {
		$errorMessage = "<p id=\"error\">Could not connect to database.</p>";
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
            <div id = "conversations">
                <?php
					// Find contacts
					$contacts = [];
					$stmt = $conn->prepare("SELECT * FROM contacts WHERE user1 = :id AND status = 2");
					$stmt->bindParam(":id", $id);
					$stmt->execute();
					$result = $stmt->fetchAll();
					foreach ($result as $row) {
						$contacts[] = $row["user2"];
					}
					$stmt = $conn->prepare("SELECT * FROM contacts WHERE user2 = :id AND status = 2");
					$stmt->bindParam(":id", $id);
					$stmt->execute();
					$result = $stmt->fetchAll();
					foreach ($result as $row) {
						$contacts[] = $row["user1"];
					}
					
					// Get active conversations
					$conversations = [];
					$stmt = $conn->prepare("SELECT * FROM messages WHERE sender = :id OR recipient = :id");
					$stmt->bindParam(":id", $id);
					$stmt->execute();
					$result = $stmt->fetchAll();
					foreach ($result as $row) {
						$sender = $row["sender"];
						$recipient = $row["recipient"];
						if ($sender == $id && !in_array($recipient, $conversations)) {
							$conversations[] = $recipient;
						}
						else if ($recipient == $id && !in_array($sender, $conversations)) {
							$conversations[] = $sender;
						}
					}
					
					echo("<h2>Conversations</h2>");
					if (!empty($conversations)) {
						foreach ($conversations as $contact) {
							$stmt = $conn->prepare("SELECT * FROM users WHERE id = :contact");
							$stmt->bindParam(":contact", $contact);
							$stmt->execute();
							$result = $stmt->fetchAll();
							$row = $result[0];
							echo("<div id=\"conversation\">");
							echo("<p id=\"user\">" . $row["firstname"] . " " . $row["lastname"] . "</p>");
							// echo last message
							echo("</div>");
						}
					}
					else {
						echo("<h4>No active conversations.</h4>");
					}
				?>
            </div>
        </div>
            
	</body>
</html>
