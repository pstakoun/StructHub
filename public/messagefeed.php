<?php
	echo("<h2>Messages</h2>");
	// Get messages
	$stmt = $conn->prepare("SELECT * FROM messages WHERE recipient = :id");
	$stmt->bindParam(":id", $id);
	$stmt->execute();
	$messages = $stmt->fetchAll();
	
	echo("<div id=\"conversation\">");
	if (!empty($messages)) {
		foreach ($messages as $message) {
			echo("<p id=\"user\">" . $message["firstname"] . " " . $message["lastname"] . "</p>");
			echo("<p id=\"message\">" . $message["message"] . "</p>");
		}
	}
	else {
		echo("<h4>No messages.</h4>");
	}
	echo("</div>");
?>
