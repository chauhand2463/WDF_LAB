<?php
require_once __DIR__ . "/session_check.php";
require_once dirname(__DIR__) . "/db.php";
require_once __DIR__ . "/menu.php";

if ($_SESSION["role"] !== "student") {
    http_response_code(403);
    exit("Unauthorized access.");
}

$student_id = $_SESSION["student_id"];
$stmt = $conn->prepare("SELECT student_id, full_name, email, mobile, course FROM students WHERE student_id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    exit("Student not found.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | Student HUB Portal</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <!-- Topbar -->
    <!-- Topbar -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="btn btn--ghost mobile-menu-btn" id="openSidebar" aria-label="Open menu" style="padding:8px 10px;">&#9776;</button>
            <div class="logo">
                <img src="../images/charusat.png" alt="CHARUSAT Logo">
                <span class="logo-wordmark">Student <strong>HUB</strong> <span class="portal-tag">Student Portal</span></span>
            </div>
        </div>
        <div class="topbar-center">
            <div class="topbar-context-badge">
                <span>Student Profile &bull; Academic Records</span>
            </div>
        </div>
        <div class="topbar-right">
            <button id="themeBtn" class="btn btn--ghost" style="padding:8px 14px;">🌙 <span>Dark Mode</span></button>
            <a href="logout.php" class="btn btn--ghost">Sign out</a>
        </div>
    </header>

    <div class="app-shell reveal">

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-inner">
                <nav class="sidebar-nav">
                    <ul class="nav-list">
                        <?= render_dynamic_menu("student") ?>
                    </ul>
                </nav>
                <div class="sidebar-divider"></div>
                <div class="sidebar-footer">
                    <p>Signed in as student</p>
                    <button class="btn btn--ghost" id="closeSidebar">Close menu</button>
                </div>
            </div>
        </aside>

        <!-- Main -->
        <main class="main reveal" style="animation-delay:0.1s">
            <div class="main-inner">

                <div class="page-header">
                    <div class="kicker">Academic Record</div>
                    <h1>Profile</h1>
                    <p>Your enrolment details and contact information.</p>
                </div>

                <div class="content-grid reveal" style="animation-delay:0.2s">
                    <div class="panel">
                        <h2>Student Information</h2>
                        <table class="data-table">
                            <tbody>
                                <tr>
                                    <td style="width:38%;color:var(--ink-secondary);font-family:var(--font-mono);font-size:0.85rem;text-transform:uppercase;letter-spacing:0.06em;">
                                        Student ID
                                    </td>
                                    <td style="font-family:var(--font-mono);">
                                        <?= htmlspecialchars($student['student_id']) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="color:var(--ink-secondary);font-family:var(--font-mono);font-size:0.85rem;text-transform:uppercase;letter-spacing:0.06em;">
                                        Full Name
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($student['full_name']) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="color:var(--ink-secondary);font-family:var(--font-mono);font-size:0.85rem;text-transform:uppercase;letter-spacing:0.06em;">
                                        Email
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($student['email']) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="color:var(--ink-secondary);font-family:var(--font-mono);font-size:0.85rem;text-transform:uppercase;letter-spacing:0.06em;">
                                        Mobile
                                    </td>
                                    <td style="font-family:var(--font-mono);">
                                        <?= htmlspecialchars($student['mobile']) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="color:var(--ink-secondary);font-family:var(--font-mono);font-size:0.85rem;text-transform:uppercase;letter-spacing:0.06em;">
                                        Course
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($student['course']) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="color:var(--ink-secondary);font-family:var(--font-mono);font-size:0.85rem;text-transform:uppercase;letter-spacing:0.06em;">
                                        University
                                    </td>
                                    <td>CHARUSAT</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>

    </div>

    <footer>
        <p>&copy; 2026 CHARUSAT University &bull; Student HUB Portal</p>
    </footer>

    <script src="../js/index.js"></script>

</body>

</html>
