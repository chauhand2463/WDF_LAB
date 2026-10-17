<?php
declare(strict_types=1);

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/../audit.php";

require_login();

$method = $_SERVER["REQUEST_METHOD"];
$id = trim((string) ($_GET["id"] ?? ""));

function student_fields(array $row): array
{
    return [
        "student_id" => (string) $row["student_id"],
        "full_name" => (string) $row["full_name"],
        "email" => (string) $row["email"],
        "mobile" => (string) $row["mobile"],
        "course" => (string) $row["course"],
        "role" => (string) ($row["role"] ?? "student")
    ];
}

function validate_student_input(array $input, bool $isUpdate): array
{
    $studentId = trim((string) ($input["student_id"] ?? ""));
    $name = trim((string) ($input["full_name"] ?? ""));
    $email = trim((string) ($input["email"] ?? ""));
    $mobile = trim((string) ($input["mobile"] ?? ""));
    $course = trim((string) ($input["course"] ?? ""));
    $password = (string) ($input["password"] ?? "");

    $errors = [];

    if (!$isUpdate && !preg_match("/^[0-9]{2}[A-Z]{2,3}[0-9]{3}$/", $studentId)) {
        $errors["student_id"] = "Student ID must look like 25CE015.";
    }

    if (mb_strlen($name) < 3 || !preg_match("/^[A-Za-z .'-]+$/u", $name)) {
        $errors["full_name"] = "Enter a valid full name (letters only, 3+ characters).";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Enter a valid email address.";
    }

    if (!preg_match("/^[0-9]{10}$/", $mobile)) {
        $errors["mobile"] = "Mobile number must contain exactly 10 digits.";
    }

    if ($course === "") {
        $errors["course"] = "Course is required.";
    }

    if (!$isUpdate && strlen($password) < 8) {
        $errors["password"] = "Password must be at least 8 characters.";
    }

    if ($isUpdate && $password !== "" && strlen($password) < 8) {
        $errors["password"] = "New password must be at least 8 characters.";
    }

    return [
        $studentId,
        $name,
        $email,
        $mobile,
        $course,
        $password,
        $errors
    ];
}

try {
    if ($method === "GET") {
        if ($id !== "") {
            $stmt = $conn->prepare(
                "SELECT student_id, full_name, email, mobile, course, role
                 FROM students
                 WHERE student_id = :id"
            );
            $stmt->execute([":id" => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                json_error("Student not found.", 404);
            }

            json_response([
                "success" => true,
                "data" => student_fields($row)
            ]);
        }

        $search = trim((string) ($_GET["search"] ?? ""));
        $course = trim((string) ($_GET["course"] ?? ""));
        $roleFilter = trim((string) ($_GET["role"] ?? "student"));

        $sort = (string) ($_GET["sort"] ?? "student_id");
        $allowedSort = ["student_id", "full_name", "email", "course"];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = "student_id";
        }

        $dir = strtolower((string) ($_GET["dir"] ?? "asc")) === "desc"
            ? "DESC"
            : "ASC";

        $page = max(1, (int) ($_GET["page"] ?? 1));
        $limit = min(100, max(1, (int) ($_GET["limit"] ?? 10)));
        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [];

        if (in_array($roleFilter, ["student", "admin"], true)) {
            $where[] = "role = :role";
            $params[":role"] = $roleFilter;
        }

        if ($search !== "") {
            $where[] = "(CAST(student_id AS CHAR) LIKE :search
                         OR full_name LIKE :search
                         OR email LIKE :search
                         OR mobile LIKE :search)";
            $params[":search"] = "%" . $search . "%";
        }

        if ($course !== "") {
            $where[] = "course = :course";
            $params[":course"] = $course;
        }

        $whereSql = $where ? " WHERE " . implode(" AND ", $where) : "";

        $countStmt = $conn->prepare("SELECT COUNT(*) FROM students" . $whereSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $sql = "SELECT student_id, full_name, email, mobile, course, role
                FROM students" . $whereSql . "
                ORDER BY " . $sort . " " . $dir . "
                LIMIT :limit OFFSET :offset";

        $stmt = $conn->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        json_response([
            "success" => true,
            "data" => array_map("student_fields", $rows),
            "total" => $total,
            "page" => $page,
            "limit" => $limit,
            "pages" => $total > 0 ? (int) ceil($total / $limit) : 0
        ]);
    }

    if ($method === "POST") {
        require_admin();
        verify_api_csrf($_SERVER["HTTP_X_CSRF_TOKEN"] ?? null);

        $input = read_json_body();

        [$studentId, $name, $email, $mobile, $course, $password, $errors] =
            validate_student_input($input, false);

        if ($errors) {
            json_error("Please fix the highlighted fields.", 422, ["errors" => $errors]);
        }

        $check = $conn->prepare(
            "SELECT student_id FROM students
             WHERE student_id = :id OR email = :email"
        );
        $check->execute([":id" => $studentId, ":email" => $email]);

        if ($check->fetch()) {
            json_error("Student ID or email already exists.", 409, [
                "errors" => ["student_id" => "Student ID or email already exists."]
            ]);
        }

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
            ":password" => password_hash($password, PASSWORD_DEFAULT)
        ]);

        log_audit($conn, "student_create", "student", $studentId, "API created " . $name);

        json_response([
            "success" => true,
            "message" => "Student added successfully.",
            "data" => [
                "student_id" => $studentId,
                "full_name" => $name,
                "email" => $email,
                "mobile" => $mobile,
                "course" => $course,
                "role" => "student"
            ]
        ], 201);
    }

    if ($method === "PUT") {
        require_admin();
        verify_api_csrf($_SERVER["HTTP_X_CSRF_TOKEN"] ?? null);

        $input = read_json_body();
        $studentId = trim((string) ($input["student_id"] ?? $id));

        if ($studentId === "") {
            json_error("Student ID is required.", 422);
        }

        [$studentId, $name, $email, $mobile, $course, $password, $errors] =
            validate_student_input($input, true);

        if ($errors) {
            json_error("Please fix the highlighted fields.", 422, ["errors" => $errors]);
        }

        $exists = $conn->prepare(
            "SELECT role FROM students WHERE student_id = :id"
        );
        $exists->execute([":id" => $studentId]);
        $current = $exists->fetch(PDO::FETCH_ASSOC);

        if (!$current) {
            json_error("Student not found.", 404);
        }

        $emailClash = $conn->prepare(
            "SELECT student_id FROM students
             WHERE email = :email AND student_id <> :id"
        );
        $emailClash->execute([":email" => $email, ":id" => $studentId]);

        if ($emailClash->fetch()) {
            json_error("That email is already used by another account.", 409, [
                "errors" => ["email" => "Email already in use."]
            ]);
        }

        if ($password !== "") {
            $stmt = $conn->prepare(
                "UPDATE students
                 SET full_name = :name,
                     email = :email,
                     mobile = :mobile,
                     course = :course,
                     password = :password
                 WHERE student_id = :id"
            );

            $stmt->execute([
                ":name" => $name,
                ":email" => $email,
                ":mobile" => $mobile,
                ":course" => $course,
                ":password" => password_hash($password, PASSWORD_DEFAULT),
                ":id" => $studentId
            ]);
        } else {
            $stmt = $conn->prepare(
                "UPDATE students
                 SET full_name = :name,
                     email = :email,
                     mobile = :mobile,
                     course = :course
                 WHERE student_id = :id"
            );

            $stmt->execute([
                ":name" => $name,
                ":email" => $email,
                ":mobile" => $mobile,
                ":course" => $course,
                ":id" => $studentId
            ]);
        }

        log_audit($conn, "student_update", "student", $studentId, "API updated " . $name);

        json_response([
            "success" => true,
            "message" => "Student updated successfully.",
            "data" => [
                "student_id" => $studentId,
                "full_name" => $name,
                "email" => $email,
                "mobile" => $mobile,
                "course" => $course,
                "role" => (string) ($current["role"] ?? "student")
            ]
        ]);
    }

    if ($method === "DELETE") {
        require_admin();
        verify_api_csrf($_SERVER["HTTP_X_CSRF_TOKEN"] ?? null);

        $studentId = $id;

        if ($studentId === "") {
            $input = read_json_body();
            $studentId = trim((string) ($input["student_id"] ?? ""));
        }

        if ($studentId === "") {
            json_error("Student ID is required.", 422);
        }

        $stmt = $conn->prepare(
            "DELETE FROM students
             WHERE student_id = :id AND role = 'student'"
        );
        $stmt->execute([":id" => $studentId]);

        if ($stmt->rowCount() === 0) {
            json_error("Student not found, or this is not a student account.", 404);
        }

        log_audit($conn, "student_delete", "student", $studentId, "API deleted student " . $studentId);

        json_response([
            "success" => true,
            "message" => "Student deleted successfully.",
            "data" => ["student_id" => $studentId]
        ]);
    }

    json_error("Method not allowed.", 405);
} catch (PDOException $e) {
    error_log("Students API error: " . $e->getMessage());

    if ($e->getCode() === "23000") {
        json_error("Operation failed. This record is referenced by other data.", 409);
    }

    json_error("Database operation failed.", 500);
}
