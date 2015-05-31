<?php
	$id;
	$conn;
	$errorMessage;

	function checkSession()
	{
		global $id;
		session_start();
		// Check for session
		if (!isset($_SESSION["id"])) {
			header("Location: login.php");
			die();
		}
		$id = $_SESSION["id"];
	}

	function checkEmptySession()
	{
		global $id;
		session_start();
		// Check for session
		if (isset($_SESSION["id"])) {
			header("Location: index.php");
			die();
		}
	}

	function setError($str)
	{
		global $errorMessage;
		if (empty($errorMessage)) {
			$errorMessage = "<p id=\"error\">" . $str . "</p>";
		}
	}

	function dbConnect()
	{
		global $conn;
		global $errorMessage;
		try {
			$conn = new PDO("mysql:host=structhubdb.db.11405843.hostedresource.com;dbname=structhubdb", "structhubdb", "Cx!ak#Unm6Bknn54");
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(PDOException $e) {
			setError("Could not connect to database.");
		}
	}

	function userFromID($id)
	{
		global $conn;
		$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
		$stmt->bindParam(":id", $id);
		$stmt->execute();
		$result = $stmt->fetch();
		return $result;
	}

	function userFromEmail($email)
	{
		global $conn;
		$stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
		$stmt->bindParam(":email", $email);
		$stmt->execute();
		$result = $stmt->fetch();
		return $result;
	}

	function userFromUsername($username)
	{
		global $conn;
		$stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
		$stmt->bindParam(":username", $username);
		$stmt->execute();
		$result = $stmt->fetch();
		return $result;
	}

	function prg()
	{
		if ($_POST) {
			header("Location: " . $_SERVER['REQUEST_URI']);
			die();
		}
	}

	function echoHeader($n)
	{
		if ($n == 1) {
			echo("
				<!DOCTYPE HTML>
				<html>
					<head>
						<meta charset=\"UTF-8\">
						<title>StructHub</title>
						<link rel=\"stylesheet\" href=\"styles/default.css\">
					</head>

					<body>
						<div id=\"titleBar\">
							<div id=\"titleBarWrap\">
								<div id=\"titleBarLogo\">
									<a href=\"index.php\"><img src=\"images/logo.png\" width=32px height=32px></a>
								</div>
								<div id=\"menu\">
									<a id=\"nav\" href=\"index.php\">Home</a>
									<a id=\"nav\" href=\"profile.php\">Profile</a>
									<a id=\"nav\" href=\"contacts.php\">Contacts</a>
									<a id=\"nav\" href=\"messaging.php\">Messaging</a>
									<a id=\"nav\" href=\"teams.php\">Teams</a>
									<a id=\"nav\" href=\"projects.php\">Projects</a>
									<a id=\"nav\" href=\"settings.php\">Settings</a>
								</div>
							</div>
						</div>
			");
		} else {
			echo("
				<!DOCTYPE HTML>
				<html>
					<head>
						<meta charset=\"UTF-8\">
						<title>StructHub</title>
						<link rel=\"stylesheet\" href=\"styles/default.css\">
					</head>

					<body>
						<div id=\"titleBar\">
							<div id=\"titleBarWrap\">
								<div>
									<h1>StructHub</h1>
								</div>
							</div>
						</div>
			");
		}
	}

	function getContacts($id)
	{
	    global $conn;
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
	    return $contacts;
	}

	function getSentRequests($id)
	{
	    global $conn;
	    $sent = [];
	    $stmt = $conn->prepare("SELECT * FROM contacts WHERE user1 = :id AND status = 1");
	    $stmt->bindParam(":id", $id);
	    $stmt->execute();
	    $result = $stmt->fetchAll();
	    foreach ($result as $row) {
	        $sent[] = $row["user2"];
	    }
	    return $sent;
	}

	function getReceivedRequests($id)
	{
	    global $conn;
	    $received = [];
	    $stmt = $conn->prepare("SELECT * FROM contacts WHERE user2 = :id AND status = 1");
	    $stmt->bindParam(":id", $id);
	    $stmt->execute();
	    $result = $stmt->fetchAll();
	    foreach ($result as $row) {
	        $received[] = $row["user1"];
	    }
	    return $received;
	}

	function searchContacts($id, $query)
	{
	    global $conn;
		// Find contacts
		$contacts = getContacts($id);

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
							$users[] = $row["id"];
						}
					}
					else if (in_array($row["id"], $contacts)) {
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
							$users[] = $row["id"];
						}
					}
					else if (in_array($row["id"], $contacts)) {
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

	function searchUsers($id, $query)
	{
		global $conn;
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

	function echoUser($result)
	{
	    $name = $result["firstname"] . " " . $result["lastname"];
	    echo("<a id=\"user\" href=\"user.php?id=" . $result["username"] . "\">" . $name . "</a><br>");
	}
?>
