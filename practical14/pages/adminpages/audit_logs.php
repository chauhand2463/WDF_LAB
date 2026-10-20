
<?php
require_once __DIR__ . "/../session_check.php";
require_once __DIR__ . "/../menu.php";

if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("Unauthorized access. Admin only.");
}

require_once __DIR__ . "/../../db.php";

if (!isset($conn) || !($conn instanceof PDO)) {
    exit("Database connection failed.");
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$search = trim((string) ($_GET["search"] ?? ""));
$actionFilter = trim((string) ($_GET["action"] ?? ""));
$page = max(1, (int) ($_GET["page"] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

$logs = [];
$actions = [];
$total = 0;
$pageCount = 0;
$message = "";

try {
    $actions = $conn->query(
        "SELECT DISTINCT action FROM audit_logs ORDER BY action"
    )->fetchAll(PDO::FETCH_COLUMN);

    $where = [];
    $params = [];

    if ($search !== "") {
        $where[] = "(action LIKE :search
                     OR entity LIKE :search
                     OR entity_id LIKE :search
                     OR student_id LIKE :search
                     OR details LIKE :search)";
        $params[":search"] = "%" . $search . "%";
    }

    if ($actionFilter !== "") {
        $where[] = "action = :action";
        $params[":action"] = $actionFilter;
    }

    $whereSql = $where ? " WHERE " . implode(" AND ", $where) : "";

    $countStmt = $conn->prepare("SELECT COUNT(*) FROM audit_logs" . $whereSql);
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $pageCount = $total > 0 ? (int) ceil($total / $perPage) : 0;
    $page = $pageCount > 0 ? min($page, $pageCount) : 1;
    $offset = ($page - 1) * $perPage;

    $stmt = $conn->prepare(
        "SELECT log_id, student_id, role, action, entity, entity_id,
                details, ip_address, created_at
         FROM audit_logs" . $whereSql . "
         ORDER BY log_id DESC
         LIMIT :limit OFFSET :offset"
    );

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->bindValue(":limit", $perPage, PDO::PARAM_INT);
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $stmt->execute();

    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Audit log listing error: " . $e->getMessage());
    $message = "Could not load audit log records.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Audit Log | StudentHub</title>

    <link rel="stylesheet" href="../../css/style.css">

    <style>
        .audit-card {
            background: var(--surface);
            color: var(--ink);
            padding: 24px;
            margin-bottom: 24px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-card);
        }

        .audit-toolbar {
            display: grid;
            grid-template-columns: 2fr 1fr auto auto;
            gap: 14px;
            align-items: end;
            margin-bottom: 20px;
        }

        .audit-toolbar label {
            display: block;
            font-weight: 600;
            font-size: 0.88rem;
            margin-bottom: 6px;
            color: var(--ink);
        }

        .audit-toolbar input,
        .audit-toolbar select {
            box-sizing: border-box;
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--surface);
            color: var(--ink);
            font: inherit;
        }

        .audit-toolbar input:focus,
        .audit-toolbar select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-soft);
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 18px;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: 0.88rem;
            font-weight: 600;
            transition: all var(--duration-ui) var(--ease-ui);
            text-decoration: none;
        }

        .primary-btn {
            background: var(--accent);
            color: white;
        }

        .primary-btn:hover {
            background: var(--accent-hover);
        }

        .reset-btn {
            background: var(--surface-muted);
            color: var(--ink-secondary);
            border: 1px solid var(--border);
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
        }

        .audit-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        .audit-table th,
        .audit-table td {
            padding: 12px 14px;
            text-align: left;
            border-bottom: 1px solid var(--border);
            vertical-align: top;
        }

        .audit-table th {
            background: var(--surface-muted);
            color: var(--ink-secondary);
            font-weight: 600;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .audit-action {
            font-weight: 600;
            color: var(--accent);
        }

        .pagination {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 18px;
        }

        .pagination a,
        .pagination span {
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            text-decoration: none;
            border: 1px solid var(--border);
            color: var(--ink);
            font-size: 0.88rem;
        }

        .pagination .current {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
            font-weight: 600;
        }

        .message {
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            background: rgba(168, 67, 59, 0.1);
            color: var(--danger);
            border: 1px solid rgba(168, 67, 59, 0.25);
            margin-bottom: 16px;
        }

        @media (max-width: 700px) {
            .audit-toolbar {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<!-- Topbar -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="btn btn--ghost mobile-menu-btn" id="openSidebar" aria-label="Open menu" style="padding:8px 10px;">&#9776;</button>
            <div class="logo">
                <img src="../../images/charusat.png" alt="CHARUSAT Logo">
                <span class="logo-wordmark">Student <strong>HUB</strong> <span class="portal-tag">Admin Console</span></span>
            </div>
        </div>
        <div class="topbar-center">
            <div class="topbar-context-badge">
                <span>System Audit &bull; Security Trail</span>
            </div>
        </div>
        <div class="topbar-right">
            <button id="themeBtn" class="btn btn--ghost" style="padding:8px 14px;">🌙 <span>Dark Mode</span></button>
            <a href="../logout.php" class="btn btn--ghost">Sign out</a>
        </div>
    </header>

<div class="app-shell reveal">

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-inner">
            <nav class="sidebar-nav">
                <ul class="nav-list">
                    <?= render_dynamic_menu("admin", "../") ?>
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
                <div class="kicker">Security Audit</div>
                <h1>Administrative Activity Log</h1>
                <p>Trace of important administrative and security actions. Total records: <strong><?= number_format($total) ?></strong>.</p>
            </div>

    <?php if ($message !== ""): ?>
        <p class="message" role="status"><?= e($message) ?></p>
    <?php endif; ?>

    <section class="audit-card">
        <form method="GET" action="audit_logs.php" class="audit-toolbar">
            <div>
                <label for="search">Search</label>
                <input type="search"
                       id="search"
                       name="search"
                       placeholder="Action, entity, student or details"
                       value="<?= e($search) ?>">
            </div>

            <div>
                <label for="action">Action</label>
                <select id="action" name="action">
                    <option value="">All Actions</option>

                    <?php foreach ($actions as $action): ?>
                        <option value="<?= e($action) ?>"
                            <?= $actionFilter === $action ? "selected" : "" ?>>
                            <?= e($action) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="audit-btn">Filter</button>
            <a href="audit_logs.php" class="audit-btn secondary">Reset</a>
        </form>
    </section>

    <section class="audit-card">
        <div class="table-wrapper">
            <table class="audit-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Action</th>
                        <th>Entity</th>
                        <th>Student</th>
                        <th>Role</th>
                        <th>IP</th>
                        <th>Details</th>
                        <th>Time</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($logs): ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= (int) $log["log_id"] ?></td>
                            <td class="audit-action"><?= e($log["action"]) ?></td>
                            <td>
                                <?= e($log["entity"] ?? "—") ?>
                                <?php if (!empty($log["entity_id"])): ?>
                                    <br><small><?= e($log["entity_id"]) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= e($log["student_id"] ?? "—") ?></td>
                            <td><?= e($log["role"] ?? "—") ?></td>
                            <td><?= e($log["ip_address"] ?? "—") ?></td>
                            <td><?= e($log["details"] ?? "") ?></td>
                            <td><?= e($log["created_at"]) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center;color:#6b7280;">
                            No audit log entries found.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pageCount > 1):
            $queryBase = ["search" => $search, "action" => $actionFilter];
        ?>
            <nav class="pagination" aria-label="Audit log pages">
                <?php
                $prevQuery = http_build_query($queryBase + ["page" => max(1, $page - 1)]);
                $nextQuery = http_build_query($queryBase + ["page" => min($pageCount, $page + 1)]);
                ?>
                <a href="audit_logs.php?<?= e($prevQuery) ?>">Prev</a>
                <span class="current">Page <?= $page ?> of <?= $pageCount ?></span>
                <a href="audit_logs.php?<?= e($nextQuery) ?>">Next</a>
            </nav>
        <?php endif; ?>
    </section>

        </div>
    </main>
</div>

<footer>
    <p style="text-align:center;">&copy; 2026 StudentHub Portal</p>
</footer>

<script src="../../js/index.js"></script>

</body>
</html>
