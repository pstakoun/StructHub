<html>
	<head>
		<meta charset="UTF-8">
		<title>Social Network</title>
		<link rel="stylesheet" href="style.css">
        <?php
            $postValid = True;
            $errorMessage = "";
            if (empty($_POST)) {
                $postValid = False;
            } else {
                $firstname = htmlspecialchars($_POST["firstname"]);
                $lastname = htmlspecialchars($_POST["lastname"]);
                $email = htmlspecialchars($_POST["email"]);
                $password = htmlspecialchars($_POST["password"]);
                $confirmpassword = htmlspecialchars($_POST["confirmpassword"]);
                // Validate name
                if (!(ctype_alpha($firstname) && ctype_alpha($lastname))) {
                    $postValid = False;
                    $errorMessage = "<p id=\"error\">Name invalid.<p>";
                // Validate email
                } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $postValid = False;
                    $errorMessage = "<p id=\"error\">Email invalid.<p>";
                } else if (strlen($password) < 6) {
                    $postValid = False;
                    $errorMessage = "<p id=\"error\">Password too short.<p>";
                } else if ($password != $confirmpassword) {
                    $postValid = False;
                    $errorMessage = "<p id=\"error\">Passwords do not match.<p>";
                }
            }
        ?>
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
		
        <?php
            if (!$postValid) {
        ?>
		<div id="register">
            <?php echo($errorMessage); ?>
			<form method="post" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
                <p id="label">First Name: <input type="text" name="firstname" value="<?php if (!empty($firstname)) { echo($firstname); } ?>" /></p>
                <p id="label">Last Name: <input type="text" name="lastname" value="<?php if (!empty($lastname)) { echo($lastname); } ?>" /></p>
                <p id="label">Email: <input type="email" name="email" value="<?php if (!empty($email)) { echo($email); } ?>" /></p>
                <p id="label">Password: <input type="password" name="password" /></p>
                <p id="label">Confirm Password: <input type="password" name="confirmpassword" /></p>
                <p id="label"><input type="submit" name="register" value="Register" /></p>
            </form>
		</div>
        <?php } else { ?>
            <p id="label">Registration successful! A confirmation email has been sent to <?php echo $email; ?>.</p>
        <?php } ?>
	</body>
</html>
