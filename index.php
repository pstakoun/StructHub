<?php
    session_start();
    if (!isset($_SESSION["id"])) {
        header("Location: login.php");
        die();
    }
?>

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
            <form id="updateStatus" method="post" action="updatestatus.php">
                <textarea id="statusText"></textarea><br>
                <input type="submit" name = "login" value = "Post Status Update" />
            </form>
            
            <div id="feeds">
                <div id="primaryFeed">
                    <?php
                        $primaryfeed = "news"/*check database for primary feed*/;
                        switch($primaryfeed) {
                            case "news":
                                include_once("newsfeed.php");
                                break;
                            case "messages":
                                include_once("messagefeed.php");
                                break;
                            default:
                                include_once("newsfeed.php");
                                //set primary feed to news (in database)
                        }
                    ?>
                </div>

                <div id="secondaryFeed">
                    <?php
                        $secondaryfeed = "messages"/*check database for secondary feed*/;
                        switch($secondaryfeed) {
                            case "news":
                                include_once("newsfeed.php");
                                break;
                            case "messages":
                                include_once("messagefeed.php");
                                break;
                            default:
                                include_once("messagefeed.php");
                                //set secondary feed to chat
                        }
                    ?>
                </div>
            </div>

            <div id="sidebar">
                <a id ="nav" href="#">Home</a><br>
                <a id ="nav" href="contacts.php">Contacts</a><br>
                <a id ="nav" href="messaging.php">Messaging</a><br>
                <a id ="nav" href="settings.php">Settings</a>
            </div>
        </div>
	</body>
</html>
