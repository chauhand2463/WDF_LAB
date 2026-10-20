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

    <title>Dashboard | Student HUB Portal</title>

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
                <span>Semester II &bull; Academic Year 2025&ndash;26</span>
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

                <!-- Page header -->
                <div class="page-header">
                    <div class="kicker">Overview</div>
                    <h1>Dashboard</h1>
                    <p>Track attendance, assignments, timetable, and notices in one place.</p>
                </div>

                <!-- Metric summary -->
                <div class="metric-grid reveal" style="animation-delay:0.2s">
                    <div class="metric metric--accent">
                        <div class="metric-label">Overall Attendance</div>
                        <div class="metric-value">85%</div>
                        <div class="metric-context">25 of 29 classes attended</div>
                    </div>
                    <div class="metric">
                        <div class="metric-label">Semester SGPA</div>
                        <div class="metric-value">7.95</div>
                        <div class="metric-context">Semester II &bull; 2025&ndash;26</div>
                    </div>
                    <div class="metric">
                        <div class="metric-label">Pending Assignments</div>
                        <div class="metric-value">2</div>
                        <div class="metric-context">Due within 7 days</div>
                    </div>
                    <div class="metric">
                        <div class="metric-label">Upcoming Exams</div>
                        <div class="metric-value">1</div>
                        <div class="metric-context">Java &bull; Monday</div>
                    </div>
                </div>

                <div class="content-grid content-grid--two reveal" style="animation-delay:0.3s">

                    <!-- Primary column -->
                    <div class="panel">
                        <h2>Attendance</h2>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th class="numeric">Attendance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Computer Network</td>
                                    <td class="numeric"><span class="status status--success">85%</span></td>
                                </tr>
                                <tr>
                                    <td>Java Programming</td>
                                    <td class="numeric"><span class="status status--success">90%</span></td>
                                </tr>
                                <tr>
                                    <td>Web Development</td>
                                    <td class="numeric"><span class="status status--warning">80%</span></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="progress" style="margin-top:16px;">
                            <div class="progress-bar" style="width:85%;"></div>
                        </div>
                    </div>

                    <div class="panel">
                        <h2>Assignments</h2>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Assignment</th>
                                    <th>Due Date</th>
                                    <th class="numeric">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Mathematics &mdash; Assignment 1</td>
                                    <td>20 Aug 2026</td>
                                    <td class="numeric"><span class="status status--warning">Pending</span></td>
                                </tr>
                                <tr>
                                    <td>Computer Engineering &mdash; Assignment 2</td>
                                    <td>22 Aug 2026</td>
                                    <td class="numeric"><span class="status status--success">Submitted</span></td>
                                </tr>
                                <tr>
                                    <td>Physics &mdash; Assignment 3</td>
                                    <td>25 Aug 2026</td>
                                    <td class="numeric"><span class="status status--warning">Pending</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>

                <!-- Secondary column -->
                <div class="content-grid content-grid--two reveal" style="animation-delay:0.4s">

                    <div class="panel">
                        <h2>Today's Timetable</h2>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Course</th>
                                    <th>Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="numeric">9:10 AM</td>
                                    <td>Computer Network</td>
                                    <td>Room 204</td>
                                </tr>
                                <tr>
                                    <td class="numeric">10:20 AM</td>
                                    <td>Java Programming</td>
                                    <td>Lab 3</td>
                                </tr>
                                <tr>
                                    <td class="numeric">11:30 AM</td>
                                    <td>Web Development</td>
                                    <td>Room 108</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="panel">
                        <h2>Notices</h2>
                        <div style="display:flex;flex-direction:column;gap:10px;">
                            <div style="padding:12px 0;border-bottom:1px solid var(--border);">
                                <div style="font-weight:600;color:var(--ink);">Java Examination</div>
                                <div style="font-size:0.85rem;color:var(--ink-muted);">Java Programming examination on Monday.</div>
                            </div>
                            <div style="padding:12px 0;border-bottom:1px solid var(--border);">
                                <div style="font-weight:600;color:var(--ink);">Network Assignment Due</div>
                                <div style="font-size:0.85rem;color:var(--ink-muted);">Submit before Friday.</div>
                            </div>
                            <div style="padding:12px 0;">
                                <div style="font-weight:600;color:var(--ink);">WDF Practical Submission</div>
                                <div style="font-size:0.85rem;color:var(--ink-muted);">Next week &mdash; Student HUB Portal.</div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </main>

    </div>

    <footer class="reveal">
        <p>&copy; 2026 CHARUSAT University &bull; Student HUB Portal</p>
    </footer>

    <script src="../js/index.js"></script>
    <script src="../js/json-test.js"></script>

</body>

</html>

