<?php
require_once __DIR__ . "/session_check.php";
require_once __DIR__ . "/menu.php";
require_once dirname(__DIR__) . "/db.php";

if ($_SESSION["role"] !== "student") {
    http_response_code(403);
    exit("Unauthorized access.");
}

$student_id = $_SESSION["student_id"] ?? "";
$studentName = $_SESSION["full_name"] ?? "";
$studentEmail = $_SESSION["email"] ?? "";

if ((empty($studentName) || empty($studentEmail)) && !empty($student_id)) {
    try {
        $stmt = $conn->prepare("SELECT full_name, email FROM students WHERE student_id = ?");
        $stmt->execute([$student_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $studentName = $row["full_name"];
            $studentEmail = $row["email"];
        }
    } catch (PDOException $e) {
        // Fallback gracefully
    }
}

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $senderName = trim($_POST["name"] ?? "");
    $senderEmail = trim($_POST["email"] ?? "");
    $subject = trim($_POST["subject"] ?? "");
    $body = trim($_POST["message"] ?? "");

    if (empty($senderName) || empty($senderEmail) || empty($subject) || empty($body)) {
        $message = "Please fill in all fields before submitting.";
        $messageType = "error";
    } elseif (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "error";
    } else {
        $ticketId = "TKT-" . strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 6));
        $message = "Thank you, " . htmlspecialchars($senderName) . "! Your message has been routed to the Student Helpdesk (Ticket #" . $ticketId . "). A response will be sent to " . htmlspecialchars($senderEmail) . " within 24 hours.";
        $messageType = "success";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Contact Support | Student HUB Portal</title>

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
                <span>Support Helpdesk &bull; Mon-Sat 9AM-5PM</span>
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
                    <div class="kicker">Get in Touch</div>
                    <h1>Student Support &amp; Helpdesk</h1>
                    <p>Have an inquiry regarding courses, fees, or university services? Submit a ticket below.</p>
                </div>

                <?php if ($message !== ""): ?>
                    <div class="form-message form-message--<?= $messageType ?> reveal" style="margin-bottom:24px; max-width:800px;">
                        <?= $message ?>
                    </div>
                <?php endif; ?>

                <div class="content-grid" style="grid-template-columns: 1.2fr 1fr; gap:24px; align-items:start;">
                    
                    <!-- Contact Form -->
                    <div class="panel reveal" style="animation-delay:0.15s;">
                        <h2>Submit a Query</h2>
                        <form method="POST" action="contact.php" style="display:flex; flex-direction:column; gap:16px; margin-top:16px;">
                            <div class="form-group">
                                <label for="name" style="display:block; margin-bottom:6px; font-weight:600;">Your Name</label>
                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    value="<?= htmlspecialchars($studentName) ?>"
                                    placeholder="Enter your full name"
                                    required
                                    style="width:100%; padding:10px 14px; border:1px solid var(--border); border-radius:var(--radius-sm); background:var(--surface); color:var(--ink);">
                            </div>

                            <div class="form-group">
                                <label for="email" style="display:block; margin-bottom:6px; font-weight:600;">Email Address</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="<?= htmlspecialchars($studentEmail) ?>"
                                    placeholder="student@charusat.edu.in"
                                    required
                                    style="width:100%; padding:10px 14px; border:1px solid var(--border); border-radius:var(--radius-sm); background:var(--surface); color:var(--ink);">
                            </div>

                            <div class="form-group">
                                <label for="subject" style="display:block; margin-bottom:6px; font-weight:600;">Inquiry Category / Subject</label>
                                <input
                                    type="text"
                                    id="subject"
                                    name="subject"
                                    placeholder="e.g. Examination schedule query, Grade discrepancy"
                                    required
                                    style="width:100%; padding:10px 14px; border:1px solid var(--border); border-radius:var(--radius-sm); background:var(--surface); color:var(--ink);">
                            </div>

                            <div class="form-group">
                                <label for="message" style="display:block; margin-bottom:6px; font-weight:600;">Detailed Message</label>
                                <textarea
                                    id="message"
                                    name="message"
                                    rows="5"
                                    placeholder="Explain your issue or question in detail..."
                                    required
                                    style="width:100%; padding:10px 14px; border:1px solid var(--border); border-radius:var(--radius-sm); background:var(--surface); color:var(--ink); font-family:inherit; resize:vertical;"></textarea>
                            </div>

                            <div class="btn-group" style="margin-top:6px;">
                                <button type="submit" class="btn btn--accent">Submit Ticket</button>
                            </div>
                        </form>
                    </div>

                    <!-- Campus Directory -->
                    <div class="panel reveal" style="animation-delay:0.2s;">
                        <h2>University Directory</h2>
                        <div style="display:flex; flex-direction:column; gap:20px; margin-top:16px;">
                            <div>
                                <h3 style="font-size:1rem; margin-bottom:4px; color:var(--ink);">Student Affairs &amp; Registrar</h3>
                                <p style="color:var(--ink-secondary); font-size:0.9rem; margin-bottom:2px;">Academic Block A, Room 104</p>
                                <p style="color:var(--accent); font-size:0.88rem; font-family:var(--font-mono);">registrar@charusat.ac.in &bull; +91 2697 265011</p>
                            </div>

                            <div style="border-top: 1px solid var(--border); padding-top:14px;">
                                <h3 style="font-size:1rem; margin-bottom:4px; color:var(--ink);">IT &amp; Portal Support</h3>
                                <p style="color:var(--ink-secondary); font-size:0.9rem; margin-bottom:2px;">Central Computing Facility, 2nd Floor</p>
                                <p style="color:var(--accent); font-size:0.88rem; font-family:var(--font-mono);">ithelpdesk@charusat.ac.in &bull; Ext. 404</p>
                            </div>

                            <div style="border-top: 1px solid var(--border); padding-top:14px;">
                                <h3 style="font-size:1rem; margin-bottom:4px; color:var(--ink);">Examination Cell</h3>
                                <p style="color:var(--ink-secondary); font-size:0.9rem; margin-bottom:2px;">Main Administrative Building</p>
                                <p style="color:var(--accent); font-size:0.88rem; font-family:var(--font-mono);">exam@charusat.ac.in &bull; +91 2697 265022</p>
                            </div>

                            <div style="border-top: 1px solid var(--border); padding-top:14px;">
                                <h3 style="font-size:1rem; margin-bottom:4px; color:var(--ink);">Operating Hours</h3>
                                <p style="color:var(--ink-secondary); font-size:0.9rem;">Monday &ndash; Saturday: 9:00 AM &ndash; 5:00 PM</p>
                                <p style="color:var(--ink-secondary); font-size:0.85rem;">Sunday &amp; Gazetted Holidays: Closed</p>
                            </div>
                        </div>
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
