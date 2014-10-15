<?php
  session_start();
  
  if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    $username = $_SESSION['username'];
    //$firstname = getfromdatabase
  } else {
    header('Location: http://www.site.com/login.php');
  }
?>

<html>
  <head>
    <title>Social Network</title>
  </head>
  <body>
    <?php echo '<p>Welcome, $firstname!</p>'; ?>
  </body>
</html>
