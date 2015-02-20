<?php
	echo("<h2>Messages</h2>");
	// Get messages from database
	$stmt = $conn->prepare("SELECT * FROM messages WHERE recipient = :id ORDER BY datecreated DESC");
	$stmt->bindParam(":id", $id);
	$stmt->execute();
	$messages = $stmt->fetchAll();
	
	// Show messages
	if (!empty($messages)) {
		$stmt = $conn->prepare("SELECT * FROM users WHERE id = :sender");
		// Create message feed
		foreach (array_reverse($messages) as $message) {
			$stmt->bindParam(":sender", $message["sender"]);
			$stmt->execute();
			$result = $stmt->fetchAll();
			$sender = $result[0];
			$timestamp = $message["datecreated"];
			
			// Display message
			echo("<div id=\"receivedMessage\">");
				echo("<a id=\"user\" href=\"user.php?id=" . $sender["username"] . "\">" . $sender["firstname"] . " " . $sender["lastname"] . "</a>");
				echo("<div id=\"timestamp\">" . $timestamp . "</div>");
				echo("<p id=\"message\">" . $message["message"] . "</p>");
			echo("</div>");
		}
	}
	else {
		echo("<h4>No messages.</h4>");
	}
?>
