<?php
	// Import util functions
    require("util.php");

    checkSession();
	dbConnect();

    echoHeader(1);
?>
        <div id="content">
            <div id="profile">
                <?php
					$result = userFromID($id);

					$firstname = $result["firstname"];
					$lastname = $result["lastname"];
					$email = $result["email"];

					// Display user information
					echo("<h2>" . $firstname . " " . $lastname . "</h2>");
					echo("<p id=\"label\">Email: " . $email . "</p>");

					if (!empty($errorMessage)) {
						echo($errorMessage);
					}
				?>
            </div>
        </div>

	</body>
</html>
