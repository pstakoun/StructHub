<?php
    session_start();
	// Check for session
    if (!isset($_SESSION["id"])) {
        header("Location: login.php");
        die();
    }
	$id = $_SESSION["id"];
	
	ob_start();
	
	$errorMessage = "";
	
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
	// Connect to database
	try {
		$conn = new PDO("mysql:host=structhubdb.db.11405843.hostedresource.com;dbname=structhubdb", "structhubdb", "Cx!ak#Unm6Bknn54");
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch(PDOException $e) {
		$errorMessage = "<p id=\"error\">Could not connect to database.</p>";
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
								$result = $stmt->fetchAll();
								if (!empty($result)) {
									$row = $result[0];
									echo("<h2>" . $row["name"] . "</h2>");
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
								$result = $stmt->fetchAll();
								$row = $result[0];
								// Display team
								echo("<a id=\"team\" href=\"teams.php?id=" . $row["id"] . "\">" . $row["name"] . "</a><br>");
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
								$result = $stmt->fetchAll();
								$row = $result[0];
								// Display team
								echo("<a id=\"team\" href=\"teams.php?id=" . $row["id"] . "\">" . $row["name"] . "</a><br>");
							}
						}
					} ?>
            </div>
        </div>
            
	</body>
</html>
