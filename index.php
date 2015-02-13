<?php
    session_start();
    if (!isset($_SESSION["id"])) {
        header("Location: login.php");
        die();
    }
	$id = $_SESSION["id"];
	
	// Connect to database
	$connection = new mysqli("localhost", "pstakoun", "yJcRNzpSaEXatKqc", "socialnetwork");
	if ($connection->connect_error) {
		$errorMessage = "<p id=\"error\">Could not connect to database.</p>";
	}
	
	// Set up feeds
	//$sql = "SELECT * FROM users WHERE id = \"" . $id . "\"";
	$sql = "SELECT * FROM users WHERE id = ?";
	//$result = $connection->query($sql);
	$stmt = $connection->prepare($sql);
	$stmt->bind_param("s", $id);
	$stmt->execute();
	$result = $stmt->get_result();
	//$row = $result->fetch_assoc();
	$row = $result->fetch_array();
	$primaryfeed = $row["primaryfeed"];
	$secondaryfeed = $row["secondaryfeed"];
	
    if (isset($_POST["statusUpdate"])) {
        $statusUpdate = htmlspecialchars($_POST["statusUpdate"]);
        if (!(empty($statusUpdate) || ctype_space($statusUpdate))) {
            // Create query
			//$sql = "INSERT INTO updates (status, posterid) VALUES (\"" . $statusUpdate . "\", \"" . $id . "\")";
			$sql = "INSERT INTO updates (status, posterid) VALUES (?, ?)";
			if (!$stmt = $connection->prepare($sql);) {
				if (empty($errorMessage)) {
					$errorMessage = "<p id=\"error\">Database error.</p>";
				}
			} else {
				$postMessage = "<p id=\"label\">Status update successful.</p>";
			}
			$stmt->bind_param("ss", $statusUpdate, $id);
			$stmt->execute();
        } else if (empty($errorMessage)) {
			$errorMessage = "<p id=\"error\">Please enter a valid status update.</p>";
		}
    }
?>

<!DOCTYPE HTML>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Social Network</title>
		<link rel="stylesheet" href="style.css">
	</head>
	
	<body>
		<div id="titleBar">
			<div id="titleBarWrap">
				<div id="titleBarLogo">
					<a href="#"><img src="images/logo.png" width=48px height=48px></a>
				</div>
                <div>
                    <h1>Social Network</h1>
                </div>
			</div>
		</div>
		
        <div id="content">
            <form id="updateStatus" method="post" action="#">
				<?php
					if (empty($errorMessage)) {
						if (!empty($postMessage)) {
							echo($postMessage);
						}
					} else {
						echo($errorMessage);
					}
				?>
                <textarea id="statusText" name="statusUpdate" placeholder="Enter status update..."></textarea><br>
                <input type="submit" name="postStatusUpdate" value="Post" />
            </form>
            
            <div id="feeds">
                <div id="primaryFeed">
                    <?php
                        switch($primaryfeed) {
                            case "news":
                                include_once("newsfeed.php");
                                break;
                            case "messages":
                                include_once("messagefeed.php");
                                break;
                            default:
                                echo($errorMessage);
                        }
                    ?>
                </div>

                <div id="secondaryFeed">
                    <?php
                        switch($secondaryfeed) {
                            case "news":
                                include_once("newsfeed.php");
                                break;
                            case "messages":
                                include_once("messagefeed.php");
                                break;
                            default:
                                echo($errorMessage);
                        }
                    ?>
                </div>
            </div>

            <div id="sidebar">
                <a id ="nav" href="#">Home</a><br>
				<a id ="nav" href="profile.php">Profile</a><br>
                <a id ="nav" href="contacts.php">Contacts</a><br>
                <a id ="nav" href="messaging.php">Messaging</a><br>
                <a id ="nav" href="settings.php">Settings</a>
            </div>
        </div>
	</body>
</html>
