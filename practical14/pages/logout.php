
<?php
session_start();
require_once "../db.php";
require_once __DIR__ . "/../audit.php";

$isHttps = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off";

if (!empty($_SESSION["student_id"])) {
    log_audit($conn, "logout", "session", $_SESSION["student_id"], "User logged out");

    $stmt = $conn->prepare(
        "UPDATE students
         SET remember_token_hash = NULL
         WHERE student_id = :id"
    );

    $stmt->execute([
        ":id" => $_SESSION["student_id"]
    ]);
}

setcookie("studenthub_remember", "", [
    "expires" => time() - 3600,
    "path" => "/",
    "secure" => $isHttps,
    "httponly" => true,
    "samesite" => "Lax"
]);

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(session_name(), "", [
        "expires" => time() - 42000,
        "path" => $params["path"],
        "secure" => $params["secure"],
        "httponly" => $params["httponly"],
        "samesite" => $params["samesite"] ?? "Lax"
    ]);
}

session_destroy();

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = dirname($scriptName);
if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
    $basePath = '';
}
$loginPage = ($basePath ? $basePath . '/login.php' : '/login.php');
header("Location: " . $loginPage);
exit;
?>