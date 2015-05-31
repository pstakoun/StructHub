<?php
    // Get contacts
	$contacts = getContacts($id);

	// Format contacts for query
	for ($i = 0; $i < count($contacts); $i++) {
		$contacts[$i] = "\"" . $contacts[$i] . "\"";
	}
	$news = [];
	// Construct id list for query
	$ids = "\"" . $id . "\"";
	if (!empty($contacts)) {
		$ids = $ids . "," . join(",", $contacts);
	}
	// Get status updates from database
	$stmt = $conn->prepare("SELECT * FROM updates WHERE posterid IN (" . $ids . ") ORDER BY datecreated DESC");
	//$stmt->bindParam(":ids", $ids);
	$stmt->execute();
	$news = $stmt->fetchAll();

	echo("<h2>News</h2>");

	if (!empty($news)) {
		// Display status updates
		foreach ($news as $status) {
			// Get user from poster id
			$result = userFromID($status["posterid"]);
			$name = $result["firstname"] . " " . $result["lastname"];
			$timestamp = $status["datecreated"];

			// Display status update
			echo("<div id=\"statusUpdate\">");
				echo("<a id=\"user\" href=\"user.php?id=" . $result["username"] . "\">" . $name . "</a>");
				echo("<div id=\"timestamp\">" . $timestamp . "</div>");
				echo("<p id=\"status\">" . $status["status"] . "</p>");
			echo("</div>");
		}
	} else {
		echo("<h4>No news.</h4>");
	}
?>
