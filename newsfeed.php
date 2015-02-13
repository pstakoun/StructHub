<?php
	// Connect to database
	$connection = new mysqli("localhost", "pstakoun", "yJcRNzpSaEXatKqc", "socialnetwork");
	if ($connection->connect_error) {
		$errorMessage = "<p id=\"error\">Could not connect to database.</p>";
	}
	
	// Get contacts
	$contacts = [];
	//$sql = "SELECT * FROM contacts WHERE user1 = \"" . $id . "\" AND status = 2";
	$sql = "SELECT * FROM contacts WHERE user1 = ? AND status = 2";
	//$result = $connection->query($sql);
	$stmt = $connection->prepare($sql);
	$stmt->bind_param("s", $id);
	$stmt->execute();
	$result = $stmt->get_result();
	//while ($row = $result->fetch_assoc()) {
	while ($row = $result->fetch_array()) {
		$contacts[] = $row["user2"];
	}
	//$sql = "SELECT * FROM contacts WHERE user2 = \"" . $id . "\" AND status = 2";
	$sql = "SELECT * FROM contacts WHERE user2 = ? AND status = 2";
	//$result = $connection->query($sql);
	$stmt = $connection->prepare($sql);
	$stmt->bind_param("s", $id);
	$stmt->execute();
	$result = $stmt->get_result();
	//while ($row = $result->fetch_assoc()) {
	while ($row = $result->fetch_array()) {
		$contacts[] = $row["user1"];
	}
	
	// Get news
	$news = [];
	foreach ($contacts as $contact) {
		//$sql = "SELECT * FROM updates WHERE posterid = \"" . $contact . "\"";
		$sql = "SELECT * FROM updates WHERE posterid = ?";
		//$result = $connection->query($sql);
		$stmt = $connection->prepare($sql);
		$stmt->bind_param("s", $contact);
		$stmt->execute();
		$result = $stmt->get_result();
		//while ($row = $result->fetch_assoc()) {
		while ($row = $result->fetch_array()) {
			$news[] = $row;
		}
	}
	
	echo("<h2>News</h2>");
	
	foreach (array_reverse($news) as $status) {
		//$sql = "SELECT * FROM users WHERE id = \"" . $status["posterid"] . "\"";
		$sql = "SELECT * FROM users WHERE id = ?";
		//$result = $connection->query($sql);
		$stmt = $connection->prepare($sql);
		$stmt->bind_param("s", $status["posterid"]);
		$stmt->execute();
		$result = $stmt->get_result();
		//$row = $result->fetch_assoc();
		$row = $result->fetch_array();
		$name = $row["firstname"] . " " . $row["lastname"];
		echo("<div id=\"statusUpdate\">");
			echo("<a id=\"user\" href=\"user.php?id=" . $row["username"] . "\">" . $name . "</a>");
			echo("<p id=\"status\">" . $status["status"] . "</p>");
		echo("</div>");
	}
?>
