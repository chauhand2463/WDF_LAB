
<?php
require_once __DIR__ . "/../session_check.php";

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
        .audit-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }

        .audit-card {
            background: #fff;
            color: #222;
            padding: 22px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,.10);
            margin-bottom: 22px;
        }

        .audit-toolbar {
            display: grid;
            grid-template-columns: 2fr 1fr auto auto;
            gap: 12px;
            align-items: end;
        }

        .audit-toolbar label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .audit-toolbar input,
        .audit-toolbar select {
            box-sizing: border-box;
            width: 100%;
            padding: 10px;
            border: 1px solid #bbb;
            border-radius: 6px;
        }

        .audit-btn {
            display: inline-block;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            background: #2563eb;
            color: #fff;
        }

        .audit-btn.secondary {
            background: #e5e7eb;
            color: #111;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .audit-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
            font-size: 14px;
        }

        .audit-table th,
        .audit-table td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
        }

        .audit-table th {
            background: #f3f4f6;
        }

        .audit-action {
            font-weight: 600;
        }

        .pagination {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 16px;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            border: 1px solid #d1d5db;
            color: #111;
        }

        .pagination .current {
            background: #2563eb;
            color: #fff;
            border-color: #2563eb;
        }

        .message {
            padding: 12px;
            border-radius: 7px;
            background: #fee2e2;
            color: #991b1b;
        }

        @media (max-width: 700px) {
            .audit-toolbar {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<header class="header">
    <div class="logo">
        <img src="../../images/charusat.png" alt="CHARUSAT Logo">
    </div>

    <button id="menuBtn"
            class="menu-btn"
            type="button"
            aria-label="Open Menu"
            aria-expanded="false">
        ☰
    </button>

    <nav id="navbar">
        <ul>
            <li><a href="../Admin.php">Dashboard</a></li>
            <li><a href="manage_students.php">Manage Students</a></li>
            <li><a href="events.php">Manage Events</a></li>
            <li><a href="../ajax_crud.php">AJAX CRUD</a></li>
            <li><a href="audit_logs.php">Audit Log</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<main class="audit-container">

    <h1>Audit Log</h1>
    <p>
        Trace of important administrative actions. Total records:
        <strong><?= number_format($total) ?></strong>.
    </p>

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

</main>

<footer>
    <p style="text-align:center;">&copy; 2026 StudentHub Portal</p>
</footer>

<script src="../../js/index.js"></script>

</body>
</html>
