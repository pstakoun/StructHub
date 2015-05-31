<?php
	// Import util functions
    require("util.php");

    checkEmptySession();

    // Import password library for backwards compatibility
	require("lib/password.php");

	if (!empty($_POST)) {
		// Get submitted email and password
        $email = htmlspecialchars($_POST["email"]);
        $password = htmlspecialchars($_POST["password"]);

		dbConnect();

		$result = userFromEmail($email);

		// Display error if user not found
		if (empty($result)) {
			setError("Email or password invalid.");
		} else {
			// Verify password
			if (password_verify($password, $result["password"])) {
				if ($result["confirmed"] == 0) {
					$_SESSION["errorMessage"] = "<p id=\"error\">Confirm your account before logging in.</p>";
					header("Location: confirm.php");
					die();
				} else {
					// Create session
					$_SESSION["id"] = $result["id"];
					header("Location: index.php");
					die();
				}
			// Display error if password incorrect
			} else {
				setError("Email or password invalid.");
			}
        }
    }

	echoHeader(0);
?>
		<div id="form">
            <form method="post" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
				<table style="margin: 0 auto;">
					<?php if (!empty($errorMessage)) { echo("<tr><td colspan=\"2\">" . $errorMessage . "</td></tr>"); } ?>
					<tr>
						<td id="label" align="right">Email: </td>
						<td id="label"><input type="email" name="email" /></td>
					</tr>
					<tr>
						<td id="label" align="right">Password: </td>
						<td id="label"><input type="password" name="password" /></td>
					</tr>
					<tr>
						<td id="label" align="right"><input type="submit" name="login" value="Login" /></td>
						<td id="link"><a href="recover.php">Forgot your password?</a></td>
					</tr>
					<tr>
						<td id="link" colspan="2"><a href="register.php">Don't have an account? Register now!</a></td>
					</tr>
				</table>

            </form>
		</div>
	</body>
</html>
