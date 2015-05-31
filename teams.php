<?php
	// Import util functions
    require("util.php");

	checkSession();
	dbConnect();

	// Initialize creation validity
	$postValid = True;
	$name = "";
	$description = "";
	if (empty($_POST)) {
		$postValid = False;
	} else {
		// Get submitted information
		$name = htmlspecialchars($_POST["name"]);
		$description = htmlspecialchars($_POST["description"]);

		// Validate name
		if (empty($name) || ctype_space($name)) {
			$postValid = False;
			$errorMessage = "<p id=\"error\">Name invalid.</p>";
		}
	}

	if (isset($_SESSION["errorMessage"])) {
		$errorMessage = $_SESSION["errorMessage"];
		unset($_SESSION["errorMessage"]);
	}

	$teams = [];
	$invitations = [];

	// Get teams
	$stmt = $conn->prepare("SELECT * FROM teammembers WHERE member = :id AND status = 2");
	$stmt->bindParam(":id", $id);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row) {
		$teams[] = $row["team"];
	}

	// Get received invitations
	$stmt = $conn->prepare("SELECT * FROM teammembers WHERE member = :id AND status = 1");
	$stmt->bindParam(":id", $id);
	$stmt->execute();
	$result = $stmt->fetchAll();
	foreach ($result as $row) {
		$invitations[] = $row["team"];
	}

    echoHeader(1);
?>
        <div id="content">
            <div id="teams">
				<?php
					if (isset($_SESSION["successMessage"])) {
						echo($_SESSION["successMessage"]);
						unset($_SESSION["successMessage"]);
					} else {
						if (!empty($_GET["id"])) {
							$team = $_GET["id"];
							// Display creation form if submitted details are invalid
							if ($team == "new") {
								if (!$postValid) { ?>
									<div id="form">
										<form method="post" action=<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>>
											<table style="margin: 0 auto;">
												<?php if (!empty($errorMessage)) { echo("<tr><td colspan=\"2\">" . $errorMessage . "</td></tr>"); } ?>
												<tr>
													<td id="label" align="right">Team Name: </td>
													<td id="label"><input type="text" name="name" value="<?php if (!empty($name)) { echo($name); } ?>" /></td>
												</tr>
												<tr>
													<td id="label" align="right">Description: </td>
													<td id="label"><input type="text" name="description" value="<?php if (!empty($description)) { echo($description); } ?>" /></td>
												</tr>
												<tr>
													<td id="label" align="right"><input type="submit" name="create" value="Create Team" /></td>
												</tr>
											</table>
										</form>
									</div>
						<?php	}
								else {
									// Ensure team id is unique
									$teamid = uniqid();
									$stmt = $conn->prepare("SELECT * FROM teams WHERE id = :teamid");
									$stmt->bindParam(":teamid", $teamid);
									$stmt->execute();
									$result = $stmt->fetchAll();
									while (!empty($result)) {
										$id = uniqid();
										$stmt->execute();
										$result = $stmt->fetchAll();
									}

									// Create team
									$stmt = $conn->prepare("INSERT INTO teams (id, name, description, owner) VALUES (:teamid, :name, :description, :id)");
									$stmt->bindParam(":teamid", $teamid);
									$stmt->bindParam(":name", $name);
									$stmt->bindParam(":description", $description);
									$stmt->bindParam(":id", $id);
									$stmt->execute();

									// Add user to team
									$s = 2;
									$stmt = $conn->prepare("INSERT INTO teammembers (member, team, status) VALUES (:id, :teamid, :status)");
									$stmt->bindParam(":id", $id);
									$stmt->bindParam(":teamid", $teamid);
									$stmt->bindParam(":status", $s);
									$stmt->execute();

									$_SESSION["successMessage"] = "<p id=\"label\">Team successfully created.</p>";

									header("Location: " . $_SERVER['REQUEST_URI']);
								}
							}
							// Display team information
							else {
								// Get team information from id
								$stmt = $conn->prepare("SELECT * FROM teams WHERE id = :team");
								$stmt->bindParam(":team", $team);
								$stmt->execute();
								$result = $stmt->fetch();
								if (!empty($result)) {
									echo("<h2>" . $result["name"] . "</h2>");
									if (!empty($_GET["action"]) && $result["owner"] == $id && ($_GET["action"] == "add" || $_GET["action"] == "remove")) {
										if ($_GET["action"] == "add") {
											// Find contacts
											$contacts = getContacts($id);

											// Find team members
											$members = [];
											$stmt = $conn->prepare("SELECT * FROM teammembers WHERE team = :team");
											$stmt->bindParam(":team", $team);
											$stmt->execute();
											$result = $stmt->fetchAll();
											foreach ($result as $row) {
												$members[] = $row["member"];
											}

											// Add user if requested
											if (!empty($_GET["user"])) {
												$un = htmlspecialchars($_GET["user"]);
												$result = userFromUsername($un);
												if (!empty($result)) {
													$u = $result["id"];
													if (in_array($u, $contacts) && !in_array($u, $members)) {
														// Add user to team
														$s = 2;
														$stmt = $conn->prepare("INSERT INTO teammembers (member, team, status) VALUES (:u, :team, :status)");
														$stmt->bindParam(":u", $u);
														$stmt->bindParam(":team", $team);
														$stmt->bindParam(":status", $s);
														$stmt->execute();

														// Update team members
														$members = [];
														$stmt = $conn->prepare("SELECT * FROM teammembers WHERE team = :team");
														$stmt->bindParam(":team", $team);
														$stmt->execute();
														$result = $stmt->fetchAll();
														foreach ($result as $row) {
															$members[] = $row["member"];
														}
													}
												}
											}

											foreach ($contacts as $user) {
												if (!in_array($user, $members)) {
													$result = userFromID($user);
													$name = $result["firstname"] . " " . $result["lastname"];
													echo("<div id=addMember>");
													echo("<a id=\"user\" href=\"user.php?id=" . $result["username"] . "\">" . $name . "</a>");
													echo("<form method=\"get\" action=teams.php>");
														echo("<input type=\"hidden\" name=\"id\" value=\"" . $team . "\" />");
														echo("<input type=\"hidden\" name=\"action\" value=\"add\" />");
														echo("<input type=\"hidden\" name=\"user\" value=\"" . $result["username"] . "\" />");
														echo("<input type=\"submit\" value=\"Add Member\" />");
													echo("</form>");
													echo("</div>");
												}
											}
										}
										else if ($_GET["action"] == "remove") {
											// Find contacts
											$contacts = getContacts($id);

											// Find team members
											$members = [];
											$stmt = $conn->prepare("SELECT * FROM teammembers WHERE team = :team");
											$stmt->bindParam(":team", $team);
											$stmt->execute();
											$result = $stmt->fetchAll();
											foreach ($result as $row) {
												$members[] = $row["member"];
											}

											// Remove user if requested
											if (!empty($_GET["user"])) {
												$un = htmlspecialchars($_GET["user"]);
												$result = userFromUsername($un);
												if (!empty($result)) {
													$u = $result["id"];
													if (in_array($u, $members)) {
														// Remove user from team
														$stmt = $conn->prepare("DELETE FROM teammembers WHERE team = :team AND member = :u");
														$stmt->bindParam(":team", $team);
														$stmt->bindParam(":u", $u);
														$stmt->execute();

														// Update team members
														$members = [];
														$stmt = $conn->prepare("SELECT * FROM teammembers WHERE team = :team");
														$stmt->bindParam(":team", $team);
														$stmt->execute();
														$result = $stmt->fetchAll();
														foreach ($result as $row) {
															$members[] = $row["member"];
														}
													}
												}
											}

											foreach ($members as $user) {
												$result = userFromID($user);
												$name = $result["firstname"] . " " . $result["lastname"];
												echo("<div id=removeMember>");
												echo("<a id=\"user\" href=\"user.php?id=" . $result["username"] . "\">" . $name . "</a>");
												echo("<form method=\"get\" action=teams.php>");
													echo("<input type=\"hidden\" name=\"id\" value=\"" . $team . "\" />");
													echo("<input type=\"hidden\" name=\"action\" value=\"remove\" />");
													echo("<input type=\"hidden\" name=\"user\" value=\"" . $result["username"] . "\" />");
													echo("<input type=\"submit\" value=\"Remove Member\" />");
												echo("</form>");
												echo("</div>");
											}
										}
									}
									else {
										if ($result["owner"] == $id) {
											// Add contact to team
											echo("<form style=\"float:left\" method=\"get\" action=teams.php>");
												echo("<input type=\"hidden\" name=\"id\" value=\"" . $team . "\" />");
												echo("<input type=\"hidden\" name=\"action\" value=\"add\" />");
												echo("<input type=\"submit\" value=\"Add Member\" />");
											echo("</form>");
											// Remove contact from team
											echo("<form style=\"float:right\" method=\"get\" action=teams.php>");
												echo("<input type=\"hidden\" name=\"id\" value=\"" . $team . "\" />");
												echo("<input type=\"hidden\" name=\"action\" value=\"remove\" />");
												echo("<input type=\"submit\" value=\"Remove Member\" />");
											echo("</form>");
										}
										echo("<p id=\"label\">" . $result["description"] . "</p>");
									}
								}
								else {
									echo("<p id=\"error\">Team not found.</p>");
								}
							}
						}
						else {
							// Create new team
							echo("<form method=\"get\" action=teams.php>");
								echo("<input type=\"hidden\" name=\"id\" value=\"new\" />");
								echo("<input type=\"submit\" value=\"Create Team\" />");
							echo("</form>");

							// Display received invitations
							if (!empty($invitations)) {
								echo("<h3>Invitations</h3>");
							}
							foreach ($invitations as $t) {
								// Get team information from id
								$stmt = $conn->prepare("SELECT * FROM teams WHERE id = :t");
								$stmt->bindParam(":t", $t);
								$stmt->execute();
								$result = $stmt->fetch();
								// Display team
								echo("<a id=\"team\" href=\"teams.php?id=" . $result["id"] . "\">" . $result["name"] . "</a><br>");
							}

							// Display teams
							if (!empty($teams)) {
								echo("<h3>Your Teams</h3>");
							}
							foreach ($teams as $t) {
								// Get team information from id
								$stmt = $conn->prepare("SELECT * FROM teams WHERE id = :t");
								$stmt->bindParam(":t", $t);
								$stmt->execute();
								$result = $stmt->fetch();
								// Display team
								echo("<a id=\"team\" href=\"teams.php?id=" . $result["id"] . "\">" . $result["name"] . "</a><br>");
							}
						}
					} ?>
            </div>
        </div>

	</body>
</html>
