
<?php
require_once "../session_check.php";
require_once __DIR__ . "/../menu.php";

if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("Unauthorized access.");
}

require_once "../../db.php";
require_once __DIR__ . "/../../audit.php";

if (empty($_SESSION["event_csrf"])) {
    $_SESSION["event_csrf"] = bin2hex(random_bytes(32));
}

$uploadDir = dirname(__DIR__, 2) . "/uploads/event_posters/";
$uploadUrl = "../../uploads/event_posters/";
$maxSize = 2 * 1024 * 1024;

if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

$message = "";
$messageType = "success";
$editEvent = null;

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function savePoster(array $file, string $dir, int $maxSize): ?string
{
    $error = $file["error"] ?? UPLOAD_ERR_NO_FILE;

    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException("Poster upload failed.");
    }

    if (
        empty($file["tmp_name"]) ||
        !is_uploaded_file($file["tmp_name"]) ||
        empty($file["size"]) ||
        $file["size"] > $maxSize
    ) {
        throw new RuntimeException("Poster must be smaller than 2 MB.");
    }

    if (!is_dir($dir) || !is_writable($dir)) {
        throw new RuntimeException("Poster upload folder is not writable.");
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file["tmp_name"]);

    $allowed = [
        "image/jpeg" => "jpg",
        "image/png"  => "png",
        "image/webp" => "webp"
    ];

    if (!isset($allowed[$mime])) {
        throw new RuntimeException("Only JPG, PNG and WebP images are allowed.");
    }

    $imageInfo = @getimagesize($file["tmp_name"]);

    if (!$imageInfo || ($imageInfo["mime"] ?? "") !== $mime) {
        throw new RuntimeException("Invalid image file.");
    }

    $filename = bin2hex(random_bytes(16)) . "." . $allowed[$mime];

    if (!move_uploaded_file($file["tmp_name"], $dir . $filename)) {
        throw new RuntimeException("Could not save the poster.");
    }

    return $filename;
}

function removePoster(?string $filename, string $dir): void
{
    if (!$filename || basename($filename) !== $filename) {
        return;
    }

    $path = $dir . $filename;

    if (is_file($path)) {
        @unlink($path);
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $token = $_POST["csrf_token"] ?? "";

        if (
            !is_string($token) ||
            !hash_equals($_SESSION["event_csrf"], $token)
        ) {
            throw new RuntimeException("Invalid request. Please refresh the page.");
        }

        $action = $_POST["action"] ?? "";

        if ($action === "delete") {
            $id = filter_var($_POST["event_id"] ?? null, FILTER_VALIDATE_INT);

            if (!$id || $id < 1) {
                throw new RuntimeException("Invalid event ID.");
            }

            $stmt = $conn->prepare(
                "SELECT poster_path FROM events WHERE event_id = :id"
            );
            $stmt->execute([":id" => $id]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$event) {
                throw new RuntimeException("Event not found.");
            }

            $stmt = $conn->prepare(
                "DELETE FROM events WHERE event_id = :id"
            );
            $stmt->execute([":id" => $id]);

            removePoster($event["poster_path"] ?? null, $uploadDir);

            log_audit($conn, "event_delete", "event", (string) $id, "Deleted event #" . $id);

            $message = "Event deleted successfully.";
            $messageType = "success";

        } elseif ($action === "save") {
            $rawId = $_POST["event_id"] ?? "";
            $isEdit = $rawId !== "";
            $id = null;

            if ($isEdit) {
                $id = filter_var($rawId, FILTER_VALIDATE_INT);

                if (!$id || $id < 1) {
                    throw new RuntimeException("Invalid event ID.");
                }
            }

            $name = trim($_POST["event_name"] ?? "");
            $date = trim($_POST["event_date"] ?? "");
            $description = trim($_POST["description"] ?? "");
            $category = trim($_POST["category"] ?? "");
            $status = $_POST["status"] ?? "Open";

            if ($name === "" || strlen($name) > 150) {
                throw new RuntimeException(
                    "Enter an event name up to 150 characters."
                );
            }

            $parsedDate = DateTime::createFromFormat("!Y-m-d", $date);

            if (!$parsedDate || $parsedDate->format("Y-m-d") !== $date) {
                throw new RuntimeException("Please select a valid event date.");
            }

            if (strlen($description) > 5000) {
                throw new RuntimeException("Description is too long.");
            }

            if (strlen($category) > 100) {
                throw new RuntimeException("Category must be under 100 characters.");
            }

            if (!in_array($status, ["Open", "Closed"], true)) {
                throw new RuntimeException("Invalid event status.");
            }

            $oldPoster = null;

            if ($isEdit) {
                $stmt = $conn->prepare(
                    "SELECT poster_path FROM events WHERE event_id = :id"
                );
                $stmt->execute([":id" => $id]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$existing) {
                    throw new RuntimeException("Event not found.");
                }

                $oldPoster = $existing["poster_path"] ?? null;
            }

            $newPoster = savePoster(
                $_FILES["poster"] ?? ["error" => UPLOAD_ERR_NO_FILE],
                $uploadDir,
                $maxSize
            );

            $poster = $newPoster ?? $oldPoster;

            if ($isEdit) {
                $stmt = $conn->prepare(
                    "UPDATE events
                     SET event_name = :name,
                         event_date = :date,
                         description = :description,
                         category = :category,
                         status = :status,
                         poster_path = :poster
                     WHERE event_id = :id"
                );

                $stmt->execute([
                    ":name" => $name,
                    ":date" => $date,
                    ":description" => $description,
                    ":category" => $category,
                    ":status" => $status,
                    ":poster" => $poster,
                    ":id" => $id
                ]);

                if ($newPoster !== null) {
                    removePoster($oldPoster, $uploadDir);
                }

                $message = "Event updated successfully.";

                log_audit($conn, "event_update", "event", (string) $id, "Updated " . $name);

            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO events
                    (event_name, event_date, description,
                     category, status, poster_path)
                    VALUES
                    (:name, :date, :description,
                     :category, :status, :poster)"
                );

                $stmt->execute([
                    ":name" => $name,
                    ":date" => $date,
                    ":description" => $description,
                    ":category" => $category,
                    ":status" => $status,
                    ":poster" => $newPoster
                ]);

                $message = "Event added successfully.";

                log_audit($conn, "event_create", "event", (string) $conn->lastInsertId(), "Added " . $name);
            }

            $messageType = "success";

        } else {
            throw new RuntimeException("Invalid action.");
        }

    } catch (RuntimeException $ex) {
        $message = $ex->getMessage();
        $messageType = "error";

    } catch (PDOException $ex) {
        error_log($ex->getMessage());
        $message = "Database operation failed. Check your events table columns.";
        $messageType = "error";
    }

    $_SESSION["event_flash"] = [
        "message" => $message,
        "type" => $messageType
    ];

    header("Location: events.php");
    exit;
}

if (isset($_SESSION["event_flash"])) {
    $message = $_SESSION["event_flash"]["message"];
    $messageType = $_SESSION["event_flash"]["type"];
    unset($_SESSION["event_flash"]);
}

$editId = filter_input(INPUT_GET, "edit", FILTER_VALIDATE_INT);

if ($editId) {
    $stmt = $conn->prepare(
        "SELECT * FROM events WHERE event_id = :id"
    );
    $stmt->execute([":id" => $editId]);
    $editEvent = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    if (!$editEvent) {
        $message = "Event not found.";
        $messageType = "error";
    }
}

$search = trim($_GET["search"] ?? "");
$statusFilter = $_GET["status"] ?? "";

$sql = "SELECT * FROM events WHERE 1=1";
$params = [];

if ($search !== "") {
    $sql .= " AND (event_name LIKE :search OR category LIKE :search)";
    $params[":search"] = "%" . $search . "%";
}

if (in_array($statusFilter, ["Open", "Closed"], true)) {
    $sql .= " AND status = :status";
    $params[":status"] = $statusFilter;
}

$sql .= " ORDER BY event_date DESC, event_id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->query("SELECT COUNT(*) FROM events");
$totalAll = (int)$stmt->fetchColumn();

$stmt = $conn->query(
    "SELECT COUNT(*) FROM events WHERE status = 'Open'"
);
$totalOpen = (int)$stmt->fetchColumn();

$stmt = $conn->query(
    "SELECT COUNT(*) FROM events WHERE status = 'Closed'"
);
$totalClosed = (int)$stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Manage Events | Student HUB</title>

<link rel="stylesheet" href="../../css/style.css">

<style>
    .stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 20px;
        box-shadow: var(--shadow-card);
    }

    .stat-card span {
        display: block;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--ink-secondary);
        font-weight: 600;
        margin-bottom: 4px;
    }

    .stat-card strong {
        font-size: 1.8rem;
        font-family: var(--font-heading);
        color: var(--accent);
    }

    .panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: var(--shadow-card);
    }

    .panel-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 20px;
        border-bottom: 1px solid var(--border);
        padding-bottom: 12px;
    }

    .panel-heading h2 {
        font-family: var(--font-heading);
        font-size: 1.35rem;
        color: var(--ink);
        margin-bottom: 4px;
    }

    .panel-heading p {
        color: var(--ink-secondary);
        font-size: 0.88rem;
    }

    .mode-badge {
        background: var(--accent-soft);
        color: var(--accent);
        border-radius: var(--radius-sm);
        padding: 4px 10px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .event-form {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px 20px;
    }

    .form-group {
        min-width: 0;
    }

    .form-group.full-width,
    .form-actions {
        grid-column: 1 / -1;
    }

    .form-group label {
        display: block;
        margin-bottom: 6px;
        color: var(--ink);
        font-size: 0.88rem;
        font-weight: 600;
    }

    .event-form input[type="text"],
    .event-form input[type="search"],
    .event-form input[type="date"],
    .event-form input[type="file"],
    .event-form select,
    .event-form textarea,
    .filters input,
    .filters select {
        display: block;
        width: 100%;
        box-sizing: border-box;
        padding: 10px 14px;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        background: var(--surface);
        color: var(--ink);
        font: inherit;
    }

    .event-form input:focus,
    .event-form select:focus,
    .event-form textarea:focus,
    .filters input:focus,
    .filters select:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px var(--accent-soft);
    }

    .event-form textarea {
        min-height: 90px;
        resize: vertical;
    }

    .form-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
    }

    .btn-action,
    .button-link {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        border: 0;
        border-radius: var(--radius-sm);
        padding: 10px 18px;
        font-family: inherit;
        font-size: 0.88rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all var(--duration-ui) var(--ease-ui);
    }

    button[type="submit"] {
        background: var(--accent);
        color: white;
    }

    button[type="submit"]:hover {
        background: var(--accent-hover);
    }

    button[type="reset"],
    .button-link {
        background: var(--surface-muted);
        color: var(--ink);
        border: 1px solid var(--border);
    }

    .button-link:hover {
        border-color: var(--ink-secondary);
    }

    .filters {
        display: grid;
        grid-template-columns: minmax(180px, 1fr) minmax(140px, 190px) auto auto;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .table-container {
        width: 100%;
        overflow-x: auto;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }

    th, td {
        padding: 12px 14px;
        text-align: left;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }

    th {
        background: var(--surface-muted);
        color: var(--ink-secondary);
        font-weight: 600;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .poster {
        display: block;
        width: 60px;
        height: 48px;
        object-fit: cover;
        border-radius: var(--radius-sm);
        border: 1px solid var(--border);
    }

    .no-poster {
        display: inline-block;
        padding: 8px;
        border: 1px dashed var(--border);
        border-radius: var(--radius-sm);
        color: var(--ink-muted);
        background: var(--surface-muted);
        font-size: 0.75rem;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .status-open {
        background: var(--accent-soft);
        color: var(--success);
    }

    .status-closed {
        background: rgba(168, 67, 59, 0.1);
        color: var(--danger);
    }

    .actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
    }

    .actions .button-link,
    .actions button {
        padding: 4px 10px;
        font-size: 0.8rem;
    }

    .notice {
        margin-bottom: 20px;
        padding: 12px 16px;
        border-radius: var(--radius-sm);
        font-weight: 500;
        font-size: 0.9rem;
    }

    .notice.success {
        background: var(--accent-soft);
        color: var(--success);
        border: 1px solid var(--border);
    }

    .notice.error {
        background: rgba(168, 67, 59, 0.1);
        color: var(--danger);
        border: 1px solid rgba(168, 67, 59, 0.25);
    }

    @media (max-width: 900px) {
        .event-form {
            grid-template-columns: 1fr;
        }
        .filters {
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
                <span>Event Management &bull; Campus Rosters</span>
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
                <div class="kicker">Admin Panel</div>
                <h1>Campus Event Management</h1>
                <p>Create, update and manage university event rosters, symposiums, and registrations.</p>
            </div>

    <?php if ($message !== ""): ?>
        <div class="notice <?= e($messageType) ?>" role="status">
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <section class="stats">
        <article class="stat-card">
            <span>Total Events</span>
            <strong><?= $totalAll ?></strong>
        </article>

        <article class="stat-card">
            <span>Open Events</span>
            <strong><?= $totalOpen ?></strong>
        </article>

        <article class="stat-card">
            <span>Closed Events</span>
            <strong><?= $totalClosed ?></strong>
        </article>
    </section>

    <section class="panel" id="event-form">
        <div class="panel-heading">
            <div>
                <h2><?= $editEvent ? "Update Event" : "Add Event" ?></h2>
                <p>
                    <?= $editEvent
                        ? "Update the event information below."
                        : "Fill in the details to create a campus event." ?>
                </p>
            </div>

            <?php if ($editEvent): ?>
                <span class="mode-badge">EDIT MODE</span>
            <?php endif; ?>
        </div>

        <form class="event-form"
              method="POST"
              action="events.php"
              enctype="multipart/form-data">

            <input type="hidden" name="csrf_token"
                   value="<?= e($_SESSION["event_csrf"]) ?>">
            <input type="hidden" name="action" value="save">

            <?php if ($editEvent): ?>
                <input type="hidden" name="event_id"
                       value="<?= (int)$editEvent["event_id"] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="event_name">Event Name *</label>
                <input type="text" id="event_name" name="event_name"
                       maxlength="150" required
                       placeholder="Enter event name"
                       value="<?= e($editEvent["event_name"] ?? "") ?>">
            </div>

            <div class="form-group">
                <label for="event_date">Event Date *</label>
                <input type="date" id="event_date" name="event_date"
                       required
                       value="<?= e($editEvent["event_date"] ?? "") ?>">
            </div>

            <div class="form-group">
                <label for="category">Event Category</label>
                <input type="text" id="category" name="category"
                       maxlength="100"
                       placeholder="Technical, Cultural, Sports..."
                       value="<?= e($editEvent["category"] ?? "") ?>">
            </div>

            <div class="form-group">
                <label for="status">Event Status</label>
                <select id="status" name="status">
                    <option value="Open"
                        <?= ($editEvent["status"] ?? "Open") === "Open"
                            ? "selected" : "" ?>>Open</option>
                    <option value="Closed"
                        <?= ($editEvent["status"] ?? "") === "Closed"
                            ? "selected" : "" ?>>Closed</option>
                </select>
            </div>

            <div class="form-group full-width">
                <label for="description">Event Description</label>
                <textarea id="description" name="description"
                          maxlength="5000" rows="4"
                          placeholder="Describe the event, venue and activities..."><?= e($editEvent["description"] ?? "") ?></textarea>
            </div>

            <div class="form-group full-width">
                <label for="poster">Event Poster</label>
                <input type="file" id="poster" name="poster"
                       accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                <small>Allowed formats: JPG, PNG and WebP. Maximum size: 2 MB.</small>

                <?php if (!empty($editEvent["poster_path"])): ?>
                    <div class="current-poster">
                        <img
                            src="<?= e($uploadUrl . rawurlencode(basename($editEvent["poster_path"]))) ?>"
                            alt="Current poster">
                        <span>Current poster. Upload a new image to replace it.</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit">
                    <?= $editEvent ? "Update Event" : "Add Event" ?>
                </button>

                <?php if ($editEvent): ?>
                    <a class="button-link" href="events.php">Cancel</a>
                <?php else: ?>
                    <button type="reset">Reset</button>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="panel" id="event-list">
        <div class="panel-heading">
            <div>
                <h2>All Events</h2>
                <p>View, search, edit and delete existing events.</p>
            </div>

            <span class="mode-badge"><?= count($events) ?> RECORDS</span>
        </div>

        <form class="filters" method="GET" action="events.php">
            <input type="search" name="search"
                   placeholder="Search event or category..."
                   aria-label="Search event or category"
                   value="<?= e($search) ?>">

            <select name="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                <option value="Open"
                    <?= $statusFilter === "Open" ? "selected" : "" ?>>
                    Open events
                </option>
                <option value="Closed"
                    <?= $statusFilter === "Closed" ? "selected" : "" ?>>
                    Closed events
                </option>
            </select>

            <button type="submit">Search / Filter</button>
            <a class="button-link" href="events.php#event-list">Clear</a>
        </form>

        <p class="results-label">
            Showing <?= count($events) ?> matching events
        </p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Poster</th>
                        <th>Event Details</th>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (empty($events)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                No events found. Try another search or add an event.
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?= (int)$event["event_id"] ?></td>

                            <td>
                                <?php if (!empty($event["poster_path"])): ?>
                                    <img class="poster"
                                         src="<?= e($uploadUrl . rawurlencode(basename($event["poster_path"]))) ?>"
                                         alt="Event poster">
                                <?php else: ?>
                                    <span class="no-poster">No poster</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="event-name">
                                    <?= e($event["event_name"]) ?>
                                </span>
                            </td>

                            <td><?= e($event["event_date"]) ?></td>

                            <td>
                                <?= e($event["category"] ?? "") !== ""
                                    ? e($event["category"])
                                    : "—" ?>
                            </td>

                            <td>
                                <?php $closed = ($event["status"] ?? "Open") === "Closed"; ?>
                                <span class="status-badge <?= $closed ? "status-closed" : "status-open" ?>">
                                    <?= e($event["status"] ?? "Open") ?>
                                </span>
                            </td>

                            <td>
                                <?= e($event["description"] ?? "") !== ""
                                    ? e($event["description"])
                                    : "—" ?>
                            </td>

                            <td>
                                <div class="actions">
                                    <a class="button-link"
                                       href="events.php?edit=<?= (int)$event["event_id"] ?>#event-form">
                                        Edit
                                    </a>

                                    <form method="POST" action="events.php"
                                          onsubmit="return confirm('Are you sure you want to delete this event?');">
                                        <input type="hidden" name="csrf_token"
                                               value="<?= e($_SESSION["event_csrf"]) ?>">
                                        <input type="hidden" name="action"
                                               value="delete">
                                        <input type="hidden" name="event_id"
                                               value="<?= (int)$event["event_id"] ?>">
                                        <button type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

        </div>
    </main>
</div>

<footer>
    <p>&copy; <?= date("Y") ?> Student HUB Portal</p>
</footer>

<script src="../../js/index.js"></script>
</body>
</html>
