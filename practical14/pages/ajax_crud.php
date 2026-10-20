<?php
require_once __DIR__ . "/session_check.php";
require_once __DIR__ . "/menu.php";

if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("Unauthorized access. Admin only.");
}

require_once __DIR__ . "/../db.php";

if (empty($_SESSION["api_csrf"])) {
    $_SESSION["api_csrf"] = bin2hex(random_bytes(32));
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AJAX CRUD Console | Student HUB</title>

    <link rel="stylesheet" href="../css/style.css">

    <style>
        .ajax-card {
            background: var(--surface);
            color: var(--ink);
            padding: 24px;
            margin-bottom: 24px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-card);
        }

        .ajax-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }

        .ajax-form label,
        .ajax-toolbar label {
            display: block;
            font-weight: 600;
            font-size: 0.88rem;
            margin-bottom: 6px;
            color: var(--ink);
        }

        .ajax-form input,
        .ajax-form select,
        .ajax-form textarea,
        .ajax-toolbar input,
        .ajax-toolbar select {
            box-sizing: border-box;
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--surface);
            color: var(--ink);
            font: inherit;
        }

        .ajax-form input:focus,
        .ajax-form select:focus,
        .ajax-form textarea:focus,
        .ajax-toolbar input:focus,
        .ajax-toolbar select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-soft);
        }

        .ajax-form textarea {
            min-height: 80px;
            resize: vertical;
        }

        .ajax-toolbar {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 16px;
            align-items: end;
            margin-bottom: 16px;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 14px;
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
            color: #fff;
        }

        .primary-btn:hover {
            background: var(--accent-hover);
        }

        .edit-btn {
            background: var(--surface-muted);
            color: var(--ink);
            border: 1px solid var(--border);
            padding: 4px 10px;
            font-size: 0.8rem;
        }

        .edit-btn:hover {
            border-color: var(--ink-secondary);
        }

        .delete-btn {
            background: rgba(168, 67, 59, 0.1);
            color: var(--danger);
            border: 1px solid rgba(168, 67, 59, 0.3);
            padding: 4px 10px;
            font-size: 0.8rem;
        }

        .delete-btn:hover {
            background: var(--danger);
            color: #fff;
        }

        .cancel-btn {
            background: var(--surface-muted);
            color: var(--ink-secondary);
            border: 1px solid var(--border);
        }

        .ajax-status {
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .ajax-status.success {
            background: var(--accent-soft);
            color: var(--success);
            border: 1px solid var(--border);
        }

        .ajax-status.error {
            background: rgba(168, 67, 59, 0.1);
            color: var(--danger);
            border: 1px solid rgba(168, 67, 59, 0.25);
        }

        .ajax-status.info {
            background: var(--surface-muted);
            color: var(--ink);
            border: 1px solid var(--border);
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
        }

        .ajax-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        .ajax-table th,
        .ajax-table td {
            padding: 12px 14px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .ajax-table th {
            background: var(--surface-muted);
            color: var(--ink-secondary);
            font-weight: 600;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .ajax-table tr:last-child td {
            border-bottom: none;
        }

        .empty-row {
            text-align: center;
            color: var(--ink-muted);
            padding: 24px 0;
            font-size: 0.9rem;
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

        .ajax-pagination {
            margin-top: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ajax-pagination button {
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--ink);
            font-size: 0.85rem;
        }

        .ajax-pagination button:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .page-info {
            font-size: 0.88rem;
            color: var(--ink-secondary);
        }

        .section-head {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 12px;
        }

        .section-head h2 {
            font-family: var(--font-heading);
            font-size: 1.3rem;
            color: var(--ink);
            margin: 0;
        }

        .section-head span {
            font-size: 0.82rem;
            color: var(--ink-secondary);
        }
    </style>
</head>

<body>

<!-- Topbar -->
<header class="topbar">
    <div class="topbar-left">
        <button class="btn btn--ghost mobile-menu-btn" id="openSidebar" aria-label="Open menu" style="padding:8px 10px;">&#9776;</button>
        <div class="logo">
            <img src="../images/charusat.png" alt="CHARUSAT Logo">
            <span class="logo-wordmark">Student <strong>HUB</strong></span>
        </div>
    </div>
    <div class="topbar-center">
        <div class="page-title-wrap reveal" style="animation-delay:0.05s">
            <span class="kicker">Administration</span>
            <h1>AJAX CRUD Console</h1>
            <p style="margin-top:6px;color:var(--ink-secondary);font-size:0.9rem;">
                Asynchronous student and event management via REST APIs
            </p>
        </div>
    </div>
    <div class="topbar-right">
        <button id="themeBtn" class="btn btn--ghost" style="padding:8px 14px;">
            &#9788; Light Mode
        </button>
        <a href="logout.php" class="btn btn--ghost">Sign out</a>
    </div>
</header>

<div class="app-shell reveal">

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-inner">
            <div class="sidebar-brand">
                <img src="../images/charusat.png" alt="CHARUSAT Logo">
                <span>Student <strong>HUB</strong></span>
            </div>
            <nav class="sidebar-nav">
                <ul class="nav-list">
                    <?php foreach (build_dynamic_menu("admin") as $item): ?>
                        <li><a href="<?= e($item["href"]) ?>" <?= $item["label"] === "AJAX CRUD" ? 'aria-current="page"' : "" ?>><?= e($item["label"]) ?></a></li>
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

    <!-- Main -->
    <main class="main reveal" style="animation-delay:0.1s">
        <div class="main-inner">

            <div class="page-header">
                <div class="kicker">REST Console</div>
                <h1>AJAX Data Management</h1>
                <p>Live management of student records and event schedules without page reloads.</p>
            </div>

            <div id="ajaxStatus" class="ajax-status" role="status" hidden></div>

            <!-- STUDENT FORM CARD -->
            <section class="ajax-card reveal" style="animation-delay:0.15s;">
                <div class="section-head">
                    <h2 id="studentFormHeading">Add New Student</h2>
                    <span>Live client-server sync with JSON APIs</span>
                </div>

                <form id="studentForm" data-mode="add" novalidate>
                    <div class="ajax-form">
                        <div>
                            <label for="student_id">Student ID *</label>
                            <input type="text"
                                   id="student_id"
                                   name="student_id"
                                   maxlength="8"
                                   placeholder="25CE015"
                                   required>
                        </div>

                        <div>
                            <label for="student_full_name">Full Name *</label>
                            <input type="text"
                                   id="student_full_name"
                                   maxlength="150"
                                   placeholder="Full Name"
                                   required>
                        </div>

                        <div>
                            <label for="student_email">Email *</label>
                            <input type="email"
                                   id="student_email"
                                   maxlength="255"
                                   placeholder="name@charusat.edu.in"
                                   required>
                        </div>

                        <div>
                            <label for="student_mobile">Mobile *</label>
                            <input type="text"
                                   id="student_mobile"
                                   maxlength="10"
                                   placeholder="10-digit number"
                                   inputmode="numeric"
                                   required>
                        </div>

                        <div>
                            <label for="student_course">Course *</label>
                            <input type="text"
                                   id="student_course"
                                   maxlength="100"
                                   placeholder="Computer Engineering"
                                   required>
                        </div>

                        <div>
                            <label for="student_password">Password *</label>
                            <input type="password"
                                   id="student_password"
                                   minlength="8"
                                   placeholder="At least 8 characters"
                                   autocomplete="new-password">
                        </div>
                    </div>

                    <div style="margin-top:16px; display:flex; gap:10px;">
                        <button type="submit" id="studentSubmitBtn" class="btn-action primary-btn">
                            Add Student
                        </button>
                        <button type="button" id="studentCancelBtn" class="btn-action cancel-btn" hidden>
                            Cancel Edit
                        </button>
                    </div>
                </form>
            </section>

            <!-- STUDENT RECORDS TABLE -->
            <section class="ajax-card reveal" style="animation-delay:0.2s;">
                <div class="section-head">
                    <h2>Student Records</h2>
                    <span>Real-time filter and live query</span>
                </div>

                <div class="ajax-toolbar">
                    <div>
                        <label for="studentSearch">Search Students</label>
                        <input type="search"
                               id="studentSearch"
                               placeholder="Search by ID, name, email or mobile...">
                    </div>

                    <div>
                        <label for="studentCourseFilter">Filter by Course</label>
                        <select id="studentCourseFilter">
                            <option value="">All Courses</option>
                        </select>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table class="ajax-table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Course</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody id="studentTableBody"></tbody>
                    </table>
                </div>

                <p id="studentTableStatus" class="empty-row" hidden></p>
                <div id="studentPagination" class="ajax-pagination"></div>
            </section>

            <!-- EVENT FORM CARD -->
            <section class="ajax-card reveal" style="animation-delay:0.25s;">
                <div class="section-head">
                    <h2 id="eventFormHeading">Add New Event</h2>
                    <span>REST-style event scheduling</span>
                </div>

                <form id="eventForm" data-mode="add" novalidate>
                    <input type="hidden" id="event_id">

                    <div class="ajax-form">
                        <div>
                            <label for="event_name">Event Name *</label>
                            <input type="text"
                                   id="event_name"
                                   maxlength="150"
                                   placeholder="Event Title"
                                   required>
                        </div>

                        <div>
                            <label for="event_date">Event Date *</label>
                            <input type="date" id="event_date" required>
                        </div>

                        <div>
                            <label for="event_category">Category</label>
                            <input type="text"
                                   id="event_category"
                                   maxlength="100"
                                   placeholder="Technical / Cultural / Sports">
                        </div>

                        <div>
                            <label for="event_status">Status</label>
                            <select id="event_status">
                                <option value="Open">Open</option>
                                <option value="Closed">Closed</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-top:16px;">
                        <label for="event_description">Event Description</label>
                        <textarea id="event_description" maxlength="5000" placeholder="Provide complete event details..."></textarea>
                    </div>

                    <div style="margin-top:16px; display:flex; gap:10px;">
                        <button type="submit" id="eventSubmitBtn" class="btn-action primary-btn">
                            Add Event
                        </button>
                        <button type="button" id="eventCancelBtn" class="btn-action cancel-btn" hidden>
                            Cancel Edit
                        </button>
                    </div>
                </form>
            </section>

            <!-- EVENT RECORDS TABLE -->
            <section class="ajax-card reveal" style="animation-delay:0.3s;">
                <div class="section-head">
                    <h2>Event Records</h2>
                    <span>Manage all scheduled university events</span>
                </div>

                <div class="ajax-toolbar">
                    <div>
                        <label for="eventSearch">Search Events</label>
                        <input type="search"
                               id="eventSearch"
                               placeholder="Search by event name or category...">
                    </div>

                    <div>
                        <label for="eventStatusFilter">Filter by Status</label>
                        <select id="eventStatusFilter">
                            <option value="">All Statuses</option>
                            <option value="Open">Open</option>
                            <option value="Closed">Closed</option>
                        </select>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table class="ajax-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Event Name</th>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody id="eventTableBody"></tbody>
                    </table>
                </div>

                <p id="eventTableStatus" class="empty-row" hidden></p>
            </section>

        </div>
    </main>

</div>

<footer>
    <p>&copy; 2026 CHARUSAT University &bull; Student HUB Portal</p>
</footer>

<script>
    window.STUDENTHUB_API = {
        studentsUrl: "../api/students.php",
        eventsUrl: "../api/events.php",
        csrf: "<?= e($_SESSION["api_csrf"]) ?>"
    };
</script>

<script src="../js/index.js"></script>
<script src="../js/api.js"></script>

</body>
</html>
