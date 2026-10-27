<?php

$host = "localhost";
$dbname = "studenthub";
$username = "root";
$password = "";

try {

    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $conn->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

}
catch (PDOException $e) {

    error_log("Database connection failed: " . $e->getMessage());

    die(
        "Database connection failed. Start MySQL in XAMPP and " .
        "import database/studenthub.sql."
    );

}

?>