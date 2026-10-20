
<?php
require_once __DIR__ . "/../session_check.php";
require_once __DIR__ . "/../menu.php";

if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("Unauthorized access. Admin only.");
}

require_once __DIR__ . "/../../db.php";
require_once __DIR__ . "/../../audit.php";

if (!isset($conn) || !($conn instanceof PDO)) {
    exit("Database connection failed.");
}

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$message = "";
$messageType = "";
$editStudent = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $token = $_POST["csrf_token"] ?? "";

        if (
            !is_string($token) ||
            !hash_equals($_SESSION["csrf_token"], $token)
        ) {
            throw new RuntimeException(
                "Invalid request. Refresh the page and try again."
            );
        }

        $action = $_POST["action"] ?? "";

        if ($action === "add") {
            $studentId = trim($_POST["student_id"] ?? "");
            $name = trim($_POST["full_name"] ?? "");
            $email = trim($_POST["email"] ?? "");
            $mobile = trim($_POST["mobile"] ?? "");
            $course = trim($_POST["course"] ?? "");
            $password = $_POST["password"] ?? "";

            if (
                $studentId === "" ||
                $name === "" ||
                $email === "" ||
                $mobile === "" ||
                $course === "" ||
                $password === ""
            ) {
                throw new RuntimeException(
                    "Please fill in all fields."
                );
            }

            if (!preg_match("/^[0-9]{2}[A-Z]{2,3}[0-9]{3}$/", $studentId)) {
                throw new RuntimeException(
                    "Student ID must look like 25CE015 (2 digits, 2-3 letters, 3 digits)."
                );
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException(
                    "Enter a valid email address."
                );
            }

            if (strlen($password) < 8) {
                throw new RuntimeException(
                    "Password must be at least 8 characters."
                );
            }

            $check = $conn->prepare(
                "SELECT student_id
                 FROM students
                 WHERE student_id = :id OR email = :email"
            );

            $check->execute([
                ":id" => $studentId,
                ":email" => $email
            ]);

            if ($check->fetch(PDO::FETCH_ASSOC)) {
                throw new RuntimeException(
                    "Student ID or email already exists."
                );
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO students
                    (student_id, full_name, email, mobile, course, password, role)
                 VALUES
                    (:id, :name, :email, :mobile, :course, :password, 'student')"
            );

            $stmt->execute([
                ":id" => $studentId,
                ":name" => $name,
                ":email" => $email,
                ":mobile" => $mobile,
                ":course" => $course,
                ":password" => $hash
            ]);

            $message = "Student added successfully!";
            $messageType = "success";

            log_audit($conn, "student_create", "student", $email, "Created " . $name);
        }

        elseif ($action === "update") {
            $studentId = trim($_POST["student_id"] ?? "");
            $name = trim($_POST["full_name"] ?? "");
            $email = trim($_POST["email"] ?? "");
            $mobile = trim($_POST["mobile"] ?? "");
            $course = trim($_POST["course"] ?? "");
            $password = $_POST["password"] ?? "";

            if ($studentId === "") {
                throw new RuntimeException("Invalid Student ID.");
            }

            if (
                $name === "" ||
                $email === "" ||
                $mobile === "" ||
                $course === ""
            ) {
                throw new RuntimeException(
                    "Name, email, mobile and course are required."
                );
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException(
                    "Enter a valid email address."
                );
            }

            $check = $conn->prepare(
                "SELECT student_id
                 FROM students
                 WHERE student_id = :id AND role = 'student'"
            );
            $check->execute([":id" => $studentId]);

            if (!$check->fetch(PDO::FETCH_ASSOC)) {
                throw new RuntimeException("Student not found.");
            }

            if ($password !== "") {
                if (strlen($password) < 8) {
                    throw new RuntimeException(
                        "New password must be at least 8 characters."
                    );
                }

                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare(
                    "UPDATE students
                     SET full_name = :name,
                         email = :email,
                         mobile = :mobile,
                         course = :course,
                         password = :password
                     WHERE student_id = :id AND role = 'student'"
                );

                $stmt->execute([
                    ":name" => $name,
                    ":email" => $email,
                    ":mobile" => $mobile,
                    ":course" => $course,
                    ":password" => $hash,
                    ":id" => $studentId
                ]);
            } else {
                $stmt = $conn->prepare(
                    "UPDATE students
                     SET full_name = :name,
                         email = :email,
                         mobile = :mobile,
                         course = :course
                     WHERE student_id = :id AND role = 'student'"
                );

                $stmt->execute([
                    ":name" => $name,
                    ":email" => $email,
                    ":mobile" => $mobile,
                    ":course" => $course,
                    ":id" => $studentId
                ]);
            }

            $message = "Student updated successfully!";
            $messageType = "success";

            log_audit($conn, "student_update", "student", $studentId, "Updated " . $name);
        }

        elseif ($action === "delete") {
            $studentId = trim($_POST["student_id"] ?? "");

            if ($studentId === "") {
                throw new RuntimeException("Student ID is required.");
            }

            $stmt = $conn->prepare(
                "DELETE FROM students
                 WHERE student_id = :id AND role = 'student'"
            );

            $stmt->execute([":id" => $studentId]);

            if ($stmt->rowCount() > 0) {
                $message = "Student " . $studentId .
                    " deleted successfully!";
                $messageType = "success";

                log_audit($conn, "student_delete", "student", $studentId, "Deleted student " . $studentId);
            } else {
                $message = "Student not found, or this is not a student account.";
                $messageType = "error";
            }
        } else {
            throw new RuntimeException("Invalid action.");
        }
    } catch (RuntimeException $ex) {
        $message = $ex->getMessage();
        $messageType = "error";
    } catch (PDOException $ex) {
        error_log("Student CRUD error: " . $ex->getMessage());

        if ($ex->getCode() === "23000") {
            $message = "Operation failed. Email may already exist, or this student may be linked to other records.";
        } else {
            $message = "Database operation failed. Check the PHP error log.";
        }

        $messageType = "error";
    }
}

$editId = trim($_GET["edit"] ?? "");

if ($editId !== "") {
    try {
        $stmt = $conn->prepare(
            "SELECT student_id, full_name, email, mobile, course
             FROM students
             WHERE student_id = :id AND role = 'student'"
        );

        $stmt->execute([":id" => $editId]);
        $editStudent = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        if (!$editStudent && $message === "") {
            $message = "Student not found.";
            $messageType = "error";
        }
    } catch (PDOException $ex) {
        error_log("Student edit error: " . $ex->getMessage());
        $message = "Unable to load student for editing.";
        $messageType = "error";
    }
}

$search = trim($_GET["search"] ?? "");
$courseFilter = trim($_GET["course"] ?? "");

try {
    $courseStmt = $conn->query(
        "SELECT DISTINCT course
         FROM students
         WHERE role = 'student'
           AND course IS NOT NULL
           AND course <> ''
         ORDER BY course"
    );

    $courses = $courseStmt->fetchAll(PDO::FETCH_COLUMN);

    $sql = "SELECT student_id, full_name, email, mobile, course
            FROM students
            WHERE role = 'student'";

    $params = [];

    if ($search !== "") {
        $sql .= " AND (
            CAST(student_id AS CHAR) LIKE :sid
            OR full_name LIKE :name
            OR email LIKE :email
            OR mobile LIKE :mobile
        )";

        $term = "%" . $search . "%";

        $params[":sid"] = $term;
        $params[":name"] = $term;
        $params[":email"] = $term;
        $params[":mobile"] = $term;
    }

    if ($courseFilter !== "") {
        $sql .= " AND course = :course";
        $params[":course"] = $courseFilter;
    }

    $sql .= " ORDER BY student_id DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalStudents = (int)$conn->query(
        "SELECT COUNT(*) FROM students WHERE role = 'student'"
    )->fetchColumn();

} catch (PDOException $ex) {
    error_log("Student listing error: " . $ex->getMessage());

    $courses = [];
    $students = [];
    $totalStudents = 0;

    if ($message === "") {
        $message = "Could not load student records.";
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Students | StudentHub</title>

    <link rel="stylesheet" href="../../css/style.css">

    <style>
        .crud-card {
            background: var(--surface);
            color: var(--ink);
            padding: 24px;
            margin-bottom: 24px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-card);
        }

        .crud-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }

        .crud-form label,
        .search-form label {
            display: block;
            font-weight: 600;
            font-size: 0.88rem;
            margin-bottom: 6px;
            color: var(--ink);
        }

        .crud-form input,
        .search-form input,
        .search-form select {
            box-sizing: border-box;
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--surface);
            color: var(--ink);
            font: inherit;
        }

        .crud-form input:focus,
        .search-form input:focus,
        .search-form select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-soft);
        }

        .crud-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 14px;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 600;
            transition: all var(--duration-ui) var(--ease-ui);
            margin: 2px;
        }

        .primary-btn {
            background: var(--accent);
            color: white;
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
            color: white;
        }

        .cancel-btn {
            background: var(--surface-muted);
            color: var(--ink-secondary);
            border: 1px solid var(--border);
        }

        .message {
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            margin: 15px 0;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .success {
            background: var(--accent-soft);
            color: var(--success);
            border: 1px solid var(--border);
        }

        .error {
            background: rgba(168, 67, 59, 0.1);
            color: var(--danger);
            border: 1px solid rgba(168, 67, 59, 0.25);
        }

        .search-form {
            display: grid;
            grid-template-columns: 2fr 1fr auto auto;
            gap: 14px;
            align-items: end;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            margin-top: 14px;
        }

        .student-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        .student-table th,
        .student-table td {
            padding: 12px 14px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .student-table th {
            background: var(--surface-muted);
            color: var(--ink-secondary);
            font-weight: 600;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .student-table tr:last-child td {
            border-bottom: none;
        }

        .crud-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
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
                <span>Student Directory &bull; Records Database</span>
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

    <div class="crud-header">
        <div>
            <h1>Student Management</h1>
            <p>Add, view, edit, delete, search and filter students.</p>
        </div>

        <h3>Total Students: <?= number_format($totalStudents) ?></h3>
    </div>

    <?php if ($message !== ""): ?>
        <div class="message <?= e($messageType) ?>" role="status">
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <!-- ADD / EDIT FORM -->
    <section class="crud-card">
        <h2><?= $editStudent ? "Edit Student" : "Add New Student" ?></h2>

        <form method="POST"
              action="manage_students.php<?= $editStudent ? "?edit=" . rawurlencode($editStudent["student_id"]) : "" ?>">

            <input type="hidden"
                   name="csrf_token"
                   value="<?= e($_SESSION["csrf_token"]) ?>">

            <input type="hidden"
                   name="action"
                   value="<?= $editStudent ? "update" : "add" ?>">

            <div class="crud-form">
                <div>
                    <label for="student_id">Student ID *</label>
                    <input type="text" id="student_id" name="student_id"
                           maxlength="20"
                           value="<?= e($editStudent["student_id"] ?? "") ?>"
                           <?php if ($editStudent): ?>
                               readonly
                           <?php else: ?>
                               required
                               pattern="[0-9]{2}[A-Z]{2,3}[0-9]{3}"
                               title="Format: 25CE015 (2 digits, 2-3 letters, 3 digits)"
                           <?php endif; ?>>
                </div>

                <div>
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name"
                           maxlength="150" required
                           value="<?= e($editStudent["full_name"] ?? "") ?>">
                </div>

                <div>
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email"
                           maxlength="255" required
                           value="<?= e($editStudent["email"] ?? "") ?>">
                </div>

                <div>
                    <label for="mobile">Mobile *</label>
                    <input type="text" id="mobile" name="mobile"
                           maxlength="20" required
                           value="<?= e($editStudent["mobile"] ?? "") ?>">
                </div>

                <div>
                    <label for="course">Course *</label>
                    <input type="text" id="course" name="course"
                           maxlength="100" required
                           value="<?= e($editStudent["course"] ?? "") ?>">
                </div>

                <div>
                    <label for="password">
                        Password <?= $editStudent ? "(optional)" : "*" ?>
                    </label>
                    <input type="password" id="password" name="password"
                           minlength="8" autocomplete="new-password"
                           <?= $editStudent ? "" : "required" ?>>
                </div>
            </div>

            <p>
                <button type="submit" class="crud-btn primary-btn">
                    <?= $editStudent ? "Update Student" : "Add Student" ?>
                </button>

                <?php if ($editStudent): ?>
                    <a href="manage_students.php"
                       class="crud-btn cancel-btn">Cancel Edit</a>
                <?php endif; ?>
            </p>
        </form>
    </section>

    <!-- SEARCH -->
    <section class="crud-card">
        <h2>Search and Filter Students</h2>

        <form method="GET" action="manage_students.php" class="search-form">
            <div>
                <label for="search">Search</label>
                <input type="search"
                       id="search"
                       name="search"
                       placeholder="Student ID, name, email or mobile"
                       value="<?= e($search) ?>">
            </div>

            <div>
                <label for="course">Filter by Course</label>
                <select id="course" name="course">
                    <option value="">All Courses</option>

                    <?php foreach ($courses as $course): ?>
                        <option value="<?= e($course) ?>"
                            <?= $courseFilter === $course ? "selected" : "" ?>>
                            <?= e($course) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="crud-btn primary-btn">
                Search / Filter
            </button>

            <a href="manage_students.php"
               class="crud-btn cancel-btn">Reset</a>
        </form>
    </section>

    <!-- STUDENT TABLE -->
    <section class="crud-card">
        <h2>Student Records (<?= count($students) ?> results)</h2>

        <div class="table-wrapper">
            <table class="student-table">
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

                <tbody>
                <?php if ($students): ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= e($student["student_id"]) ?></td>
                            <td><?= e($student["full_name"]) ?></td>
                            <td><?= e($student["email"]) ?></td>
                            <td><?= e($student["mobile"]) ?></td>
                            <td><?= e($student["course"]) ?></td>
                            <td>
                                <a class="crud-btn edit-btn"
                                   href="manage_students.php?edit=<?= rawurlencode($student["student_id"]) ?>">
                                    Edit
                                </a>

                                <form method="POST"
                                      action="manage_students.php"
                                      style="display:inline"
                                      onsubmit="return confirm('Delete student <?= e($student["student_id"]) ?>? This action cannot be undone.');">

                                    <input type="hidden"
                                           name="csrf_token"
                                           value="<?= e($_SESSION["csrf_token"]) ?>">

                                    <input type="hidden"
                                           name="action"
                                           value="delete">

                                    <input type="hidden"
                                           name="student_id"
                                           value="<?= e($student["student_id"]) ?>">

                                    <button type="submit"
                                            class="crud-btn delete-btn">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No student records found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

        </div>
    </main>
</div>

<footer>
    <p>&copy; 2026 CHARUSAT University &bull; Student HUB Portal</p>
</footer>

<script src="../../js/index.js"></script>

</body>
</html>
