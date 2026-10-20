<?php
session_start();
require_once "../db.php";

$message = "";
$messageType = "error";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $studentid = trim($_POST["studentid"] ?? "");
    $fullname = trim($_POST["fullname"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");
    $course = trim($_POST["course"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm = $_POST["confirm"] ?? "";

    if (!preg_match("/^[0-9]{2}[A-Z]{2,3}[0-9]{3}$/", $studentid)) {
        $message = "Enter a valid ID, for example 25AD001 or 25CE015.";
        $messageType = "error";
    } elseif (!preg_match("/^[A-Za-z ]{3,}$/", $fullname)) {
        $message = "Enter a valid full name (letters and spaces only).";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Enter a valid institutional email address.";
        $messageType = "error";
    } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {
        $message = "Mobile number must contain exactly 10 digits.";
        $messageType = "error";
    } elseif ($course === "") {
        $message = "Please select a department.";
        $messageType = "error";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
        $messageType = "error";
    } elseif ($password !== $confirm) {
        $message = "Passwords do not match.";
        $messageType = "error";
    } else {
        try {
            $check = $conn->prepare(
                "SELECT student_id FROM students WHERE student_id = :student_id OR email = :email"
            );
            $check->execute([
                ":student_id" => $studentid,
                ":email" => $email
            ]);

            if ($check->fetch()) {
                $message = "Administrator ID or email already exists in the system.";
                $messageType = "error";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $sql = "INSERT INTO students
                        (student_id, full_name, email, mobile, course, password, role)
                        VALUES
                        (:student_id, :full_name, :email, :mobile, :course, :password, 'admin')";

                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ":student_id" => $studentid,
                    ":full_name" => $fullname,
                    ":email" => $email,
                    ":mobile" => $mobile,
                    ":course" => $course,
                    ":password" => $hashedPassword
                ]);

                $message = "Administrator account created successfully! You can now log in.";
                $messageType = "success";
            }
        } catch (PDOException $e) {
            $message = "Database error. Administrator account could not be created.";
            $messageType = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Registration | Student HUB</title>

    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <div class="auth-shell reveal">

        <div class="auth-card" style="max-width: 540px;">

            <div class="logo">
                <img src="../images/charusat.png" alt="CHARUSAT Logo">
                <span class="logo-wordmark">Student <strong>HUB</strong></span>
            </div>

            <h1>Admin Registration</h1>
            <p>Create an administrator account for portal management.</p>

            <?php if ($message !== ""): ?>
                <div class="form-message form-message--<?= $messageType ?>">
                    <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form id="adminRegisterForm" method="POST" action="admin_register.php" style="display:flex; flex-direction:column; gap:16px;">

                <div class="form-group">
                    <label for="studentid">Admin / Faculty ID <span>*</span></label>
                    <input
                        type="text"
                        id="studentid"
                        name="studentid"
                        maxlength="8"
                        placeholder="e.g. 25AD001"
                        pattern="[0-9]{2}[A-Z]{2,3}[0-9]{3}"
                        required
                        value="<?= htmlspecialchars($_POST['studentid'] ?? '') ?>">
                    <span style="font-size:0.75rem; color:var(--ink-secondary); margin-top:3px; display:block;">Format: 2 digits, 2-3 uppercase letters, 3 digits</span>
                </div>

                <div class="form-group">
                    <label for="fullname">Full Name <span>*</span></label>
                    <input
                        type="text"
                        id="fullname"
                        name="fullname"
                        minlength="3"
                        placeholder="e.g. Dr. Ramesh Patel"
                        required
                        value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="email">Institutional Email <span>*</span></label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="admin@charusat.ac.in"
                        required
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="mobile">Mobile Number <span>*</span></label>
                    <input
                        type="tel"
                        id="mobile"
                        name="mobile"
                        pattern="[0-9]{10}"
                        maxlength="10"
                        placeholder="10-digit mobile number"
                        required
                        value="<?= htmlspecialchars($_POST['mobile'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="course">Department / Faculty <span>*</span></label>
                    <select id="course" name="course" required style="width:100%; padding:10px 14px; border:1px solid var(--border); border-radius:var(--radius-sm); background:var(--surface); color:var(--ink); font-family:inherit;">
                        <option value="">-- Select Department --</option>
                        <option value="Computer Engineering" <?= (($_POST['course'] ?? '') === 'Computer Engineering') ? 'selected' : '' ?>>Computer Engineering (CE)</option>
                        <option value="Information Technology" <?= (($_POST['course'] ?? '') === 'Information Technology') ? 'selected' : '' ?>>Information Technology (IT)</option>
                        <option value="Mechanical Engineering" <?= (($_POST['course'] ?? '') === 'Mechanical Engineering') ? 'selected' : '' ?>>Mechanical Engineering (ME)</option>
                        <option value="Civil Engineering" <?= (($_POST['course'] ?? '') === 'Civil Engineering') ? 'selected' : '' ?>>Civil Engineering (CL)</option>
                        <option value="Electrical Engineering" <?= (($_POST['course'] ?? '') === 'Electrical Engineering') ? 'selected' : '' ?>>Electrical Engineering (EE)</option>
                        <option value="Administration" <?= (($_POST['course'] ?? '') === 'Administration') ? 'selected' : '' ?>>Central Administration</option>
                    </select>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <div class="form-group">
                        <label for="password">Password <span>*</span></label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            minlength="8"
                            placeholder="At least 8 chars"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="confirm">Confirm Password <span>*</span></label>
                        <input
                            type="password"
                            id="confirm"
                            name="confirm"
                            minlength="8"
                            placeholder="Confirm password"
                            required>
                    </div>
                </div>

                <div class="btn-group" style="margin-top:10px;">
                    <button type="submit" class="btn btn--accent">Create Administrator Account</button>
                    <a href="login.php" class="btn btn--ghost" style="text-align:center;">Back to Login</a>
                </div>

            </form>

        </div>

    </div>

    <footer>
        <p>&copy; 2026 CHARUSAT University &bull; Student HUB Portal</p>
    </footer>

</body>

</html>
