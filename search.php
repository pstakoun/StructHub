<?php
	// Import util functions
    require("util.php");

	checkSession();
	dbConnect();

	// Get query and type from url
	$query = "";
	$type = null;
	if (isset($_GET["query"])) { $query = htmlspecialchars($_GET["query"]); }
	if (isset($_GET["type"])) { $type = htmlspecialchars($_GET["type"]); }

    echoHeader(1);
?>
        <div id="content">
			<form method="get" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
				<input type="hidden" name="type" value="user" />
				<p id="label">Search for user: <input type="text" name="query" value=<?php echo("\"" . $query . "\""); ?> />
				<input type="submit" value="Search" /></p>
			</form>
            <div id = "results">
                <?php
					// Display search results depending on given type
					switch ($type) {
						case "user":
							// Get users from query
							$users = searchUsers($id, $query);
							//Display found users
							if (count($users) == 0) {
                                setError("No results found.");
							} else {
								foreach ($users as $u) {
                                    $result = userFromID($u);
									$name = $result["firstname"] . " " . $result["lastname"];
									echo("<a id=\"user\" href=\"user.php?id=" . $result["username"] . "\">" . $name . "</a><br>");
								}
							}
							break;
						default:
							// Set error message if type invalid
                            setError("No results found.");
					}
					// Display error message if set
					if (!empty($errorMessage)) { echo($errorMessage); }
				?>
            </div>
        </div>

	</body>
</html>
