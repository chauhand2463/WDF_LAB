<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set("session.use_strict_mode", "1");
    session_start();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$loginPage = "/practical14/pages/login.php";
$timeoutSeconds = 15 * 60;

if (
    empty($_SESSION["student_id"]) ||
    empty($_SESSION["role"])
) {
    header("Location: " . $loginPage);
    exit;
}

$allowedRoles = ["student", "admin"];

if (!in_array($_SESSION["role"], $allowedRoles, true)) {
    $_SESSION = [];
    session_destroy();

    header("Location: " . $loginPage);
    exit;
}

if (
    !isset($_SESSION["last_activity"]) ||
    !is_numeric($_SESSION["last_activity"]) ||
    (time() - (int) $_SESSION["last_activity"]) >= $timeoutSeconds
) {
    $studentId = $_SESSION["student_id"];

    try {
        require_once __DIR__ . "/../db.php";

        $stmt = $conn->prepare(
            "UPDATE students
             SET remember_token_hash = NULL
             WHERE student_id = :id"
        );

        $stmt->execute([":id" => $studentId]);

    } catch (Throwable $e) {
        error_log("Remember-me invalidation failed: " . $e->getMessage());
    }

    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();

        setcookie(session_name(), "", [
            "expires" => time() - 3600,
            "path" => $params["path"] ?: "/",
            "secure" => $params["secure"],
            "httponly" => true,
            "samesite" => $params["samesite"] ?? "Lax"
        ]);
    }

    $isHttps = (
        !empty($_SERVER["HTTPS"]) &&
        $_SERVER["HTTPS"] !== "off"
    );

    setcookie("studenthub_remember", "", [
        "expires" => time() - 3600,
        "path" => "/",
        "secure" => $isHttps,
        "httponly" => true,
        "samesite" => "Lax"
    ]);

    session_destroy();

    header("Location: " . $loginPage . "?timeout=1");
    exit;
}

$_SESSION["last_activity"] = time();

?>