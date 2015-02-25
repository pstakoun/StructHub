<?php
    session_start();
	// Check for session
    if (!isset($_SESSION["id"])) {
        header("Location: login.php");
        die();
    }
	$id = $_SESSION["id"];
	
	$query = null;
	if (isset($_GET["query"])) { $query = htmlspecialchars($_GET["query"]); }
	
	// Get user from url
	$activeConversation = null;
	if (isset($_GET["id"])) { $activeConversation = htmlspecialchars($_GET["id"]); }
	
	$errorMessage = "";
	// Connect to database
	try {
		$conn = new PDO("mysql:host=structhubdb.db.11405843.hostedresource.com;dbname=structhubdb", "structhubdb", "Cx!ak#Unm6Bknn54");
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch(PDOException $e) {
		$errorMessage = "<p id=\"error\">Could not connect to database.</p>";
	}
	
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
	$contacts[] = $id;
	
	// Get user from username
	$user = null;
	$stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
	$stmt->bindParam(":username", $activeConversation);
	$stmt->execute();
	$result = $stmt->fetchAll();
	if (!empty($result) && in_array($result[0]["id"], $contacts)) {
		$row = $result[0];
		$user = $row["id"];
	}
	
	// Send message if set
    if (isset($_POST["message"]) && !empty($user)) {
        $message = nl2br(htmlspecialchars($_POST["message"]));
		// Check if message valid
        if (!(empty($_POST["message"]) || ctype_space($_POST["message"]))) {
			// Send message
			$stmt = $conn->prepare("INSERT INTO messages (sender, recipient, message) VALUES (:id, :user, :message)");
			$stmt->bindParam(":id", $id);
			$stmt->bindParam(":user", $user);
			$stmt->bindParam(":message", $message);
			$stmt->execute();
		}
    }
	
	// PRG
	if ($_POST) {
		header("Location: " . $_SERVER['REQUEST_URI']);
		die();
	}
	
	// Get users
	function getContacts($conn, $id, $query)
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
		$contacts[] = $id;
		
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
            <div id="conversations">
                <?php
					// Get active conversations
					$conversations = [];
					$stmt = $conn->prepare("SELECT * FROM messages WHERE sender = :id OR recipient = :id ORDER BY datecreated DESC");
					$stmt->bindParam(":id", $id);
					$stmt->execute();
					$messages = $stmt->fetchAll();
					// Store found conversations
					foreach ($messages as $row) {
						$sender = $row["sender"];
						$recipient = $row["recipient"];
						if ($sender == $id && !in_array($recipient, $conversations)) {
							$conversations[] = $recipient;
						}
						else if ($recipient == $id && !in_array($sender, $conversations)) {
							$conversations[] = $sender;
						}
					}
				?>
				<h2>Conversations</h2>
				<form id="conversation" method="get" action=<?php echo(htmlspecialchars($_SERVER["PHP_SELF"])); ?>>
					<input type="hidden" name="id" value="new" />
					<input type="submit" value="Start New Conversation" />
				</form>
				<?php
					// Check for ongoing conversations
					if (!empty($conversations)) {
						// Display conversations
						foreach ($conversations as $contact) {
							// Get user from id
							$stmt = $conn->prepare("SELECT * FROM users WHERE id = :contact");
							$stmt->bindParam(":contact", $contact);
							$stmt->execute();
							$result = $stmt->fetchAll();
							$row = $result[0];
							// Display conversation
							echo("<div id=\"conversation\">");
							echo("<a id=\"user\" href=\"messaging.php?id=" . $row["username"] . "\">" . $row["firstname"] . " " . $row["lastname"] . "</a>");
							echo("</div>");
						}
					}
				?>
            </div>
			<div id="activeConversation">
				<?php
					// Check for active conversation
					if (!empty($activeConversation)) {
						// Check if conversation being created
						if ($activeConversation == "new") {
							echo("<div id=\"conversation\">");
							echo("<form method=\"get\" action=\"messaging.php\">");
							echo("<input type=\"hidden\" name=\"id\" value=\"new\"/>");
							echo("<input type=\"text\" name=\"query\" style=\"margin-right:8px;\"/>");
							echo("<input type=\"submit\" value=\"Search for Contact\" />");
							echo("</form>");
							if (isset($query)) {
								$users = getContacts($conn, $id, $query);
								if (count($users) == 0) {
									if (empty($errorMessage)) { $errorMessage = "<p id=\"error\">No results found.</p>"; }
								} else {
									foreach ($users as $u) {
										// Check if conversation exists
										if (!in_array($u, $conversations)) {
											// Get user information from id
											$stmt = $conn->prepare("SELECT * FROM users WHERE id = :u");
											$stmt->bindParam(":u", $u);
											$stmt->execute();
											$result = $stmt->fetchAll();
											$row = $result[0];
											$name = $row["firstname"] . " " . $row["lastname"];
											echo("<br><a id=\"user\" href=\"messaging.php?id=" . $row["username"] . "\">" . $name . "</a>");
										}
									}
								}
							}
							if (!empty($errorMessage)) { echo($errorMessage); }
							echo("</div>");
						}
						else {
							// Get user from username
							$stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
							$stmt->bindParam(":username", $activeConversation);
							$stmt->execute();
							$result = $stmt->fetchAll();
							
							// Display user if found
							if (!empty($result) && in_array($result[0]["id"], $contacts)) {
								$row = $result[0];
								$user = $row["id"];
								
								// Get messages from database
								$stmt = $conn->prepare("SELECT * FROM messages WHERE (sender = :user AND recipient = :id) OR (sender = :id AND recipient = :user) ORDER BY datecreated DESC");
								$stmt->bindParam(":user", $user);
								$stmt->bindParam(":id", $id);
								$stmt->execute();
								$messages = $stmt->fetchAll();
								
								// Display user
								echo("<h2>" . $row["firstname"] . " " . $row["lastname"] . "</h2>");
								
								// Display messages
								echo("<div id=\"messages\">");
								$stmt = $conn->prepare("SELECT * FROM users WHERE id = :sender");
								foreach ($messages as $message) {
									$stmt->bindParam(":sender", $message["sender"]);
									$stmt->execute();
									$result = $stmt->fetchAll();
									$sender = $result[0];
									$timestamp = $message["datecreated"];
									
									// Display message
									echo("<div id=\"message\">");
										echo("<a id=\"user\" href=\"user.php?id=" . $sender["username"] . "\">" . $sender["firstname"] . " " . $sender["lastname"] . "</a>");
										echo("<div id=\"timestamp\">" . $timestamp . "</div>");
										echo("<p id=\"messageText\">" . $message["message"] . "</p>");
									echo("</div>");
								}
								// Message sending form
								echo("<form id=\"sendMessage\" method=\"post\" action=\"messaging.php?id=" . $row["username"] . "\">");
									echo("<textarea id=\"messageInput\" name=\"message\" placeholder=\"Enter message...\"></textarea><br>");
									echo("<input id=\"messageButton\" type=\"submit\" value=\"Send\" />");
								echo("</form>");
							} else {
								echo("<h2>Contact not found.</h2>");
							}
						}
					}
					else {
						echo("<h3>No active conversation.</h3>");
					}
				?>
			</div>
        </div>
            
	</body>
</html>
