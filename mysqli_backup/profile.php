<?php
    session_start();
    if (!isset($_SESSION["id"])) {
        header("Location: login.php");
        die();
    }
	$id = $_SESSION["id"];
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
					<a href="index.php"><img src="images/logo.png" width=48px height=48px></a>
				</div>
                <div>
                    <h1>Social Network</h1>
                </div>
			</div>
		</div>
		
        <div id="content">
            <div id = "profile">
                <?php
					// Connect to database
					$connection = new mysqli("localhost", "pstakoun", "yJcRNzpSaEXatKqc", "socialnetwork");
					if ($connection->connect_error) {
						$errorMessage = "<p id=\"error\">Could not connect to database.</p>";
					}
					
					// Get user information
					$sql = "SELECT * FROM users WHERE id = ?";
					$stmt = $connection->prepare($sql);
					$stmt->bind_param("s", $id);
					$stmt->execute();
					$result = $stmt->get_result();
					$row = $result->fetch_array();
					$firstname = $row["firstname"];
					$lastname = $row["lastname"];
					$email = $row["email"];
					
					echo("<h2>" . $firstname . " " . $lastname . "</h2>");
					echo("<p id=\"label\">Email: " . $email . "</p>");
				?>
            </div>
            
            <?php include_once("sidebar.php"); ?>
        </div>
        
	</body>
</html>