<?php
require_once __DIR__ . "/session_check.php";
require_once __DIR__ . "/menu.php";
require_once __DIR__ . "/db.php";

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

    <title>Profile | Student HUB Portal</title>

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

            <nav id="navbar">
                <?= render_dynamic_menu("student") ?>
            </nav>


            <!-- Login -->

            <div class="logout-text reveal" style="animation-delay:0.2s">

                <a href="logout.php">
                    Logout
                </a>

            </div>


        </div>


    </header>

    <!-- Profile Section -->

    <main>

        <div class="page-title-wrap reveal">
            <h1>Student Profile</h1>
            <span class="tag">ACCOUNT DETAILS</span>
        </div>

        <div class="profile-card box reveal" style="max-width:560px;margin:0 auto 44px auto;text-align:center;">

            <img
                src="profile.png"
                alt="Student Photo"
                class="profile-img"
                style="width:110px;height:110px;border-radius:50%;object-fit:cover;border:2px solid var(--border-focus);margin:0 auto 16px auto;display:block;box-shadow:0 0 24px var(--accent-glow);">

            <div class="profile-info">

                <h2>
                    <?= htmlspecialchars($student['full_name']) ?>
                </h2>

                <p>
                    <strong>Roll No:</strong>
                    <span style="font-family:var(--font-mono);color:var(--accent);">
                        <?= htmlspecialchars($student['student_id']) ?>
                    </span>
                </p>


                <p>
                    <strong>Department:</strong> CSPIT
                </p>


                <p>
                    <strong>Year:</strong> II Year
                </p>


                <p>
                    <strong>Email:</strong>
                    <?= htmlspecialchars($student['email']) ?>
                </p>


                <p>
                    <strong>Phone:</strong>
                    <span style="font-family:var(--font-mono);">
                        <?= htmlspecialchars($student['mobile']) ?>
                    </span>
                </p>

            </div>

        </div>

    </main>

</body>

</html>
