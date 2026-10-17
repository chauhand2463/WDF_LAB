<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set("session.use_strict_mode", "1");
    session_start();
}

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("X-Content-Type-Options: nosniff");

$timeoutSeconds = 15 * 60;

function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function json_error(string $message, int $status = 400, array $extra = []): void
{
    json_response(
        array_merge(
            [
                "success" => false,
                "error" => $message,
                "status" => $status
            ],
            $extra
        ),
        $status
    );
}

function require_login(): void
{
    global $timeoutSeconds;

    if (empty($_SESSION["student_id"]) || empty($_SESSION["role"])) {
        json_error("Authentication required. Please log in.", 401);
    }

    if (
        !isset($_SESSION["last_activity"]) ||
        !is_numeric($_SESSION["last_activity"]) ||
        (time() - (int) $_SESSION["last_activity"]) >= $timeoutSeconds
    ) {
        $_SESSION = [];
        session_destroy();

        json_error("Session expired. Please log in again.", 401);
    }

    $_SESSION["last_activity"] = time();
}

function require_admin(): void
{
    require_login();

    if (($_SESSION["role"] ?? "") !== "admin") {
        json_error("Administrator privileges are required.", 403);
    }
}

function read_json_body(): array
{
    $raw = file_get_contents("php://input");

    if ($raw === false || trim($raw) === "") {
        return [];
    }

    $data = json_decode($raw, true);

    if (!is_array($data)) {
        json_error("Invalid JSON payload.", 400);
    }

    return $data;
}

function api_csrf_token(): string
{
    if (empty($_SESSION["api_csrf"])) {
        $_SESSION["api_csrf"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["api_csrf"];
}

function verify_api_csrf(?string $token): void
{
    if (
        !is_string($token) ||
        $token === "" ||
        empty($_SESSION["api_csrf"]) ||
        !hash_equals($_SESSION["api_csrf"], $token)
    ) {
        json_error("Invalid or missing CSRF token.", 419);
    }
}

require_once __DIR__ . "/../db.php";

if (!isset($conn) || !($conn instanceof PDO)) {
    json_error("Database connection failed.", 500);
}
