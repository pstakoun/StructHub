<?php
	// Import util functions
    require("util.php");

	checkSession();
	dbConnect();

	// Set up feeds
	$result = userFromID($id);
	$primaryfeed = $result["primaryfeed"];
	$secondaryfeed = $result["secondaryfeed"];

	// Post status update if set
    if (isset($_POST["statusUpdate"])) {
        $statusUpdate = nl2br(htmlspecialchars($_POST["statusUpdate"]));
		// Check if status update valid
        if (!(empty($_POST["statusUpdate"]) || ctype_space($_POST["statusUpdate"]))) {
			// Post status update
			$stmt = $conn->prepare("INSERT INTO updates (status, posterid) VALUES (:statusUpdate, :id)");
			$stmt->bindParam(":id", $id);
			$stmt->bindParam(":statusUpdate", $statusUpdate);
			$stmt->execute();
			$postMessage = "<p id=\"label\">Status update successful.</p>";
        } else {
			setError("Please enter a valid status update.");
		}
    }

	prg();

	echoHeader(1);
?>
        <div id="content">
            <form id="updateStatus" method="post" action="#">
				<?php
					// Display error message if exists
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

			<div id="primaryFeed">
				<?php
					// Display primary feed
					switch($primaryfeed) {
						case "news":
							include_once("newsfeed.php");
							break;
						case "messages":
							include_once("messagefeed.php");
							break;
						default:
							// TODO set primary feed to news
					}
				?>
			</div>

			<div id="secondaryFeed">
				<?php
					// Display secondary feed
					switch($secondaryfeed) {
						case "news":
							include_once("newsfeed.php");
							break;
						case "messages":
							include_once("messagefeed.php");
							break;
						default:
							// TODO set secondary feed to messages
					}
				?>
			</div>

        </div>
	</body>
</html>
