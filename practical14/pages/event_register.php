<?php
require_once __DIR__ . "/session_check.php";
require_once __DIR__ . "/menu.php";
require_once dirname(__DIR__) . "/db.php";

if ($_SESSION["role"] !== "student") {
    http_response_code(403);
    exit("Unauthorized access.");
}

$currentStudentId = $_SESSION["student_id"] ?? "";
$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id = trim($_POST["student_id"] ?? $currentStudentId);
    $event_id = trim($_POST["event_id"] ?? "");

    if ($student_id === "") {
        $message = "Student ID is required.";
        $messageType = "error";
    } elseif ($event_id === "") {
        $message = "Please select an event to register.";
        $messageType = "error";
    } else {
        try {
            $student = $conn->prepare("SELECT student_id FROM students WHERE student_id = :student_id");
            $student->execute([":student_id" => $student_id]);

            if (!$student->fetch()) {
                $message = "Student ID not found in records.";
                $messageType = "error";
            } else {
                $check = $conn->prepare(
                    "SELECT registration_id FROM registrations WHERE student_id = :student_id AND event_id = :event_id"
                );
                $check->execute([
                    ":student_id" => $student_id,
                    ":event_id" => $event_id
                ]);

                if ($check->fetch()) {
                    $message = "You are already registered for this event.";
                    $messageType = "error";
                } else {
                    $sql = "INSERT INTO registrations (student_id, event_id, registered_at) VALUES (:student_id, :event_id, NOW())";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        ":student_id" => $student_id,
                        ":event_id" => $event_id
                    ]);

                    $message = "Registration successful! We have confirmed your seat.";
                    $messageType = "success";
                }
            }
        } catch (PDOException $e) {
            $message = "Database error while registering for event.";
            $messageType = "error";
        }
    }
}

// Fetch available upcoming events
$availableEvents = [];
try {
    $eventsStmt = $conn->query(
        "SELECT event_id, event_name, event_date, description, status, location
         FROM events
         WHERE status = 'Open' OR status IS NULL
         ORDER BY event_date ASC"
    );
    if ($eventsStmt) {
        $availableEvents = $eventsStmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $availableEvents = [];
}

// Fetch current registrations for this student
$myRegistrations = [];
try {
    $myRegStmt = $conn->prepare(
        "SELECT r.registration_id, r.registered_at, e.event_name, e.event_date, e.location, e.description
         FROM registrations r
         JOIN events e ON e.event_id = r.event_id
         WHERE r.student_id = :student_id
         ORDER BY e.event_date ASC"
    );
    $myRegStmt->execute([":student_id" => $currentStudentId]);
    $myRegistrations = $myRegStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $myRegistrations = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Event Registration | Student HUB Portal</title>

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
                <span>Campus Events &bull; Registration Open</span>
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
                    <div class="kicker">Campus Life</div>
                    <h1>Register for Events</h1>
                    <p>Participate in technical symposiums, sports leagues, cultural fests, and workshops.</p>
                </div>

                <?php if ($message !== ""): ?>
                    <div class="form-message form-message--<?= $messageType ?> reveal" style="margin-bottom:24px; max-width:800px;">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <div class="content-grid" style="grid-template-columns: minmax(320px, 1fr) minmax(360px, 1.4fr); gap:24px; align-items:start;">

                    <!-- Registration Form Panel -->
                    <div class="panel reveal" style="animation-delay:0.15s;">
                        <h2>Event Enrollment</h2>
                        <form method="POST" action="event_register.php" style="display:flex; flex-direction:column; gap:16px; margin-top:16px;">

                            <div class="form-group">
                                <label for="student_id" style="display:block; margin-bottom:6px; font-weight:600;">Student ID</label>
                                <input
                                    type="text"
                                    id="student_id"
                                    name="student_id"
                                    value="<?= htmlspecialchars($currentStudentId) ?>"
                                    readonly
                                    style="width:100%; padding:10px 14px; border:1px solid var(--border); border-radius:var(--radius-sm); background:var(--surface-muted); color:var(--ink); font-family:var(--font-mono); font-weight:600;">
                                <span style="font-size:0.8rem; color:var(--ink-secondary); margin-top:4px; display:block;">Verified logged-in student account</span>
                            </div>

                            <div class="form-group">
                                <label for="event_id" style="display:block; margin-bottom:6px; font-weight:600;">Select Upcoming Event</label>
                                <select
                                    id="event_id"
                                    name="event_id"
                                    required
                                    style="width:100%; padding:10px 14px; border:1px solid var(--border); border-radius:var(--radius-sm); background:var(--surface); color:var(--ink); font-family:inherit;">
                                    <option value="">-- Choose an Event --</option>
                                    <?php foreach ($availableEvents as $ev): ?>
                                        <option value="<?= htmlspecialchars($ev["event_id"]) ?>">
                                            <?= htmlspecialchars($ev["event_name"]) ?> &bull; <?= htmlspecialchars($ev["event_date"]) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="btn-group" style="margin-top:8px;">
                                <button type="submit" class="btn btn--accent">Register Seat</button>
                                <button type="reset" class="btn btn--ghost">Reset</button>
                            </div>
                        </form>
                    </div>

                    <!-- My Registered Events Panel -->
                    <div class="panel reveal" style="animation-delay:0.2s;">
                        <h2>My Event Registrations</h2>
                        <p style="color:var(--ink-secondary); font-size:0.88rem; margin-bottom:16px;">Events you have confirmed attendance for this academic year.</p>

                        <?php if (empty($myRegistrations)): ?>
                            <div style="padding:28px 20px; text-align:center; background:var(--surface-muted); border-radius:var(--radius-sm); color:var(--ink-secondary);">
                                <p style="font-weight:600;">No event registrations yet.</p>
                                <p style="font-size:0.88rem; margin-top:4px;">Select an event on the left to secure your participation certificate and seat.</p>
                            </div>
                        <?php else: ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Event Name</th>
                                        <th>Date</th>
                                        <th class="numeric">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($myRegistrations as $reg): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($reg["event_name"]) ?></strong>
                                                <?php if (!empty($reg["location"])): ?>
                                                    <div style="font-size:0.8rem; color:var(--ink-secondary);"><?= htmlspecialchars($reg["location"]) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($reg["event_date"]) ?></td>
                                            <td class="numeric"><span class="status status--success">Registered</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
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
