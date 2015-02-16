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
	try {
		$conn = new PDO("mysql:host=localhost;dbname=socialnetwork", "pstakoun", "yJcRNzpSaEXatKqc");
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
		
		if (count($name) == 1) {
			$n = $name[0];
			$stmt = $conn->prepare("SELECT * FROM users WHERE firstname LIKE :n OR lastname LIKE :n");
			$stmt->bindParam(":n", $n);
			$stmt->execute();
			$result = $stmt->fetchAll();
			foreach ($result as $row) {
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
				$stmt = $conn->prepare("SELECT * FROM users WHERE firstname LIKE :n OR lastname LIKE :n");
				$stmt->bindParam(":n", $n);
				$stmt->execute();
				$result = $stmt->fetchAll();
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
			foreach ($tempusers as $u) {
				if (!in_array($u, $users)) {
					$users[] = $u;
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
			<form method="post" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
				<p id="label">Search for user: <input type="text" name="query" value=<?php echo("\"" . $query . "\""); ?> />
				<input type="hidden" name="type" value="user" />
				<input type="submit" name="search" value="Search" /></p>
			</form>
            <div id = "results">
                <?php
					// Display search results
					switch ($type) {
						case "user":
							// Get results
							$users = getUsers($conn, $id, $query);
							if (count($users) == 0) {
								if (empty($errorMessage)) { $errorMessage = "<p id=\"error\">No results found.</p>"; }
							}
							foreach ($users as $u) {
								$stmt = $conn->prepare("SELECT * FROM users WHERE id = :u");
								$stmt->bindParam(":u", $u);
								$stmt->execute();
								$result = $stmt->fetchAll();
								$row = $result[0];
								$name = $row["firstname"] . " " . $row["lastname"];
								echo("<a id=\"user\" href=\"user.php?id=" . $row["username"] . "\">" . $name . "</a><br>");
							}
							break;
						default:
							if (empty($errorMessage)) { $errorMessage = "<p id=\"error\">No results found.</p>"; }
					}
					if (!empty($errorMessage)) { echo($errorMessage); }
				?>
            </div>

            <?php include_once("sidebar.php"); ?>
        </div>
            
	</body>
</html>
