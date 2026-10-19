<?php

if (!in_array($_SERVER["REMOTE_ADDR"] ?? "", ["127.0.0.1", "::1"], true)) {
    http_response_code(403);
    exit("Localhost only.");
}

require_once __DIR__ . "/../db.php";

$accounts = [
    ["ADMIN001", "System Administrator", "admin@studenthub.test", "9000000000", "Administration", "Admin@12345", "admin"],
    ["25CE015", "Dhairy Chauhan", "dhairy@studenthub.test", "9428280245", "B.Tech CE", "Student@12345", "student"],
];

$sql = "INSERT INTO students (student_id, full_name, email, mobile, course, password, role)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE password = VALUES(password), role = VALUES(role), remember_token_hash = NULL";
$stmt = $conn->prepare($sql);

echo "<pre>";
try {
    foreach ($accounts as [$id, $name, $email, $mobile, $course, $plain, $role]) {
        $stmt->execute([$id, $name, $email, $mobile, $course, password_hash($plain, PASSWORD_DEFAULT), $role]);
        echo "$role  $id  password: $plain\n";
    }
    echo "\nDone. Delete reset_passwords.php now.";
} catch (PDOException $e) {
    echo "Failed: " . htmlspecialchars($e->getMessage());
}
echo "</pre>";
