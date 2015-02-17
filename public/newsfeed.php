<?php
	$errorMessage = "";
	// Connect to database
	try {
		$conn = new PDO("mysql:host=structhubdb.db.11405843.hostedresource.com;dbname=structhubdb", "structhubdb", "Cx!ak#Unm6Bknn54");
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch(PDOException $e) {
		$errorMessage = "<p id=\"error\">Could not connect to database.</p>";
	}
	
	// Get contacts
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
	
	// Get news
	/*$news = [];
	foreach ($contacts as $contact) {
		$stmt = $conn->prepare("SELECT * FROM updates WHERE posterid = :contact");
		$stmt->bindParam(":contact", $contact);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$news[] = $row;
		}
	}*/
	for ($i = 0; $i < count($contacts); $i++) {
		$contacts[$i] = "\"" . $contacts[$i] . "\"";
	}
	$news = [];
	$ids = "\"" . $id . "\"";
	if (!empty($contacts)) {
		$ids = $ids . "," . join(",", $contacts);
	}
	$stmt = $conn->prepare("SELECT * FROM updates WHERE posterid IN (" . $ids . ")");
	//$stmt->bindParam(":ids", $ids);
	$stmt->execute();
	$news = $stmt->fetchAll();
	
	echo("<h2>News</h2>");
	
	foreach (array_reverse($news) as $status) {
		$stmt = $conn->prepare("SELECT * FROM users WHERE id = :posterid");
		$stmt->bindParam(":posterid", $status["posterid"]);
		$stmt->execute();
		$result = $stmt->fetchAll();
		$row = $result[0];
		$name = $row["firstname"] . " " . $row["lastname"];
		$timestamp = $status["datecreated"];
		echo("<div id=\"statusUpdate\">");
			echo("<a id=\"user\" href=\"user.php?id=" . $row["username"] . "\">" . $name . "</a>");
			echo("<div id=\"timestamp\">" . $timestamp . "</div>");
			echo("<p id=\"status\">" . $status["status"] . "</p>");
		echo("</div>");
	}
?>
