
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

    <style>
        .admin-dashboard {
            max-width: 1150px;
            margin: 30px auto;
            padding: 20px;
        }

        .admin-welcome {
            margin-bottom: 25px;
        }

        .admin-welcome h1 {
            margin-bottom: 8px;
        }

        .admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }

        .admin-card {
            padding: 24px;
            border-radius: 12px;
            background: var(--card-bg, #fff);
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.10);
        }

        .admin-card h2 {
            font-size: 18px;
            margin-top: 0;
        }

        .admin-number {
            font-size: 32px;
            font-weight: bold;
            margin: 12px 0;
        }

        .admin-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .admin-action {
            display: block;
            padding: 24px;
            border-radius: 12px;
            background: var(--card-bg, #fff);
            color: inherit;
            text-decoration: none;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.10);
            transition: transform 0.2s ease;
        }

        .admin-action:hover {
            transform: translateY(-4px);
        }

        .admin-action h2 {
            margin-top: 0;
        }

        .admin-action:focus-visible {
            outline: 3px solid #2563eb;
            outline-offset: 3px;
        }

        .admin-message {
            padding: 12px;
            margin: 15px 0;
            border-radius: 8px;
            background: #fff3cd;
            color: #664d03;
        }

        .admin-navigation {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 18px;
        }

        .admin-navigation a {
            text-decoration: none;
        }

        .admin-panels {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .admin-table th,
        .admin-table td {
            text-align: left;
            padding: 9px 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        .admin-table th {
            background: #f3f4f6;
        }

        .activity-list {
            list-style: none;
            padding: 0;
            margin: 0;
            font-size: 14px;
        }

        .activity-list li {
            padding: 9px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .activity-action {
            font-weight: 600;
        }

        .activity-time {
            color: #6b7280;
            font-size: 12px;
        }

        .chart {
            display: flex;
            align-items: flex-end;
            gap: 14px;
            height: 180px;
            padding-top: 20px;
        }

        .chart-bar {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            align-items: center;
            height: 100%;
            gap: 6px;
        }

        .chart-fill {
            width: 100%;
            background: linear-gradient(180deg, #3b82f6, #2563eb);
            border-radius: 6px 6px 0 0;
            min-height: 4px;
        }

        .chart-value {
            font-size: 12px;
            font-weight: 600;
        }

        .chart-label {
            font-size: 12px;
            color: #4b5563;
        }

        .panel-empty {
            color: #6b7280;
            font-size: 14px;
        }

        @media (max-width: 600px) {
            .admin-dashboard {
                padding: 12px;
            }

            .admin-card,
            .admin-action {
                padding: 18px;
            }
        }
    </style>
</head>

<body>

    <header class="header">

        <div class="logo">
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

        <nav id="navbar" class="admin-navigation">
            <?php foreach (build_dynamic_menu("admin") as $item): ?>
                <a href="<?= h($item["href"]) ?>"
                   <?= $item["label"] === "Dashboard" ? 'aria-current="page"' : "" ?>>
                    <?= h($item["label"]) ?>
                </a>
            <?php endforeach; ?>
        </nav>

    </header>

    <main class="admin-dashboard">

        <section class="admin-welcome">
            <h1>Welcome, <?= h($adminName) ?> (Administrator)</h1>

            <p>
                Role-based administration console. Menu options are generated from
                your <strong><?= h($_SESSION["role"]) ?></strong> role and all
                administrative actions are recorded in the audit log.
            </p>
        </section>

        <?php if ($dbMessage !== ""): ?>
            <p class="admin-message" role="status"><?= h($dbMessage) ?></p>
        <?php endif; ?>

        <section aria-labelledby="overviewTitle">
            <h2 id="overviewTitle">Dashboard Overview</h2>

            <div class="admin-stats">

                <article class="admin-card">
                    <h2>Students</h2>
                    <p class="admin-number"><?= number_format($totalStudents) ?></p>
                    <p>Accounts with the student role.</p>
                </article>

                <article class="admin-card">
                    <h2>Administrators</h2>
                    <p class="admin-number"><?= number_format($totalAdmins) ?></p>
                    <p>Accounts with the admin role.</p>
                </article>

                <article class="admin-card">
                    <h2>Total Events</h2>
                    <p class="admin-number"><?= number_format($totalEvents) ?></p>
                    <p><?= number_format($openEvents) ?> currently open.</p>
                </article>

                <article class="admin-card">
                    <h2>Registrations</h2>
                    <p class="admin-number"><?= number_format($totalRegistrations) ?></p>
                    <p>Total event registrations.</p>
                </article>

            </div>
        </section>

        <section aria-labelledby="actionsTitle">
            <h2 id="actionsTitle">Administration Tools</h2>

            <div class="admin-actions">

                <a class="admin-action" href="adminpages/manage_students.php">
                    <h2>Manage Students</h2>
                    <p>Add, view, edit, delete, search, and filter student records.</p>
                    <strong>Open Student Management →</strong>
                </a>

                <a class="admin-action" href="adminpages/events.php">
                    <h2>Manage Events</h2>
                    <p>Create events, upload posters and control event status.</p>
                    <strong>Open Events →</strong>
                </a>

                <a class="admin-action" href="ajax_crud.php">
                    <h2>AJAX CRUD</h2>
                    <p>Manage students and events asynchronously via the JSON API.</p>
                    <strong>Open AJAX CRUD →</strong>
                </a>

                <a class="admin-action" href="adminpages/audit_logs.php">
                    <h2>Audit Log</h2>
                    <p>Review the trail of important administrative actions.</p>
                    <strong>View Audit Log →</strong>
                </a>

            </div>
        </section>

        <div class="admin-panels">

            <section class="admin-card" aria-labelledby="latestRegTitle">
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
                                    <small><?= h($reg["student_id"]) ?></small>
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

            <section class="admin-card" aria-labelledby="recentLogsTitle">
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

        <section class="admin-card" aria-labelledby="chartTitle" style="margin-top:20px;">
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
