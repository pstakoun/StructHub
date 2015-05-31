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
		foreach ($messages as $message) {
			$stmt->bindParam(":sender", $message["sender"]);
			$stmt->execute();
			$result = $stmt->fetch();
			$timestamp = $message["datecreated"];

			// Display message
			echo("<div id=\"message\">");
				echo("<a id=\"user\" href=\"user.php?id=" . $result["username"] . "\">" . $result["firstname"] . " " . $result["lastname"] . "</a>");
				echo("<div id=\"timestamp\">" . $timestamp . "</div>");
				echo("<p id=\"messageText\">" . $message["message"] . "</p>");
			echo("</div>");
		}
	}
	else {
		echo("<h4>No messages.</h4>");
	}
?>
