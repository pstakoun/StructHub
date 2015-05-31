<?php
	// Import util functions
    require("util.php");

    checkSession();
	dbConnect();

    $contacts = getContacts($id);
    $sent = getSentRequests($id);
    $received = getReceivedRequests($id);

	echoHeader(1);
?>
        <div id="content">
            <div id = "contacts">
				<form method="get" action="search.php">
					<input type="hidden" name="type" value="user" />
					<p id="label">Search for user: <input type="text" name="query" />
					<input type="submit" value="Search" /></p>
				</form>
				<?php
                    if (!empty($errorMessage)) { echo($errorMessage); }
					// Display received requests if not empty
					if (!empty($received)) {
						echo("<h3>Received Requests</h3>");
					}
					foreach ($received as $contact) {
                        $result = userFromID($contact);
						echoUser($result);
					}
					// Display sent requests if not empty
					if (!empty($sent)) {
						echo("<h3>Sent Requests</h3>");
					}
					foreach ($sent as $contact) {
                        $result = userFromID($contact);
						echoUser($result);
					}
                    echo("<h3>Contacts</h3>");
                    if (empty($contacts)) {
                        echo("<h4>You have no contacts.</h4>");
                    }
					// Display contacts
					foreach ($contacts as $contact) {
						$result = userFromID($contact);
						echoUser($result);
					}
				?>
            </div>
        </div>

	</body>
</html>
