<?php
    session_start();
    if (!isset($_SESSION["id"])) {
        header("Location: login.php");
        die();
    }
	$id = $_SESSION["id"];
	
	$username = null;
	if (isset($_GET["id"])) {
		$username = htmlspecialchars($_GET["id"]);
	}
	
	// Connect to database
	$connection = new mysqli("localhost", "pstakoun", "yJcRNzpSaEXatKqc", "socialnetwork");
	if ($connection->connect_error) {
		$errorMessage = "<p id=\"error\">Could not connect to database.</p>";
	}
	
	if (!empty($username)) {
		// Get user id
		$sql = "SELECT * FROM users WHERE username = ?";
		$stmt = $connection->prepare($sql);
		$stmt->bind_param("s", $username);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			$row = $result->fetch_array();
			$userid = $row["id"];
			
			$sql = "SELECT * FROM contacts WHERE (user1 = ? AND user2 = ?) OR (user1 = ? AND user2 = ?)";
			$stmt = $connection->prepare($sql);
			$stmt->bind_param("ssss", $id, $userid, $userid, $id);
			$stmt->execute();
			$result = $stmt->get_result();
			if ($result->num_rows == 0) {
				$sql = "INSERT INTO contacts(user1, user2, status) VALUES (?, ?, 0)";
				$stmt = $connection->prepare($sql);
				$stmt->bind_param("ss", $id, $userid);
				$stmt->execute();
			}
			else {
				if (isset($_POST["removeContact"])) {
					$sql = "UPDATE contacts SET status = 0 WHERE (user1 = ? AND user2 = ?) OR (user1 = ? AND user2 = ?)";
					$stmt = $connection->prepare($sql);
					$stmt->bind_param("ssss", $id, $userid, $userid, $id);
					$stmt->execute();
				}
				if (isset($_POST["acceptContactRequest"])) {
					$sql = "UPDATE contacts SET status = 2 WHERE (user1 = ? AND user2 = ?) OR (user1 = ? AND user2 = ?)";
					$stmt = $connection->prepare($sql);
					$stmt->bind_param("ssss", $id, $userid, $userid, $id);
					$stmt->execute();
				}
				if (isset($_POST["deleteContactRequest"])) {
					$sql = "UPDATE contacts SET status = 0 WHERE (user1 = ? AND user2 = ?) OR (user1 = ? AND user2 = ?)";
					$stmt = $connection->prepare($sql);
					$stmt->bind_param("ssss", $id, $userid, $userid, $id);
					$stmt->execute();
				}
				if (isset($_POST["addContact"])) {
					$sql = "UPDATE contacts SET status = 1 WHERE (user1 = ? AND user2 = ?) OR (user1 = ? AND user2 = ?)";
					$stmt = $connection->prepare($sql);
					$stmt->bind_param("ssss", $id, $userid, $userid, $id);
					$stmt->execute();
				}
			}
		}
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
            <div id = "profile">
                <?php
					// Get user information
					$sql = "SELECT * FROM users WHERE username = ?";
					$stmt = $connection->prepare($sql);
					$stmt->bind_param("s", $username);
					$stmt->execute();
					$result = $stmt->get_result();
					if ($username == null || $result->num_rows == 0) {
						echo("<p id=\"error\">User not found.</p>");
					}
					else {
						$row = $result->fetch_array();
						$userid = $row["id"];
						$firstname = $row["firstname"];
						$lastname = $row["lastname"];
						echo("<h2>" . $firstname . " " . $lastname . "</h2>");
						
						// Find contacts
						$contacts = [];
						$sql = "SELECT * FROM contacts WHERE user1 = ? AND status = 2";
						$stmt = $connection->prepare($sql);
						$stmt->bind_param("s", $id);
						$stmt->execute();
						$result = $stmt->get_result();
						while ($row = $result->fetch_array()) {
							$contacts[] = $row["user2"];
						}
						$sql = "SELECT * FROM contacts WHERE user2 = ? AND status = 2";
						$stmt = $connection->prepare($sql);
						$stmt->bind_param("s", $id);
						$stmt->execute();
						$result = $stmt->get_result();
						while ($row = $result->fetch_array()) {
							$contacts[] = $row["user1"];
						}
						$sent = [];
						$received = [];
						$sql = "SELECT * FROM contacts WHERE user1 = ? AND status = 1";
						$stmt = $connection->prepare($sql);
						$stmt->bind_param("s", $id);
						$stmt->execute();
						$result = $stmt->get_result();
						while ($row = $result->fetch_array()) {
							$sent[] = $row["user2"];
						}
						$sql = "SELECT * FROM contacts WHERE user2 = ? AND status = 1";
						$stmt = $connection->prepare($sql);
						$stmt->bind_param("s", $id);
						$stmt->execute();
						$result = $stmt->get_result();
						while ($row = $result->fetch_array()) {
							$received[] = $row["user1"];
						}
						
						if (in_array($userid, $contacts)) {
						?>
							<form method="post" action=<?php echo("user.php?id=" . $username); ?>>
								<p id="label"><input type="submit" name="removeContact" value="Remove Contact" /></p>
							</form>
						<?php
						}
						else if (in_array($userid, $sent)) {
						?>
							<form method="post" action=<?php echo("user.php?id=" . $username); ?>>
								<p id="label"><input type="submit" name="deleteContactRequest" value="Delete Contact Request" /></p>
							</form>
						<?php
						}
						else if (in_array($userid, $received)) {
						?>
							<form method="post" action=<?php echo("user.php?id=" . $username); ?>>
								<p id="label"><input type="submit" name="acceptContactRequest" value="Accept Contact Request" /></p>
							</form>
						<?php
						}
						else {
						?>
							<form method="post" action=<?php echo("user.php?id=" . $username); ?>>
								<p id="label"><input type="submit" name="addContact" value="Add Contact" /></p>
							</form>
						<?php
						}
					}
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
