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
			</div>
		</div>
		
		<div id="primaryFeed">
		<div id="primaryFeed">
			<?php
				$primaryfeed = "news"/*check database for primary feed*/;
				switch($primaryfeed) {
					case "news":
						include_once("newsfeed.php");
						break;
					case "chat":
						include_once("chatfeed.php");
						break;
					default:
						include_once("newsfeed.php");
						//set primary feed to news
				}
			?>
		</div>
		
		<div id="secondaryFeed">
			<?php
				$secondaryfeed = "chat"/*check database for secondary feed*/;
				switch($primaryfeed) {
					case "news":
						include_once("newsfeed.php");
						break;
					case "chat":
						include_once("chatfeed.php");
						break;
					default:
						include_once("chatfeed.php");
						//set secondary feed to chat
				}
			?>
		</div>
		
		<div id="sidebar">
			<a href="#">Home</a>
			<a href="settings.php">Settings</a>
		</div>
	</body>
</html>
