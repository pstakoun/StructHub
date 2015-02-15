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
						$sql = "SELECT * FROM contacts WHERE user1 = ? AND status = 1";
						$stmt = $connection->prepare($sql);
						$stmt->bind_param("s", $id);
						$stmt->execute();
						$result = $stmt->get_result();
						while ($row = $result->fetch_array()) {
							$contacts[] = $row["user2"];
						}
						$sql = "SELECT * FROM contacts WHERE user2 = ? AND status = 1";
						$stmt = $connection->prepare($sql);
						$stmt->bind_param("s", $id);
						$stmt->execute();
						$result = $stmt->get_result();
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
						$sql = "SELECT * FROM users WHERE id = ?";
						$stmt = $connection->prepare($sql);
						$stmt->bind_param("s", $contact);
						$stmt->execute();
						$result = $stmt->get_result();
						$row = $result->fetch_array();
						$name = $row["firstname"] . " " . $row["lastname"];
						echo("<a id=\"user\" href=\"user.php?id=" . $row["username"] . "\">" . $name . "</a><br>");
					}
					
				?>
            </div>
            
            <?php include_once("sidebar.php"); ?>
        </div>
        
	</body>
</html>
