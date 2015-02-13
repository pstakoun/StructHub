<?php
    session_start();
    if (!isset($_SESSION["id"])) {
        header("Location: login.php");
        die();
    }
	$id = $_SESSION["id"];
	$query = "";
	$type = null;
	if (isset($_POST["query"])) { $query = $_POST["query"]; }
	if (isset($_POST["type"])) { $type = $_POST["type"]; }
	
	// Connect to database
	$connection = new mysqli("localhost", "pstakoun", "yJcRNzpSaEXatKqc", "socialnetwork");
	if ($connection->connect_error) {
		$errorMessage = "<p id=\"error\">Could not connect to database.</p>";
	}
	
	// Get users
	function getUsers($connection, $id, $query)
	{
		// Find contacts
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
		
		// Find users
		$users = [];
		$tempusers = [];
		$name = preg_split('/\s+/', $query);
		
		if (count($name) == 1) {
			$n = $name[0];
			$sql = "SELECT * FROM users WHERE firstname LIKE \"" . $n . "\" OR lastname LIKE \"" . $n . "\"";
			$result = $connection->query($sql);
			while ($row = $result->fetch_assoc()) {
				if (in_array($row["id"], $contacts)) {
					array_unshift($users, $row["id"]);
				}
				else {
					$users[] = $row["id"];
				}
			}
		}
		else {
			foreach ($name as $n) {
				$sql = "SELECT * FROM users WHERE firstname LIKE \"" . $n . "\" OR lastname LIKE \"" . $n . "\"";
				$result = $connection->query($sql);
				while ($row = $result->fetch_assoc()) {
					if (in_array($row["id"], $tempusers)) {
						if (!in_array($row["id"], $users)) {
							if (in_array($row["id"], $contacts)) {
								array_unshift($users, $row["id"]);
							}
							else {
								$users[] = $row["id"];
							}
						}
					}
					else {
						$tempusers[] = $row["id"];					
					}
				}
			}
		}
		return $users;
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
			<form method="post" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
				<p id="label">Search for user: <input type="text" name="query" value=<?php echo($query); ?> />
				<input type="hidden" name="type" value="user" />
				<input type="submit" name="search" value="Search" /></p>
			</form>
            <div id = "results">
                <?php
					// Display search results
					switch ($type) {
						case "user":
							// Get results
							$users = getUsers($connection, $id, $query);
							if (count($users) == 0) {
								if (empty($errorMessage)) { $errorMessage = "<p id=\"error\">No results found.</p>"; }
							}
							foreach ($users as $u) {
								$sql = "SELECT * FROM users WHERE id = \"" . $u . "\"";
								$result = $connection->query($sql);
								$row = $result->fetch_assoc();
								$name = $row["firstname"] . " " . $row["lastname"];
								echo("<a id=\"user\" href=\"user.php?id=" . $row["username"] . "\">" . $name . "</a>");
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
