
<?php
require_once __DIR__ . "/session_check.php";
require_once __DIR__ . "/menu.php";

if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("Unauthorized access.");
}

require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../audit.php";

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

    <header class="header reveal">

        <div class="header-inner reveal" style="animation-delay:0.05s">

            <div class="logo reveal" style="animation-delay:0.1s">
                <img src="../images/charusat.png" alt="CHARUSAT Logo">
            </div>

            <button
                id="menuBtn"
                class="menu-btn"
                type="button"
                aria-label="Open Menu"
                aria-expanded="false">
                ☰
            </button>

            <nav id="navbar">
                <?php foreach (build_dynamic_menu("admin") as $item): ?>
                    <a href="<?= h($item["href"]) ?>"
                       <?= $item["label"] === "Dashboard" ? 'aria-current="page"' : "" ?>>
                        <?= h($item["label"]) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

        </div>

    </header>

    <main class="admin-dashboard reveal">

        <section class="admin-welcome reveal">
            <h1>Welcome, <?= h($adminName) ?></h1>
            <span class="tag">ADMINISTRATION CONSOLE</span>
            <p>
                Role-based administration console. Menu options are generated from
                your <strong><?= h($_SESSION["role"]) ?></strong> role and all
                administrative actions are recorded in the audit log.
            </p>
        </section>

        <?php if ($dbMessage !== ""): ?>
            <p class="login-message login-message--error" role="status" style="max-width:1100px;margin:0 auto 24px auto;text-align:left;">
                <?= h($dbMessage) ?>
            </p>
        <?php endif; ?>

        <section aria-labelledby="overviewTitle" class="reveal" style="animation-delay:0.15s;">
            <h2 id="overviewTitle">Dashboard Overview</h2>

            <div class="admin-stats reveal" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin:25px 0;">

                <article class="admin-card card reveal">
                    <h2>Students</h2>
                    <p class="admin-number"><?= number_format($totalStudents) ?></p>
                    <p>Accounts with the student role.</p>
                </article>

                <article class="admin-card card reveal">
                    <h2>Administrators</h2>
                    <p class="admin-number"><?= number_format($totalAdmins) ?></p>
                    <p>Accounts with the admin role.</p>
                </article>

                <article class="admin-card card reveal">
                    <h2>Total Events</h2>
                    <p class="admin-number"><?= number_format($totalEvents) ?></p>
                    <p><?= number_format($openEvents) ?> currently open.</p>
                </article>

                <article class="admin-card card reveal">
                    <h2>Registrations</h2>
                    <p class="admin-number"><?= number_format($totalRegistrations) ?></p>
                    <p>Total event registrations.</p>
                </article>

            </div>
        </section>

        <section aria-labelledby="actionsTitle" class="reveal" style="animation-delay:0.25s;">
            <h2 id="actionsTitle">Administration Tools</h2>

            <div class="admin-actions reveal" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px;margin-top:20px;">

                <a class="admin-action btn btn--ghost reveal" href="adminpages/manage_students.php" style="padding:24px;text-align:left;display:block;">
                    <h2 style="margin:0 0 6px 0;font-family:var(--font-display);">Manage Students</h2>
                    <p style="margin:0 0 8px 0;color:var(--text-muted);">Add, view, edit, delete, search, and filter student records.</p>
                    <strong style="color:var(--accent);">Open Student Management →</strong>
                </a>

                <a class="admin-action btn btn--ghost reveal" href="adminpages/events.php" style="padding:24px;text-align:left;display:block;">
                    <h2 style="margin:0 0 6px 0;font-family:var(--font-display);">Manage Events</h2>
                    <p style="margin:0 0 8px 0;color:var(--text-muted);">Create events, upload posters and control event status.</p>
                    <strong style="color:var(--accent);">Open Events →</strong>
                </a>

                <a class="admin-action btn btn--ghost reveal" href="ajax_crud.php" style="padding:24px;text-align:left;display:block;">
                    <h2 style="margin:0 0 6px 0;font-family:var(--font-display);">AJAX CRUD</h2>
                    <p style="margin:0 0 8px 0;color:var(--text-muted);">Manage students and events asynchronously via the JSON API.</p>
                    <strong style="color:var(--accent);">Open AJAX CRUD →</strong>
                </a>

                <a class="admin-action btn btn--ghost reveal" href="adminpages/audit_logs.php" style="padding:24px;text-align:left;display:block;">
                    <h2 style="margin:0 0 6px 0;font-family:var(--font-display);">Audit Log</h2>
                    <p style="margin:0 0 8px 0;color:var(--text-muted);">Review the trail of important administrative actions.</p>
                    <strong style="color:var(--accent);">View Audit Log →</strong>
                </a>

            </div>
        </section>

        <div class="admin-panels reveal" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px;margin-top:25px;animation-delay:0.35s;">

            <section class="admin-card card reveal" aria-labelledby="latestRegTitle">
                <h2 id="latestRegTitle">Latest Registrations</h2>

                <?php if ($latestRegistrations): ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Event</th>
                                <th>Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($latestRegistrations as $reg): ?>
                            <tr>
                                <td>
                                    <?= h($reg["full_name"]) ?>
                                    <br>
                                    <small style="font-family:var(--font-mono);color:var(--text-muted);"><?= h($reg["student_id"]) ?></small>
                                </td>
                                <td><?= h($reg["event_name"]) ?></td>
                                <td><?= h($reg["registered_at"]) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="panel-empty">No registrations yet.</p>
                <?php endif; ?>
            </section>

            <section class="admin-card card reveal" aria-labelledby="recentLogsTitle">
                <h2 id="recentLogsTitle">Recent Audit Activity</h2>

                <?php if ($recentLogs): ?>
                    <ul class="activity-list">
                    <?php foreach ($recentLogs as $log): ?>
                        <li>
                            <span class="activity-action"><?= h($log["action"]) ?></span>
                            <?php if (!empty($log["entity"])): ?>
                                — <?= h($log["entity"]) ?>
                                <?= h($log["entity_id"]) ?>
                            <?php endif; ?>
                            <br>
                            <span class="activity-time">
                                <?= h($log["student_id"] ?? "system") ?>
                                (<?= h($log["role"] ?? "-") ?>)
                                · <?= h($log["created_at"]) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="panel-empty">No audit entries yet.</p>
                <?php endif; ?>
            </section>

        </div>

        <section class="admin-card card reveal" aria-labelledby="chartTitle" style="margin-top:20px;animation-delay:0.45s;">
            <h2 id="chartTitle">Event Registrations by Month</h2>

            <?php if ($monthlyRegistrations): ?>
                <div class="chart">
                <?php foreach ($monthlyRegistrations as $month):
                    $total = (int) $month["total"];
                    $height = $maxMonthly > 0
                        ? max(4, (int) round(($total / $maxMonthly) * 100))
                        : 4;
                ?>
                    <div class="chart-bar">
                        <span class="chart-value"><?= number_format($total) ?></span>
                        <div class="chart-fill" style="height: <?= $height ?>%;"></div>
                        <span class="chart-label"><?= h($month["label"]) ?></span>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="panel-empty">No registration data to chart yet.</p>
            <?php endif; ?>
        </section>

    </main>

    <footer>
        <p style="text-align: center;">
            &copy; 2026 Student HUB Portal
        </p>
    </footer>

    <script src="../js/index.js"></script>

</body>
</html>
