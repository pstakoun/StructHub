<?php
	// Import util functions
    require("util.php");

	checkSession();

	// Check for logout
    if (isset($_POST["logout"])) {
        session_unset();
        session_destroy();
        header("Location: login.php");
        die();
    }

	prg();
    
    echoHeader(1);
?>
        <div id="content">
            <div id = "settings">
                <form method="post" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
                    <p id="label"><input type="submit" name="logout" value="Logout" /></p>
                </form>
            </div>
        </div>

	</body>
</html>
