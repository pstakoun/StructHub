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
            <div id = "contacts">
				<h2>Contacts</h2>
                <?php
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
					$stmt = $conn->prepare("SELECT * FROM contacts WHERE user1 = :id AND status = 1");
					$stmt->bindParam(":id", $id);
					$stmt->execute();
					$result = $stmt->fetchAll();
					foreach ($result as $row) {
						$contacts[] = $row["user2"];
					}
					$stmt = $conn->prepare("SELECT * FROM contacts WHERE user2 = :id AND status = 1");
					$stmt->bindParam(":id", $id);
					$stmt->execute();
					$result = $stmt->fetchAll();
					foreach ($result as $row) {
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
						$stmt = $conn->prepare("SELECT * FROM users WHERE id = :contact");
						$stmt->bindParam(":contact", $contact);
						$stmt->execute();
						$result = $stmt->fetchAll();
						$row = $result[0];
						$name = $row["firstname"] . " " . $row["lastname"];
						echo("<a id=\"user\" href=\"user.php?id=" . $row["username"] . "\">" . $name . "</a><br>");
					}
					
				?>
            </div>
            
            <?php include_once("sidebar.php"); ?>
        </div>
        
	</body>
</html>
