<?php

ob_start();

// try to connect to mysql database
try {

    $con = new PDO("mysql:dbname=kpmmddyqrz;host=157.230.154.46:3306", "kpmmddyqrz", "23bVyAhXcH");
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    // if unsuccessful, show error message
} catch (PDOExeption $e) {
    echo "Connection failed: " . $e->getMessage();
}