<?php

session_start();

if (!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

require_once "../db.php";

$message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (
        !isset($_POST["csrf_token"]) ||
        $_POST["csrf_token"] != $_SESSION["csrf_token"]
    ) {

        $message = "Invalid form submission.";

    }
    else {

        $studentid = trim($_POST["studentid"]);
        $fullname = trim($_POST["fullname"]);
        $email = trim($_POST["email"]);
        $mobile = trim($_POST["mobile"]);
        $course = trim($_POST["course"]);
        $password = $_POST["password"];
        $confirm = $_POST["confirm"];


        if ($studentid == "") {

            $message = "Enter Student ID";

        }
        elseif (
            !preg_match(
                "/^[0-9]{2}[A-Z]{2,3}[0-9]{3}$/",
                $studentid
            )
        ) {

            $message = "Student ID must be like 25CE015";

        }
        elseif ($fullname == "") {

            $message = "Enter your name";

        }
        elseif (!preg_match("/^[A-Za-z ]{3,}$/", $fullname)) {

            $message = "Enter a valid name";

        }
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $message = "Enter a valid email";

        }
        elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {

            $message = "Mobile number must be 10 digits";

        }
        elseif ($course == "") {

            $message = "Select a course";

        }
        elseif (strlen($password) < 8) {

            $message = "Password must be at least 8 characters";

        }
        elseif ($password != $confirm) {

            $message = "Passwords do not match";

        }
        else {

            try {

                $check = $conn->prepare(
                    "SELECT student_id, email
                     FROM students
                     WHERE student_id = :student_id
                     OR email = :email"
                );

                $check->execute([
                    ":student_id" => $studentid,
                    ":email" => $email
                ]);


                if ($check->fetch()) {

                    $message = "Student ID or Email already exists";

                }
                else {

                    $hashedPassword = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );


                    $sql = "INSERT INTO students
                            (
                                student_id,
                                full_name,
                                email,
                                mobile,
                                course,
                                password
                            )
                            VALUES
                            (
                                :student_id,
                                :full_name,
                                :email,
                                :mobile,
                                :course,
                                :password
                            )";

                    $stmt = $conn->prepare($sql);

                    $stmt->execute([
                        ":student_id" => $studentid,
                        ":full_name" => $fullname,
                        ":email" => $email,
                        ":mobile" => $mobile,
                        ":course" => $course,
                        ":password" => $hashedPassword
                    ]);


                    $file = "../data/students.csv";

                    $fp = fopen($file, "a");


                    if ($fp === false) {

                        $message =
                            "Saved in database but CSV file could not be opened.";

                    }
                    else {

                        if (filesize($file) == 0) {

                            fputcsv($fp, [
                                "Student ID",
                                "Full Name",
                                "Email",
                                "Mobile",
                                "Course",
                                "Password"
                            ]);

                        }


                        fputcsv($fp, [
                            $studentid,
                            $fullname,
                            $email,
                            $mobile,
                            $course,
                            $hashedPassword
                        ]);

                        fclose($fp);


                        $message =
                            "Registration Successful! Data saved in CSV and Database.";

                    }

                }

            }
            catch (PDOException $e) {

                $message =
                    "Database error. Student was not registered.";

            }

        }

    }

}

?>

<!DOCTYPE html>
<html lang="en">


<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Student Registration | Student HUB</title>


    <link
        rel="stylesheet"
        href="../css/style.css">

</head>


<body>

    <div class="auth-shell reveal">

        <div class="auth-card">

            <div class="logo">
                <img src="../images/charusat.png" alt="CHARUSAT Logo">
                <span class="logo-wordmark">Student <strong>HUB</strong></span>
            </div>

            <h1>Create Account</h1>
            <p>Register as a new student.</p>

            <?php

            if ($message != "") {

                $type = strpos($message, "Successful") !== false ? "success" : "error";
                echo "<div class=\"form-message form-message--{$type}\">";
                echo htmlspecialchars($message);
                echo "</div>";

            }

            ?>


            <form
                id="registerForm"
                method="POST"
                action="register.php">


                <!-- CSRF Token -->

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php
                        echo htmlspecialchars(
                            $_SESSION["csrf_token"]
                        );
                    ?>">


                <div class="form-group reveal">

                    <label for="studentid">
                        Student ID:
                    </label>

                    <input
                        type="text"
                        id="studentid"
                        name="studentid"
                        placeholder="Example: 25CE015"
                        maxlength="8"
                        required>

                </div>

                <div class="form-group reveal">

                    <label for="fullname">
                        Full Name:
                    </label>

                    <input
                        type="text"
                        id="fullname"
                        name="fullname"
                        placeholder="Enter your full name"
                        required>

                </div>

                <div class="form-group reveal">

                    <label for="email">
                        Email:
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        required>

                </div>

                <div class="form-group reveal">

                    <label for="mobile">
                        Mobile Number:
                    </label>

                    <input
                        type="tel"
                        id="mobile"
                        name="mobile"
                        placeholder="Enter 10-digit mobile number"
                        maxlength="10"
                        required>

                </div>

                <div class="form-group reveal">

                    <label for="course">
                        Course:
                    </label>

                    <select
                        id="course"
                        name="course"
                        required>

                        <option value="">
                            Select Course
                        </option>

                        <option value="B.tech">
                            B.tech
                        </option>

                        <option value="BSc IT">
                            BSc IT
                        </option>

                        <option value="BCom">
                            BCom
                        </option>

                        <option value="BBA">
                            BBA
                        </option>

                    </select>

                </div>

                <div class="form-group reveal">

                    <label for="password">
                        Password:
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter password"
                        required>

                </div>

                <div class="form-group reveal">

                    <label for="confirm">
                        Confirm Password:
                    </label>

                    <input
                        type="password"
                        id="confirm"
                        name="confirm"
                        placeholder="Confirm password"
                        required>

                </div>

                <div class="btn-group reveal" style="margin-top:10px;">
                    <button type="submit" class="btn btn--accent">Register</button>
                    <button type="reset" class="btn btn--ghost">Reset</button>
                </div>

            </form>

        </div>

    </div>

    <footer>
        <p>&copy; 2026 CHARUSAT University &bull; Student HUB Portal</p>
    </footer>

    <script src="../js/register.js"></script>

</body>

</html>
