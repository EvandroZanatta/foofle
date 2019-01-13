<?php 

  ob_start();

  // try to connect to mysql database
  try {

    $con = new PDO("mysql:dbname=foofle;host=localhost", "root", "");
    $con -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
  
  // if unsuccessful, show error message
  } catch(PDOExeption $e) {
    echo "Connection failed: " . $e -> getMessage();
  }

?>