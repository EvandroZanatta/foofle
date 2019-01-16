<?php

ob_start();

$url = parse_url(getenv("CLEARDB_DATABASE_URL"));

$server = $url["host"];
$username = $url["user"];
$password = $url["pass"];
$db = substr($url["path"], 1);

// try to connect to mysql database
try {

    $con = new PDO("mysql:dbname=$db;host=$server", "$username", "$password");
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    // if unsuccessful, show error message
} catch (PDOExeption $e) {
    echo "Connection failed: " . $e->getMessage();
}