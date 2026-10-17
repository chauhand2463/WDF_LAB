<?php

require_once "../db.php";

echo "<h2>StudentHub Database Connection</h2>";

echo "<p>Database connection successful!</p>";


try {


    $stmt = $conn->query(
        "SELECT COUNT(*) AS total FROM students"
    );

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<p>Total Students: "
        . htmlspecialchars($result["total"])
        . "</p>";



    $stmt = $conn->query(
        "SELECT COUNT(*) AS total FROM events"
    );

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<p>Total Events: "
        . htmlspecialchars($result["total"])
        . "</p>";



    $stmt = $conn->query(
        "SELECT COUNT(*) AS total FROM registrations"
    );

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<p>Total Registrations: "
        . htmlspecialchars($result["total"])
        . "</p>";

}
catch (PDOException $e) {

    echo "<p>Database error occurred.</p>";

}

?>