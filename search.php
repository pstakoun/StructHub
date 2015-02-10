<?php
    session_start();
    if (!isset($_SESSION["id"])) {
        header("Location: login.php");
        die();
    }
	$id = $_SESSION["id"];
	$query = $_POST["query"];
	$type = $_POST["type"];
	
	// Connect to database
	$connection = new mysqli("localhost", "pstakoun", "yJcRNzpSaEXatKqc", "socialnetwork");
	if ($connection->connect_error) {
		$errorMessage = "<p id=\"error\">Could not connect to database.</p>";
	}
	
	// Get contacts
	function getContacts($connection, $id) {
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
		return $contacts;
	}
?>

<!DOCTYPE HTML>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Social Network</title>
		<link rel="stylesheet" href="style.css">
	</head>
	
	<body>
		<div id="titleBar">
			<div id="titleBarWrap">
				<div id="titleBarLogo">
					<a href="index.php"><img src="images/logo.png" width=48px height=48px></a>
				</div>
                <div>
                    <h1>Social Network</h1>
                </div>
			</div>
		</div>
		
        <div id="content">
            <div id = "results">
                <?php
					
					// Display search results
					switch ($type) {
						case "user":
							// Get results
							$results = getContacts($connection, $id);
							foreach ($results as $user) {
								$sql = "SELECT * FROM users WHERE id = \"" . $user . "\"";
								$result = $connection->query($sql);
								$row = $result->fetch_assoc();
								$name = $row["firstname"] . " " . $row["lastname"];
								echo("<a id=\"user\" href=\"index.php\">" . $name . "</a>");
							}
							break;
						default:
							if (empty($errorMessage)) { $errorMessage = "<p id=\"error\">No results found.</p>"; }
					}
					if (!empty($errorMessage)) { echo($errorMessage); }
				?>
            </div>

            <div id = "sidebar">
                <a id ="nav" href="index.php">Home</a><br>
				<a id ="nav" href="profile.php">Profile</a><br>
                <a id ="nav" href="contacts.php">Contacts</a><br>
                <a id ="nav" href="messaging.php">Messaging</a><br>
                <a id ="nav" href="settings.php">Settings</a>
            </div>
        </div>
            
	</body>
</html>
