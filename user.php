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
					// Connect to database
					$connection = new mysqli("localhost", "pstakoun", "yJcRNzpSaEXatKqc", "socialnetwork");
					if ($connection->connect_error) {
						$errorMessage = "<p id=\"error\">Could not connect to database.</p>";
					}
					
					// Get user information
					//$sql = "SELECT * FROM users WHERE username = \"" . $username . "\"";
					$sql = "SELECT * FROM users WHERE username = ?";
					//$result = $connection->query($sql);
					$stmt = $connection->prepare($sql);
					$stmt->bind_param("s", $username);
					$stmt->execute();
					$result = $stmt->get_result();
					if ($username == null || $result->num_rows == 0) {
						echo("<p id=\"error\">User not found.</p>");
					}
					else {
						//$row = $result->fetch_assoc();
						$row = $result->fetch_array();
						$firstname = $row["firstname"];
						$lastname = $row["lastname"];
						echo("<h2>" . $firstname . " " . $lastname . "</h2>");
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
