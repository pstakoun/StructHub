<?php
	// Import util functions
    require("util.php");

	checkSession();
	dbConnect();

	// Get username from url
	$username = null;
	if (isset($_GET["id"])) {
		$username = htmlspecialchars($_GET["id"]);
	}

	// Get user from given username
	if (!empty($username)) {
		$result = userFromUsername($username);
		if (!empty($result)) {
			$userid = $result["id"];

			// Remove contact if needed
			if (isset($_POST["removeContact"])) {
				$stmt = $conn->prepare("UPDATE contacts SET status = 0 WHERE (user1 = :id AND user2 = :userid) OR (user1 = :userid AND user2 = :id)");
				$stmt->bindParam(":id", $id);
				$stmt->bindParam(":userid", $userid);
				$stmt->execute();
			}
			// Accept contact request if needed
			if (isset($_POST["acceptContactRequest"])) {
				$stmt = $conn->prepare("UPDATE contacts SET status = 2 WHERE user1 = :userid AND user2 = :id");
				$stmt->bindParam(":id", $id);
				$stmt->bindParam(":userid", $userid);
				$stmt->execute();
			}
			// Decline contact request if needed
			if (isset($_POST["declineContactRequest"])) {
				$stmt = $conn->prepare("UPDATE contacts SET status = 0 WHERE user1 = :userid AND user2 = :id");
				$stmt->bindParam(":id", $id);
				$stmt->bindParam(":userid", $userid);
				$stmt->execute();
			}
			// Revoke contact request if needed
			if (isset($_POST["revokeContactRequest"])) {
				$stmt = $conn->prepare("UPDATE contacts SET status = 0 WHERE user1 = :id AND user2 = :userid");
				$stmt->bindParam(":id", $id);
				$stmt->bindParam(":userid", $userid);
				$stmt->execute();
			}
			// Add contact if needed
			if (isset($_POST["addContact"])) {
				$stmt = $conn->prepare("SELECT * FROM contacts WHERE user1 = :id AND user2 = :userid");
				$stmt->bindParam(":id", $id);
				$stmt->bindParam(":userid", $userid);
				$stmt->execute();
				$result = $stmt->fetchAll();
				// Create contact request
				if (empty($result)) {
					$stmt = $conn->prepare("INSERT INTO contacts(user1, user2, status) VALUES (:id, :userid, 1)");
					$stmt->bindParam(":id", $id);
					$stmt->bindParam(":userid", $userid);
					$stmt->execute();
				}
				else {
					$stmt = $conn->prepare("UPDATE contacts SET status = 1 WHERE user1 = :id AND user2 = :userid");
					$stmt->bindParam(":id", $id);
					$stmt->bindParam(":userid", $userid);
					$stmt->execute();
				}
			}
		}
	}

	prg();
    
    echoHeader(1);
?>
        <div id="content">
            <div id="profile">
                <?php
					$result = userFromUsername($username);
					if (empty($username) || empty($result)) {
						echo("<p id=\"error\">User not found.</p>");
					}
					else {
						$userid = $result["id"];
						$firstname = $result["firstname"];
						$lastname = $result["lastname"];
						$bio = $result["bio"];
						echo("<h2>" . $firstname . " " . $lastname . "</h2>");

						$contacts = getContacts($id);
						$sent = getSentRequests($id);
						$received = getReceivedRequests($id);

						if (in_array($userid, $contacts)) {
						?>
							<form style="float:left" method="post" action=<?php echo("user.php?id=" . $username); ?>>
								<p id="label"><input type="submit" name="removeContact" value="Remove Contact" /></p>
							</form>
							<form style="float:right" method="post" action=<?php echo("messaging.php?id=" . $username); ?>>
								<p id="label"><input type="submit" name="sendMessage" value="Send Message" /></p>
							</form>
						<?php
						}
						else if (in_array($userid, $sent)) {
						?>
							<form method="post" action=<?php echo("user.php?id=" . $username); ?>>
								<p id="label"><input type="submit" name="revokeContactRequest" value="Revoke Contact Request" /></p>
							</form>
						<?php
						}
						else if (in_array($userid, $received)) {
						?>
							<form method="post" action=<?php echo("user.php?id=" . $username); ?>>
								<p id="label"><input type="submit" name="acceptContactRequest" value="Accept Contact Request" /></p>
							</form>
							<form method="post" action=<?php echo("user.php?id=" . $username); ?>>
								<p id="label"><input type="submit" name="declineContactRequest" value="Decline Contact Request" /></p>
							</form>
						<?php
						}
						else if ($userid != $id) {
						?>
							<form method="post" action=<?php echo("user.php?id=" . $username); ?>>
								<p id="label"><input type="submit" name="addContact" value="Add Contact" /></p>
							</form>
						<?php
						}

						if (!empty($bio) && empty($_POST["editBio"])) {
						?>
							<div id="bio">
								<?php echo($bio); ?>
							</div>
						<?php
						}
						else if (!empty($_POST["editBio"])) {
						?>
							<!-- Edit bio. -->
						<?php
						}
					}
				?>
            </div>
        </div>

	</body>
</html>
