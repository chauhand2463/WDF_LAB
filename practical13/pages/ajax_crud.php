<?php
require_once __DIR__ . "/session_check.php";

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

    <title>AJAX CRUD | StudentHub</title>

    <link rel="stylesheet" href="../css/style.css">

    <style>
        .ajax-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }

        .ajax-card {
            background: #fff;
            color: #222;
            padding: 22px;
            margin-bottom: 24px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,.10);
        }

        .ajax-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
        }

        .ajax-form label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .ajax-form input,
        .ajax-form select,
        .ajax-form textarea,
        .ajax-toolbar input,
        .ajax-toolbar select {
            box-sizing: border-box;
            width: 100%;
            padding: 10px;
            border: 1px solid #bbb;
            border-radius: 6px;
            font: inherit;
        }

        .ajax-form textarea {
            min-height: 80px;
            resize: vertical;
        }

        .ajax-toolbar {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 12px;
            align-items: end;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            margin: 3px 0;
        }

        .primary-btn {
            background: #2563eb;
            color: white;
        }

        .edit-btn {
            background: #eab308;
            color: #111;
        }

        .delete-btn {
            background: #dc2626;
            color: white;
        }

        .cancel-btn {
            background: #e5e7eb;
            color: #111;
        }

        .btn:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        .ajax-status {
            padding: 12px;
            border-radius: 7px;
            margin: 15px 0;
        }

        .ajax-status.success {
            background: #d1fae5;
            color: #065f46;
        }

        .ajax-status.error {
            background: #fee2e2;
            color: #991b1b;
        }

        .ajax-status.info {
            background: #dbeafe;
            color: #1e40af;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .ajax-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
        }

        .ajax-table th,
        .ajax-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .ajax-table th {
            background: #f3f4f6;
            color: #111;
        }

        .empty-row {
            text-align: center;
            color: #6b7280;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-open {
            background: #d1fae5;
            color: #065f46;
        }

        .status-closed {
            background: #fee2e2;
            color: #991b1b;
        }

        .ajax-pagination {
            margin-top: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-info {
            font-size: 14px;
            color: #374151;
        }

        .section-head {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        @media (max-width: 700px) {
            .ajax-container {
                padding: 10px;
            }

            .ajax-card {
                padding: 14px;
            }

            .ajax-toolbar {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<header class="header">
    <div class="logo">
        <img src="../images/charusat.png" alt="CHARUSAT Logo">
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
            <li><a href="adminpages/manage_students.php">Manage Students</a></li>
            <li><a href="adminpages/events.php">Manage Events</a></li>
            <li><a href="ajax_crud.php">AJAX CRUD</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<main class="ajax-container">

    <h1>AJAX CRUD Dashboard</h1>
    <p>
        Manage students and events without page reloads. The browser talks to the
        JSON API (<code>../api/</code>) using <code>fetch()</code>.
    </p>

    <div id="ajaxStatus" class="ajax-status" role="status" hidden></div>

    <section class="ajax-card">
        <div class="section-head">
            <h2 id="studentFormHeading">Add New Student</h2>
            <span>Live search, sorting sync and pagination</span>
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
                           required>
                </div>

                <div>
                    <label for="student_email">Email *</label>
                    <input type="email"
                           id="student_email"
                           maxlength="255"
                           required>
                </div>

                <div>
                    <label for="student_mobile">Mobile *</label>
                    <input type="text"
                           id="student_mobile"
                           maxlength="10"
                           inputmode="numeric"
                           required>
                </div>

                <div>
                    <label for="student_course">Course *</label>
                    <input type="text"
                           id="student_course"
                           maxlength="100"
                           required>
                </div>

                <div>
                    <label for="student_password">Password *</label>
                    <input type="password"
                           id="student_password"
                           minlength="8"
                           autocomplete="new-password">
                </div>
            </div>

            <p>
                <button type="submit" id="studentSubmitBtn" class="btn primary-btn">
                    Add Student
                </button>
                <button type="button" id="studentCancelBtn" class="btn cancel-btn" hidden>
                    Cancel Edit
                </button>
            </p>
        </form>
    </section>

    <section class="ajax-card">
        <h2>Student Records</h2>

        <div class="ajax-toolbar">
            <div>
                <label for="studentSearch">Search</label>
                <input type="search"
                       id="studentSearch"
                       placeholder="Student ID, name, email or mobile">
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

    <section class="ajax-card">
        <div class="section-head">
            <h2 id="eventFormHeading">Add New Event</h2>
            <span>Event CRUD over REST-style endpoints</span>
        </div>

        <form id="eventForm" data-mode="add" novalidate>
            <input type="hidden" id="event_id">

            <div class="ajax-form">
                <div>
                    <label for="event_name">Event Name *</label>
                    <input type="text"
                           id="event_name"
                           maxlength="150"
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
                           placeholder="Technical / Cultural">
                </div>

                <div>
                    <label for="event_status">Status</label>
                    <select id="event_status">
                        <option value="Open">Open</option>
                        <option value="Closed">Closed</option>
                    </select>
                </div>
            </div>

            <div style="margin-top:15px;">
                <label for="event_description">Description</label>
                <textarea id="event_description" maxlength="5000"></textarea>
            </div>

            <p>
                <button type="submit" id="eventSubmitBtn" class="btn primary-btn">
                    Add Event
                </button>
                <button type="button" id="eventCancelBtn" class="btn cancel-btn" hidden>
                    Cancel Edit
                </button>
            </p>
        </form>
    </section>

    <section class="ajax-card">
        <h2>Event Records</h2>

        <div class="ajax-toolbar">
            <div>
                <label for="eventSearch">Search</label>
                <input type="search"
                       id="eventSearch"
                       placeholder="Event name or category">
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

</main>

<footer>
    <p style="text-align:center;">&copy; 2026 StudentHub Portal</p>
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
