<?php

session_start();
require_once "../db.php";
require_once __DIR__ . "/../audit.php";

$isHttps = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off";
$timeout = 15 * 60;
$cookieName = "studenthub_remember";
$message = "";
$messageType = "error";

function redirectByRole($role)
{
    header("Location: " . ($role === "admin" ? "admin.php" : "index.php"));
    exit;
}

function clearRememberCookie($name, $isHttps)
{
    setcookie($name, "", [
        "expires" => time() - 3600,
        "path" => "/",
        "secure" => $isHttps,
        "httponly" => true,
        "samesite" => "Lax"
    ]);
}

if (isset($_SESSION["student_id"])) {
    if (
        !isset($_SESSION["last_activity"]) ||
        time() - $_SESSION["last_activity"] >= $timeout
    ) {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();

            setcookie(session_name(), "", [
                "expires" => time() - 3600,
                "path" => $params["path"],
                "secure" => $params["secure"],
                "httponly" => true,
                "samesite" => "Lax"
            ]);
        }

        session_destroy();
        clearRememberCookie($cookieName, $isHttps);

        header("Location: login.php?timeout=1");
        exit;
    }

    $_SESSION["last_activity"] = time();
}

if (isset($_GET["timeout"])) {
    $message = "Session expired. Please log in again.";
}

if (isset($_SESSION["student_id"])) {
    redirectByRole($_SESSION["role"] ?? "student");
}

if (
    $_SERVER["REQUEST_METHOD"] !== "POST" &&
    !isset($_GET["timeout"]) &&
    !empty($_COOKIE[$cookieName])
) {
    $token = $_COOKIE[$cookieName];

    if (preg_match('/^[a-f0-9]{64}$/', $token)) {
        try {
            $stmt = $conn->prepare(
                "SELECT student_id, full_name, email, role
                 FROM students
                 WHERE remember_token_hash = :token"
            );

            $stmt->execute([
                ":token" => hash("sha256", $token)
            ]);

            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($student) {
                session_regenerate_id(true);

                $_SESSION["student_id"] = $student["student_id"];
                $_SESSION["full_name"] = $student["full_name"];
                $_SESSION["email"] = $student["email"];
                $_SESSION["role"] = $student["role"];
                $_SESSION["last_activity"] = time();

                log_audit($conn, "login_remember", "session", $student["student_id"], "Remember-me auto login");

                redirectByRole($student["role"]);
            }
        } catch (PDOException $e) {
            $message = "Database error. Please try again.";
        }
    }

    clearRememberCookie($cookieName, $isHttps);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $studentID = trim($_POST["studentID"] ?? "");
    $studentpass = $_POST["studentpass"] ?? "";
    $remember = isset($_POST["remember"]);

    if ($studentID === "") {
        $message = "Please enter your Student ID.";
        $messageType = "error";
    } elseif ($studentpass === "") {
        $message = "Please enter your password.";
        $messageType = "error";
    } else {
        try {
            $stmt = $conn->prepare(
                "SELECT student_id, full_name, email, password, role
                 FROM students
                 WHERE student_id = :id"
            );

            $stmt->execute([
                ":id" => $studentID
            ]);

            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if (
                !$student ||
                !password_verify($studentpass, $student["password"])
            ) {
                $message = "Invalid Student ID or password.";
                $messageType = "error";
            } else {
                session_regenerate_id(true);

                $_SESSION["student_id"] = $student["student_id"];
                $_SESSION["full_name"] = $student["full_name"];
                $_SESSION["email"] = $student["email"];
                $_SESSION["role"] = $student["role"];
                $_SESSION["last_activity"] = time();

                $stmt = $conn->prepare(
                    "UPDATE students
                     SET last_login = NOW(),
                         remember_token_hash = NULL
                     WHERE student_id = :id"
                );

                $stmt->execute([
                    ":id" => $student["student_id"]
                ]);

                if ($remember) {
                    $token = bin2hex(random_bytes(32));

                    $stmt = $conn->prepare(
                        "UPDATE students
                         SET remember_token_hash = :token
                         WHERE student_id = :id"
                    );

                    $stmt->execute([
                        ":token" => hash("sha256", $token),
                        ":id" => $student["student_id"]
                    ]);

                    setcookie($cookieName, $token, [
                        "expires" => time() + (30 * 24 * 60 * 60),
                        "path" => "/",
                        "secure" => $isHttps,
                        "httponly" => true,
                        "samesite" => "Lax"
                    ]);
                } else {
                    clearRememberCookie($cookieName, $isHttps);
                }

                log_audit($conn, "login", "session", $student["student_id"], "Successful login");

                redirectByRole($student["role"]);
            }
        } catch (PDOException $e) {
            $message = "Database error. Please try again.";
            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>CHARUSAT | Student Login</title>

    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <!-- Header -->
    <header class="header reveal">

        <div class="header-inner reveal" style="animation-delay:0.05s">

            <div class="logo reveal" style="animation-delay:0.1s">
                <img src="../images/charusat.png" alt="CHARUSAT Logo">
            </div>

            <nav>
                <ul class="nav-list">
                    <li><a href="register.php">Create Account</a></li>
                </ul>
            </nav>

        </div>

    </header>

    <main>

        <div class="page-title-wrap reveal">
            <h1>Welcome Back</h1>
            <span class="tag">STUDENT LOGIN</span>
        </div>

        <div class="page-intro reveal">Sign in to access your student portal.</div>

        <div class="login-container box reveal" style="max-width:460px;margin:0 auto 44px auto;">

            <?php if ($message !== ""): ?>
                <p class="login-message login-message--<?= $messageType ?>">
                    <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
                </p>
            <?php endif; ?>

            <form id="loginForm" method="POST" action="login.php">

                <div class="form-group reveal">
                    <label for="studentID">Student ID <span>*</span></label>
                    <input
                        type="text"
                        id="studentID"
                        name="studentID"
                        placeholder="Example: 24CE001"
                        autocomplete="username"
                        required>
                </div>

                <div class="form-group reveal">
                    <label for="studentpass">Password <span>*</span></label>
                    <input
                        type="password"
                        id="studentpass"
                        name="studentpass"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required>
                </div>

                <div class="login-options reveal" style="display:flex;align-items:center;gap:10px;color:var(--text-muted);font-size:0.9rem;">
                    <label class="remember" style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="#" style="color:var(--accent);margin-left:auto;">Forgot Password?</a>
                </div>

                <div class="submit-section reveal" style="margin-top:8px;">
                    <button type="submit" class="btn btn--accent">Login</button>
                    <button type="button" class="btn btn--ghost" onclick="window.location.href='role.php'">Sign up</button>
                </div>

            </form>

        </div>

    </main>

    <footer>
        <h1>© 2026 CHARUSAT University &nbsp;|&nbsp; Student Portal</h1>
    </footer>

    <script src="../js/login.js"></script>

</body>
</html>
