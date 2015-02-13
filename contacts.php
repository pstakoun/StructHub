<?php
    session_start();
    if (!isset($_SESSION["id"])) {
        header("Location: login.php");
        die();
    }
	$id = $_SESSION["id"];
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
            <div id = "contacts">
				<h2>Contacts</h2>
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
				?>
					<form method="post" action="search.php">
						<p id="label">Search for user: <input type="text" name="query" />
						<input type="hidden" name="type" value="user" />
						<input type="submit" name="search" value="Search" /></p>
					</form>
				<?php
					foreach ($contacts as $contact) {
						//$sql = "SELECT * FROM users WHERE id = \"" . $contact . "\"";
						$sql = "SELECT * FROM users WHERE id = ?";
						//$result = $connection->query($sql);
						$stmt = $connection->prepare($sql);
						$stmt->bind_param("s", $contact);
						$stmt->execute();
						$result = $stmt->get_result();
						//$row = $result->fetch_assoc();
						$row = $result->fetch_array();
						$name = $row["firstname"] . " " . $row["lastname"];
						echo("<a id=\"user\" href=\"user.php?id=" . $row["username"] . "\">" . $name . "</a>");
					}
					
				?>
            </div>
            
            <div id = "sidebar">
                <a id ="nav" href="index.php">Home</a><br>
				<a id ="nav" href="profile.php">Profile</a><br>
                <a id ="nav" href="#">Contacts</a><br>
                <a id ="nav" href="messaging.php">Messaging</a><br>
                <a id ="nav" href="settings.php">Settings</a>
            </div>
        </div>
        
	</body>
</html>
