<?php
require_once __DIR__ . "/session_check.php";
require_once __DIR__ . "/menu.php";
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../audit.php";

if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("Unauthorized access.");
}

if (!isset($conn) || !($conn instanceof PDO)) {
    exit("Database connection failed.");
}

$adminName = $_SESSION["full_name"] ?? "Administrator";

$totalStudents = 0;
$totalAdmins = 0;
$totalEvents = 0;
$openEvents = 0;
$totalRegistrations = 0;
$latestRegistrations = [];
$recentLogs = [];
$monthlyRegistrations = [];
$dbMessage = "";

try {
    $stats = $conn->query(
        "SELECT
            COALESCE(SUM(CASE WHEN role = 'student' THEN 1 ELSE 0 END), 0)
                AS total_students,
            COALESCE(SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END), 0)
                AS total_admins
         FROM students"
    )->fetch(PDO::FETCH_ASSOC);

    $totalStudents = (int) ($stats["total_students"] ?? 0);
    $totalAdmins = (int) ($stats["total_admins"] ?? 0);

    $totalEvents = (int) $conn->query(
        "SELECT COUNT(*) FROM events"
    )->fetchColumn();

    $openEvents = (int) $conn->query(
        "SELECT COUNT(*) FROM events WHERE status = 'Open'"
    )->fetchColumn();

    $totalRegistrations = (int) $conn->query(
        "SELECT COUNT(*) FROM registrations"
    )->fetchColumn();

    $latestRegistrations = $conn->query(
        "SELECT r.registered_at,
                r.student_id,
                s.full_name,
                e.event_name
         FROM registrations r
         JOIN students s ON s.student_id = r.student_id
         JOIN events e ON e.event_id = r.event_id
         ORDER BY r.registered_at DESC, r.registration_id DESC
         LIMIT 5"
    )->fetchAll(PDO::FETCH_ASSOC);

    $recentLogs = $conn->query(
        "SELECT log_id, student_id, role, action, entity, entity_id, created_at
         FROM audit_logs
         ORDER BY log_id DESC
         LIMIT 6"
    )->fetchAll(PDO::FETCH_ASSOC);

    $monthlyRegistrations = $conn->query(
        "SELECT DATE_FORMAT(registered_at, '%Y-%m') AS ym,
                DATE_FORMAT(registered_at, '%b %Y') AS label,
                COUNT(*) AS total
         FROM registrations
         GROUP BY ym, label
         ORDER BY ym DESC
         LIMIT 6"
    )->fetchAll(PDO::FETCH_ASSOC);

    $monthlyRegistrations = array_reverse($monthlyRegistrations);

} catch (Throwable $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    $dbMessage = "Unable to load statistics. Please check the database connection.";
}

$maxMonthly = 0;

foreach ($monthlyRegistrations as $row) {
    $maxMonthly = max($maxMonthly, (int) $row["total"]);
}

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | Student HUB</title>

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
                    <span class="kicker">Administration</span>
                    <h1>Admin Dashboard</h1>
                    <p style="margin-top:6px;color:var(--ink-secondary);font-size:0.9rem;">
                        Role-based administration console &mdash; all actions are recorded in the audit log
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
                        <?php foreach (build_dynamic_menu("admin") as $item): ?>
                            <li><a href="<?= h($item["href"]) ?>" <?= $item["label"] === "Dashboard" ? 'aria-current="page"' : "" ?>><?= h($item["label"]) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
                <div class="sidebar-divider"></div>
                <div class="sidebar-footer">
                    <p>Signed in as administrator</p>
                    <button class="btn btn--ghost" id="closeSidebar">Close menu</button>
                </div>
            </div>
        </aside>

        <main class="main reveal" style="animation-delay:0.1s">
            <div class="main-inner">

                <div class="page-header">
                    <div class="kicker">Dashboard Overview</div>
                    <h1>Welcome, <?= h($adminName) ?></h1>
                    <p>Role-based administration console. Menu options are generated from your role.</p>
                </div>

                <?php if ($dbMessage !== ""): ?>
                    <div class="form-message form-message--error reveal" style="max-width:640px;margin-bottom:24px;text-align:left;">
                        <?= h($dbMessage) ?>
                    </div>
                <?php endif; ?>

                <!-- Metric summary -->
                <div class="metric-grid reveal" style="animation-delay:0.2s">
                    <div class="metric metric--accent">
                        <div class="metric-label">Students</div>
                        <div class="metric-value"><?= number_format($totalStudents) ?></div>
                        <div class="metric-context">Accounts with the student role.</div>
                    </div>
                    <div class="metric">
                        <div class="metric-label">Administrators</div>
                        <div class="metric-value"><?= number_format($totalAdmins) ?></div>
                        <div class="metric-context">Accounts with the admin role.</div>
                    </div>
                    <div class="metric">
                        <div class="metric-label">Total Events</div>
                        <div class="metric-value"><?= number_format($totalEvents) ?></div>
                        <div class="metric-context"><?= number_format($openEvents) ?> currently open.</div>
                    </div>
                    <div class="metric">
                        <div class="metric-label">Registrations</div>
                        <div class="metric-value"><?= number_format($totalRegistrations) ?></div>
                        <div class="metric-context">Total event registrations.</div>
                    </div>
                </div>

                <!-- Administration tools -->
                <div class="section reveal" style="animation-delay:0.3s">
                    <h2>Administration Tools</h2>
                    <div class="content-grid content-grid--two" style="grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;">
                        <a class="admin-action panel btn--ghost" href="adminpages/manage_students.php" style="text-decoration:none;color:inherit;display:block;">
                            <h3 style="margin:0 0 8px 0;font-family:var(--font-heading);font-size:1.15rem;">Manage Students</h3>
                            <p style="margin:0;color:var(--ink-secondary);font-size:0.9rem;">Add, view, edit, delete, search, and filter student records.</p>
                        </a>
                        <a class="admin-action panel btn--ghost" href="adminpages/events.php" style="text-decoration:none;color:inherit;display:block;">
                            <h3 style="margin:0 0 8px 0;font-family:var(--font-heading);font-size:1.15rem;">Manage Events</h3>
                            <p style="margin:0;color:var(--ink-secondary);font-size:0.9rem;">Create events and control event status.</p>
                        </a>
                        <a class="admin-action panel btn--ghost" href="ajax_crud.php" style="text-decoration:none;color:inherit;display:block;">
                            <h3 style="margin:0 0 8px 0;font-family:var(--font-heading);font-size:1.15rem;">AJAX CRUD</h3>
                            <p style="margin:0;color:var(--ink-secondary);font-size:0.9rem;">Manage students and events asynchronously via the JSON API.</p>
                        </a>
                        <a class="admin-action panel btn--ghost" href="adminpages/audit_logs.php" style="text-decoration:none;color:inherit;display:block;">
                            <h3 style="margin:0 0 8px 0;font-family:var(--font-heading);font-size:1.15rem;">Audit Log</h3>
                            <p style="margin:0;color:var(--ink-secondary);font-size:0.9rem;">Review the trail of important administrative actions.</p>
                        </a>
                    </div>
                </div>

                <!-- Latest registrations + recent audit -->
                <div class="content-grid content-grid--two reveal" style="animation-delay:0.4s">
                    <div class="panel">
                        <h2>Latest Registrations</h2>
                        <?php if ($latestRegistrations): ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Event</th>
                                        <th class="numeric">Registered</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($latestRegistrations as $reg): ?>
                                    <tr>
                                        <td>
                                            <div style="font-family:var(--font-mono);font-size:0.85rem;"><?= h($reg["student_id"]) ?></div>
                                            <?= h($reg["full_name"]) ?>
                                        </td>
                                        <td><?= h($reg["event_name"]) ?></td>
                                        <td class="numeric" style="font-family:var(--font-mono);font-size:0.85rem;"><?= h($reg["registered_at"]) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <h3>No registrations yet</h3>
                                <p>No event registrations have been recorded.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="panel">
                        <h2>Recent Audit Activity</h2>
                        <?php if ($recentLogs): ?>
                            <ul style="list-style:none;padding:0;margin:0;">
                            <?php foreach ($recentLogs as $log): ?>
                                <li style="padding:10px 0;border-bottom:1px solid var(--border);font-size:0.9rem;">
                                    <span style="font-weight:600;color:var(--accent);font-family:var(--font-mono);font-size:0.85rem;"><?= h($log["action"]) ?></span>
                                    <?php if (!empty($log["entity"])): ?>
                                        <span style="color:var(--ink-secondary);"> — <?= h($log["entity"]) ?> <?= h($log["entity_id"]) ?></span>
                                    <?php endif; ?>
                                    <div style="font-size:0.8rem;color:var(--ink-muted);font-family:var(--font-mono);margin-top:4px;">
                                        <?= h($log["student_id"] ?? "system") ?> (<?= h($log["role"] ?? "-") ?>) · <?= h($log["created_at"]) ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="empty-state" style="padding:24px 0;">
                                <h3 style="font-size:1rem;">No audit entries yet</h3>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Registrations by month chart -->
                <div class="panel reveal" style="animation-delay:0.5s;">
                    <h2>Event Registrations by Month</h2>
                    <?php if ($monthlyRegistrations): ?>
                        <div class="chart" style="height:140px;padding-top:18px;display:flex;align-items:flex-end;gap:14px;">
                        <?php foreach ($monthlyRegistrations as $month):
                            $total = (int) $month["total"];
                            $height = $maxMonthly > 0
                                ? max(4, (int) round(($total / $maxMonthly) * 100))
                                : 4;
                        ?>
                            <div class="chart-bar" style="flex:1;display:flex;flex-direction:column;justify-content:flex-end;align-items:center;height:100%;gap:6px;">
                                <span class="chart-value" style="font-family:var(--font-mono);font-size:0.8rem;font-weight:700;color:var(--ink);"><?= number_format($total) ?></span>
                                <div class="chart-fill" style="width:100%;background:linear-gradient(180deg,var(--accent),var(--accent-hover));border-radius:6px 6px 0 0;min-height:4px;height:<?= $height ?>%;"></div>
                                <span class="chart-label" style="font-size:0.78rem;color:var(--ink-muted);text-align:center;line-height:1.2;"><?= h($month["label"]) ?></span>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state" style="padding:24px 0;">
                            <h3 style="font-size:1rem;">No registration data to chart yet</h3>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </main>

    </div>

    <footer>
        <p style="text-align:center;">&copy; 2026 Student HUB Portal</p>
    </footer>

    <script src="../js/index.js"></script>

</body>
</html>
