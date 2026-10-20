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

    <title>Assignments | Student HUB Portal</title>

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
                <span>Coursework &bull; 2 Pending Submissions</span>
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
                    <div class="kicker">Coursework</div>
                    <h1>Assignments &amp; Projects</h1>
                    <p>Track your assignments, milestones, and grading status for the semester.</p>
                </div>

                <!-- Metrics -->
                <div class="metric-grid reveal" style="animation-delay:0.15s; margin-bottom:24px;">
                    <div class="metric metric--accent">
                        <div class="metric-label">Total Assigned</div>
                        <div class="metric-value">4</div>
                        <div class="metric-context">All registered courses</div>
                    </div>
                    <div class="metric">
                        <div class="metric-label">Pending Submissions</div>
                        <div class="metric-value">2</div>
                        <div class="metric-context">Due within 7 days</div>
                    </div>
                    <div class="metric">
                        <div class="metric-label">Completed &amp; Submitted</div>
                        <div class="metric-value">2</div>
                        <div class="metric-context">50% semester completion</div>
                    </div>
                    <div class="metric">
                        <div class="metric-label">Average Score</div>
                        <div class="metric-value">92%</div>
                        <div class="metric-context">Graded coursework</div>
                    </div>
                </div>

                <div class="content-grid reveal" style="animation-delay:0.2s">
                    <div class="panel">
                        <h2>Current Assignment Submissions</h2>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Assignment Title</th>
                                    <th>Deadline</th>
                                    <th class="numeric">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Mathematics (MA101)</td>
                                    <td>Differential Equations Problem Set 1</td>
                                    <td>20-Aug-2026</td>
                                    <td class="numeric"><span class="status status--warning">Pending</span></td>
                                </tr>
                                <tr>
                                    <td>Computer Engineering (CE201)</td>
                                    <td>Data Structures: AVL Trees &amp; Hashing</td>
                                    <td>22-Aug-2026</td>
                                    <td class="numeric"><span class="status status--success">Submitted</span></td>
                                </tr>
                                <tr>
                                    <td>Physics (PY102)</td>
                                    <td>Electromagnetism Lab Simulation Report</td>
                                    <td>25-Aug-2026</td>
                                    <td class="numeric"><span class="status status--warning">Pending</span></td>
                                </tr>
                                <tr>
                                    <td>English &amp; Comm. (HS101)</td>
                                    <td>Technical Writing &amp; Presentation Draft</td>
                                    <td>28-Aug-2026</td>
                                    <td class="numeric"><span class="status status--success">Submitted</span></td>
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
