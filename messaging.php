<?php
	// Import util functions
    require("util.php");

    checkSession();
    dbConnect();

	$query;
	if (isset($_GET["query"])) { $query = htmlspecialchars($_GET["query"]); }

	// Get user from url
	$activeConversation = null;
	if (isset($_GET["id"])) { $activeConversation = htmlspecialchars($_GET["id"]); }

	// Find contacts
	$contacts = getContacts($id);

	// Get user from username
	$user;
	$result = userFromUsername($activeConversation);
	if (!empty($result) && in_array($result["id"], $contacts)) {
		$user = $result["id"];
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

	prg();

    echoHeader(1);
?>
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
								$users = searchContacts($id, $query);
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
							$result = userFromUsername($activeConversation);

							// Display user if found
							if (!empty($result) && in_array($result["id"], $contacts)) {
								$user = $result["id"];

								// Get messages from database
								$stmt = $conn->prepare("SELECT * FROM messages WHERE (sender = :user AND recipient = :id) OR (sender = :id AND recipient = :user) ORDER BY datecreated DESC");
								$stmt->bindParam(":user", $user);
								$stmt->bindParam(":id", $id);
								$stmt->execute();
								$messages = $stmt->fetchAll();

								// Display user
								echo("<h2>" . $result["firstname"] . " " . $result["lastname"] . "</h2>");

								// Display messages
								echo("<div id=\"messages\">");
								$stmt = $conn->prepare("SELECT * FROM users WHERE id = :sender");
								foreach ($messages as $message) {
									$stmt->bindParam(":sender", $message["sender"]);
									$stmt->execute();
									$result = $stmt->fetch();
									$timestamp = $message["datecreated"];

									// Display message
									echo("<div id=\"message\">");
										echo("<a id=\"user\" href=\"user.php?id=" . $result["username"] . "\">" . $result["firstname"] . " " . $result["lastname"] . "</a>");
										echo("<div id=\"timestamp\">" . $timestamp . "</div>");
										echo("<p id=\"messageText\">" . $message["message"] . "</p>");
									echo("</div>");
								}
								// Message sending form
								echo("<form id=\"sendMessage\" method=\"post\" action=\"messaging.php?id=" . $result["username"] . "\">");
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
