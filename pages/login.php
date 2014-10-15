<?php
  session_start();
  
  if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    header('Location: http://www.site.com/index.php');
  }
?>

<html>
  <head>
    <title>Social Network</title>
  </head>
  <body>
    <h2>Login</h2>
  </body>
</html>
