<?php
	// Connect to database
	$connection = new mysqli("localhost", "pstakoun", "yJcRNzpSaEXatKqc", "socialnetwork");
	if ($connection->connect_error) {
		$errorMessage = "<p id=\"error\">Could not connect to database.</p>";
	}
	
	// Get contacts
	$contacts = [];
	$sql = "SELECT * FROM contacts WHERE user1 = \"" . $id . "\" AND status = 2";
	$result = $connection->query($sql);
	while ($row = $result->fetch_assoc()) {
		$contacts[] = $row["user2"];
	}
	$sql = "SELECT * FROM contacts WHERE user2 = \"" . $id . "\" AND status = 2";
	$result = $connection->query($sql);
	while ($row = $result->fetch_assoc()) {
		$contacts[] = $row["user1"];
	}
	
	// Get news
	$news = [];
	foreach ($contacts as $contact) {
		$sql = "SELECT * FROM updates WHERE posterid = \"" . $contact . "\"";
		$result = $connection->query($sql);
		while ($row = $result->fetch_assoc()) {
			$news[] = $row;
		}
	}
	
	echo("<h2>News</h2>");
	
	foreach ($news as $status) {
		$sql = "SELECT * FROM users WHERE id = \"" . $status["posterid"] . "\"";
		$result = $connection->query($sql);
		$row = $result->fetch_assoc();
		$name = $row["firstname"] . " " . $row["lastname"];
		echo("<p id=\"contact\">" . $name . "</p>");
		echo("<p id=\"status\">" . $status["status"] . "</p>");
	}
?>
