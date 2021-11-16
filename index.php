<?php

session_start();
$_SESSION = array();
session_destroy();
session_start();
$_SESSION['dummy']=1;
 ?>


<html>
  <head>
    <title>Pagina di Redirect</title>
   <meta http-equiv="refresh" content="0;URL=http://prenotazioni.it/inizio.php"> 
   
  </head>
  <body>
    <p>
    <a href="http://prenotazioni.it/inizio.php">clicca qui</a>.
    </p>
  </body>
</html>
