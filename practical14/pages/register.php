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

    <title>StudentHub - Register</title>


    <link
        rel="stylesheet"
        href="../css/style.css">

</head>


<body>


    <!-- Header -->

    <header class="header reveal">


        <div class="header-inner reveal" style="animation-delay:0.05s">


            <div class="logo reveal" style="animation-delay:0.1s">

                <img
                    src="../images/charusat.png"
                    alt="CHARUSAT Logo">

            </div>


            <!-- Navigation -->

            <nav>

                <ul class="nav-list">


                    <li>

                        <a href="login.php">
                            Sign In
                        </a>

                    </li>


                     

                </ul>

            </nav>


        </div>


    </header>


    <!-- Registration Section -->

    <main>

        <section class="box reveal" style="max-width:560px;margin:0 auto 44px auto;">

            <div class="page-title-wrap reveal" style="margin-bottom:8px;">
                <h2>Student Registration</h2>
                <span class="tag">CREATE ACCOUNT</span>
            </div>

            <?php

            if ($message != "") {

                echo "<p class=\"login-message login-message--error\">";

                echo htmlspecialchars($message);

                echo "</p>";

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


                <!-- Student ID -->

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


                <!-- Full Name -->

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



                <!-- Email -->

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



                <!-- Mobile -->

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



                <!-- Course -->

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



                <!-- Password -->

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



                <!-- Confirm Password -->

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



                <!-- Buttons -->

                <div class="btn-group reveal" style="margin-top:10px;">
                    <button type="submit" class="btn btn--accent">Register</button>
                    <button type="reset" class="btn btn--ghost">Reset</button>
                </div>

            </form>


        </section>

    </main>


    <!-- Footer -->

    <footer>

        <p>
            @2026 Student HUB Portal
        </p>

    </footer>


</body>

</html>
