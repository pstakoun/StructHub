<?php
    session_start();
	// Check for session
    if (!isset($_SESSION["id"])) {
        header("Location: login.php");
        die();
    }
	$id = $_SESSION["id"];
	
	// Get query and type from url
	$query = "";
	$type = null;
	if (isset($_GET["query"])) { $query = $_GET["query"]; }
	if (isset($_GET["type"])) { $type = $_GET["type"]; }
	
	$errorMessage = "";
	// Connect to database
	try {
		$conn = new PDO("mysql:host=structhubdb.db.11405843.hostedresource.com;dbname=structhubdb", "structhubdb", "Cx!ak#Unm6Bknn54");
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch(PDOException $e) {
		$errorMessage = "<p id=\"error\">Could not connect to database.</p>";
	}
	
	// Get users
	function getUsers($conn, $id, $query)
	{
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
		
		// Find users
		$users = [];
		$tempusers = [];
		$name = preg_split('/\s+/', $query);
		// Check if query is only 1 argument
		if (count($name) == 1) {
			$n = $name[0];
			// Get users from query
			$stmt = $conn->prepare("SELECT * FROM users WHERE firstname = :n OR lastname = :n");
			$stmt->bindParam(":n", $n);
			$stmt->execute();
			$result = $stmt->fetchAll();
			// Store found users
			foreach ($result as $row) {
				if (in_array($row["id"], $contacts)) {
					array_unshift($users, $row["id"]);
				}
				else {
					$users[] = $row["id"];
				}
			}
			$ln = "%" . $n . "%";
			// Get partial matches
			$stmt = $conn->prepare("SELECT * FROM users WHERE firstname LIKE :n OR lastname LIKE :n");
			$stmt->bindParam(":n", $ln);
			$stmt->execute();
			$result = $stmt->fetchAll();
			// Store found users
			foreach ($result as $row) {
				if (in_array($row["id"], $contacts) && !in_array($row["id"], $users)) {
					array_unshift($users, $row["id"]);
				}
				else if (!in_array($row["id"], $users)) {
					$users[] = $row["id"];
				}
			}
		}
		else {
			// Check each query argument for matching users
			foreach ($name as $n) {
				// Get users from argument
				$stmt = $conn->prepare("SELECT * FROM users WHERE firstname = :n OR lastname = :n");
				$stmt->bindParam(":n", $n);
				$stmt->execute();
				$result = $stmt->fetchAll();
				// Store users with more than 1 matching arguments before others
				foreach ($result as $row) {
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
			// Add users with only 1 matching argument
			foreach ($tempusers as $u) {
				if (!in_array($u, $users)) {
					$users[] = $u;
				}
			}
			// Get partial matches for each query argument
			foreach ($name as $n) {
				$ln = "%" . $n . "%";
				$stmt = $conn->prepare("SELECT * FROM users WHERE firstname LIKE :n OR lastname LIKE :n");
				$stmt->bindParam(":n", $ln);
				$stmt->execute();
				$result = $stmt->fetchAll();
				// Store users with more than 1 partially matching arguments before others
				foreach ($result as $row) {
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
			// Add users with only 1 partially matching argument
			foreach ($tempusers as $u) {
				if (!in_array($u, $users)) {
					$users[] = $u;
				}
			}
		}
		// Return found users
		return $users;
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
					<a href="index.php"><img src="images/logo.png" width=32px height=32px></a>
				</div>
                <?php include_once("menu.php"); ?>
			</div>
		</div>
		
        <div id="content">
			<form method="get" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
				<input type="hidden" name="type" value="user" />
				<p id="label">Search for user: <input type="text" name="query" value=<?php echo("\"" . $query . "\""); ?> />
				<input type="submit" value="Search" /></p>
			</form>
            <div id = "results">
                <?php
					// Display search results depending on given type
					switch ($type) {
						case "user":
							// Get users from query
							$users = getUsers($conn, $id, $query);
							//Display found users
							if (count($users) == 0) {
								if (empty($errorMessage)) { $errorMessage = "<p id=\"error\">No results found.</p>"; }
							} else {
								foreach ($users as $u) {
									// Get user information from id
									$stmt = $conn->prepare("SELECT * FROM users WHERE id = :u");
									$stmt->bindParam(":u", $u);
									$stmt->execute();
									$result = $stmt->fetchAll();
									$row = $result[0];
									$name = $row["firstname"] . " " . $row["lastname"];
									echo("<a id=\"user\" href=\"user.php?id=" . $row["username"] . "\">" . $name . "</a><br>");
								}
							}
							break;
						default:
							// Set error message if type invalid
							if (empty($errorMessage)) { $errorMessage = "<p id=\"error\">No results found.</p>"; }
					}
					// Display error message if set
					if (!empty($errorMessage)) { echo($errorMessage); }
				?>
            </div>
        </div>
            
	</body>
</html>
