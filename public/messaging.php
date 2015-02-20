<?php
    session_start();
	// Check for session
    if (!isset($_SESSION["id"])) {
        header("Location: login.php");
        die();
    }
	$id = $_SESSION["id"];
	
	// Get user from url
	$activeConversation = null;
	if (!empty($_GET["id"])) {
		$activeConversation = htmlspecialchars($_GET["id"]);
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
					$stmt = $conn->prepare("SELECT * FROM messages WHERE sender = :id OR recipient = :id ORDER BY datecreated DESC");
					$stmt->bindParam(":id", $id);
					$stmt->execute();
					$messages = $stmt->fetchAll();
					// Store found conversations
					foreach ($messages as $row) {
						$sender = $row["sender"];
						$recipient = $row["recipient"];
						if ($sender == $id && !in_array($recipient, $conversations)) {
							$conversations[] = $recipient;
						}
						else if ($recipient == $id && !in_array($sender, $conversations)) {
							$conversations[] = $sender;
						}
					}
				?>
				<h2>Conversations</h2>
				<?php
					// Check for ongoing conversations
					if (!empty($conversations)) {
						// Display conversations
						foreach ($conversations as $contact) {
							// Get user from id
							$stmt = $conn->prepare("SELECT * FROM users WHERE id = :contact");
							$stmt->bindParam(":contact", $contact);
							$stmt->execute();
							$result = $stmt->fetchAll();
							$row = $result[0];
							// Display conversation
							echo("<div id=\"conversation\">");
							echo("<a id=\"user\" href=\"messaging.php?id=" . $row["username"] . "\">" . $row["firstname"] . " " . $row["lastname"] . "</a>");
							// TODO: echo("<p id=\"message\">" . $row["message"] . "</p>");
							echo("</div>");
						}
					}
				?>
            </div>
			<div id = "activeConversation">
				<?php
					// Check for active conversation
					if (!empty($activeConversation)) {
						// Get user from id
						$stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
						$stmt->bindParam(":username", $activeConversation);
						$stmt->execute();
						$result = $stmt->fetchAll();
						// Display user if found
						if (!empty($result)) {
							$row = $result[0];
							echo("<h2>" . $row["firstname"] . " " . $row["lastname"] . "</h2>");
						} else {
							echo("<h2>User not found.</h2>");
						}
					}
					else {
						echo("<h3>No active conversation.</h3>");
					}
				?>
			</div>
        </div>
            
	</body>
</html>
