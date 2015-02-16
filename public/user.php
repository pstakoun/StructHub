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
	try {
		$conn = new PDO("mysql:host=structhubdb.db.11405843.hostedresource.com;dbname=structhubdb", "structhubdb", "Cx!ak#Unm6Bknn54");
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch(PDOException $e) {
		$errorMessage = "<p id=\"error\">Could not connect to database.</p>";
	}
	
	if (!empty($username)) {
		// Get user id
		$stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
		$stmt->bindParam(":username", $username);
		$stmt->execute();
		$result = $stmt->fetchAll();
		if (!empty($result)) {
			$row = $result[0];
			$userid = $row["id"];
			
			$stmt = $conn->prepare("SELECT * FROM contacts WHERE (user1 = :id AND user2 = :userid) OR (user1 = :userid AND user2 = :id)");
			$stmt->bindParam(":id", $id);
			$stmt->bindParam(":userid", $userid);
			$stmt->execute();
			$result = $stmt->fetchAll();
			if (empty($result)) {
				$stmt = $conn->prepare("INSERT INTO contacts(user1, user2, status) VALUES (:id, :userid, 0)");
				$stmt->bindParam(":id", $id);
				$stmt->bindParam(":userid", $userid);
				$stmt->execute();
			}
			else {
				if (isset($_POST["removeContact"])) {
					$stmt = $conn->prepare("UPDATE contacts SET status = 0 WHERE (user1 = :id AND user2 = :userid) OR (user1 = :userid AND user2 = :id)");
					$stmt->bindParam(":id", $id);
					$stmt->bindParam(":userid", $userid);
					$stmt->execute();
				}
				if (isset($_POST["acceptContactRequest"])) {
					$stmt = $conn->prepare("UPDATE contacts SET status = 2 WHERE (user1 = :id AND user2 = :userid) OR (user1 = :userid AND user2 = :id)");
					$stmt->bindParam(":id", $id);
					$stmt->bindParam(":userid", $userid);
					$stmt->execute();
				}
				if (isset($_POST["deleteContactRequest"])) {
					$stmt = $conn->prepare("UPDATE contacts SET status = 0 WHERE (user1 = :id AND user2 = :userid) OR (user1 = :userid AND user2 = :id)");
					$stmt->bindParam(":id", $id);
					$stmt->bindParam(":userid", $userid);
					$stmt->execute();
				}
				if (isset($_POST["addContact"])) {
					$stmt = $conn->prepare("UPDATE contacts SET status = 1 WHERE (user1 = :id AND user2 = :userid) OR (user1 = :userid AND user2 = :id)");
					$stmt->bindParam(":id", $id);
					$stmt->bindParam(":userid", $userid);
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
		<title>StructHub</title>
		<link rel="stylesheet" href="style.css">
	</head>
	
	<body>
		<div id="titleBar">
			<div id="titleBarWrap">
				<div id="titleBarLogo">
					<a href="index.php"><img src="images/logo.png" width=48px height=48px></a>
				</div>
                <div>
                    <h1>StructHub</h1>
                </div>
			</div>
		</div>
		
        <div id="content">
            <div id = "profile">
                <?php
					// Get user information
					$stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
					$stmt->bindParam(":username", $username);
					$stmt->execute();
					$result = $stmt->fetchAll();
					if (empty($username) || empty($result)) {
						echo("<p id=\"error\">User not found.</p>");
					}
					else {
						$row = $result[0];
						$userid = $row["id"];
						$firstname = $row["firstname"];
						$lastname = $row["lastname"];
						echo("<h2>" . $firstname . " " . $lastname . "</h2>");
						
						// Find contacts
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
						$sent = [];
						$received = [];
						$stmt = $conn->prepare("SELECT * FROM contacts WHERE user1 = :id AND status = 1");
						$stmt->bindParam(":id", $id);
						$stmt->execute();
						$result = $stmt->fetchAll();
						foreach ($result as $row) {
							$sent[] = $row["user2"];
						}
						$stmt = $conn->prepare("SELECT * FROM contacts WHERE user2 = :id AND status = 1");
						$stmt->bindParam(":id", $id);
						$stmt->execute();
						$result = $stmt->fetchAll();
						foreach ($result as $row) {
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
            
            <?php include_once("sidebar.php"); ?>
        </div>
        
	</body>
</html>
