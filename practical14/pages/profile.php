<?php
require_once "session_check.php";
require_once "db.php";

if ($_SESSION["role"] !== "student") {
    http_response_code(403);
    exit("Unauthorized access.");
}

$student_id = $_SESSION["student_id"];
$stmt = $conn->prepare("SELECT student_id, full_name, email, mobile, course FROM students WHERE student_id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    exit("Student not found.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Profile</title>

    <link
        rel="stylesheet"
        href="../css/profile.css">

</head>


<body>


    <!-- Header -->

    <header class="header">


        <div class="logo">

            <img
                src="../images/charusat.png"
                alt="CHARUSAT Logo">

        </div>


        <!-- Navigation -->

        <nav>

            <ul>

                <li>
                    <a href="profile.php">
                        Profile
                    </a>
                </li>

                <li>
                    <a href="attendance.php">
                        Attendance
                    </a>
                </li>

                <li>
                    <a href="assignment.php">
                        Assignment
                    </a>
                </li>

                
                <li>
                    <a href="event_register.php">
                        Event Registration
                    </a>
                </li>

                <li>
                    <a href="index.php">
                        Dashboard
                    </a>
                </li>
               
                <li>
                    <a href="courses.php">
                        Courses
                    </a>
                </li>
                <li>
                    <a href="students.csv">
                        Faculty
                    </a>
                </li>

                <li>
                    <a href="contact.php">
                        Contact
                    </a>
                </li>

            </ul>


        </nav>


        <!-- Login -->

        <div class="logout-text">

            <a href="logout.php">
                Logout
            </a>

        </div>


    </header>


    <!-- Profile Section -->

    <div class="profile-card">


        <img
            src="profile.png"
            alt="Student Photo"
            class="profile-img">


        <div class="profile-info">


            <h2>
                Student Profile
            </h2>


            <p>
                <strong>Name:</strong>
                <?= htmlspecialchars($student['full_name']) ?>
            </p>


            <p>
                <strong>Roll No:</strong>
                <?= htmlspecialchars($student['student_id']) ?>
            </p>


            <p>
                <strong>Department:</strong>
                CSPIT
            </p>


            <p>
                <strong>Year:</strong>
                II Year
            </p>


            <p>
                <strong>Email:</strong>
                <?= htmlspecialchars($student['email']) ?>
            </p>


            <p>
                <strong>Phone:</strong>
                <?= htmlspecialchars($student['mobile']) ?>
            </p>


        </div>

    </div>

</body>

</html>