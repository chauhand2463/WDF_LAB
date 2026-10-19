<?php
require_once __DIR__ . "/session_check.php";
require_once __DIR__ . "/menu.php";

if ($_SESSION["role"] !== "student") {
    http_response_code(403);
    exit("Unauthorized access.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Attendance | Student HUB Portal</title>

    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <div class="app-shell reveal">

        <header class="topbar">
            <div class="topbar-left">
                <div class="logo">
                    <img src="../images/charusat.png" alt="CHARUSAT Logo">
                    <span class="logo-wordmark">Student <strong>HUB</strong></span>
                </div>
            </div>
            <div class="topbar-center">
                <div class="page-title-wrap reveal" style="animation-delay:0.05s">
                    <span class="kicker">Attendance</span>
                    <h1>Class Attendance</h1>
                    <p style="margin-top:6px;color:var(--ink-secondary);font-size:0.9rem;">
                        Track your attendance across registered courses
                    </p>
                </div>
            </div>
            <div class="topbar-right">
                <a href="logout.php" class="btn btn--ghost">Sign out</a>
            </div>
        </header>

        <aside class="sidebar" id="sidebar">
            <div class="sidebar-inner">
                <div class="sidebar-brand">
                    <img src="../images/charusat.png" alt="CHARUSAT Logo">
                    <span>Student <strong>HUB</strong></span>
                </div>
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

        <main class="main reveal" style="animation-delay:0.1s">
            <div class="main-inner">

                <div class="page-header">
                    <div class="kicker">Attendance Records</div>
                    <h1>Attendance</h1>
                    <p>View your attendance percentage by subject this semester.</p>
                </div>

                <div class="content-grid reveal" style="animation-delay:0.2s">
                    <div class="panel">
                        <h2>Subject-wise Attendance</h2>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th class="numeric">Attendance</th>
                                    <th class="numeric">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Computer Network</td>
                                    <td class="numeric">85%</td>
                                    <td class="numeric"><span class="status status--success">Good</span></td>
                                </tr>
                                <tr>
                                    <td>Java Programming</td>
                                    <td class="numeric">90%</td>
                                    <td class="numeric"><span class="status status--success">Good</span></td>
                                </tr>
                                <tr>
                                    <td>Web Development</td>
                                    <td class="numeric">80%</td>
                                    <td class="numeric"><span class="status status--warning">Watch</span></td>
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
