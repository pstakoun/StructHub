<?php
    session_start();
    if (!isset($_SESSION["id"])) {
        header("Location: login.php");
        die();
    }
	$id = $_SESSION["id"];
	
	$errorMessage = "";
	// Connect to database
	try {
		$conn = new PDO("mysql:host=structhubdb.db.11405843.hostedresource.com;dbname=structhubdb", "structhubdb", "Cx!ak#Unm6Bknn54");
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch(PDOException $e) {
		$errorMessage = "<p id=\"error\">Could not connect to database.</p>";
	}
	
	// Set up feeds
	$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
	$stmt->bindParam(":id", $id);
	$stmt->execute();
	$result = $stmt->fetchAll();
	$row = $result[0];
	$primaryfeed = $row["primaryfeed"];
	$secondaryfeed = $row["secondaryfeed"];
	
    if (isset($_POST["statusUpdate"])) {
        $statusUpdate = htmlspecialchars($_POST["statusUpdate"]);
        if (!(empty($statusUpdate) || ctype_space($statusUpdate))) {
			$stmt = $conn->prepare("INSERT INTO updates (status, posterid) VALUES (:statusUpdate, :id)");
			$stmt->bindParam(":id", $id);
			$stmt->bindParam(":statusUpdate", $statusUpdate);
			$stmt->execute();
			$postMessage = "<p id=\"label\">Status update successful.</p>";
        } else if (empty($errorMessage)) {
			$errorMessage = "<p id=\"error\">Please enter a valid status update.</p>";
		}
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
					<a href="#"><img src="images/logo.png" width=48px height=48px></a>
				</div>
                <div>
                    <h1>StructHub</h1>
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

            <?php include_once("sidebar.php"); ?>
        </div>
	</body>
</html>
