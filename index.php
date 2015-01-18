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
					<a href="index.php"><img src="images/logo.png"></a>
				</div>
                <div>
                    <h1>Social Network</h1>
                </div>
			</div>
		</div>
		
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
						//set primary feed to news
				}
			?>
		</div>
		
		<div id="secondaryFeed">
			<?php
				$secondaryfeed = "messages"/*check database for secondary feed*/;
				switch($primaryfeed) {
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
		
		<div id="sidebar">
			<a href="#">Home</a>
			<a href="contacts.php">Contacts</a>
			<a href="messaging.php">Messaging</a>
			<a href="settings.php">Settings</a>
		</div>
	</body>
</html>
