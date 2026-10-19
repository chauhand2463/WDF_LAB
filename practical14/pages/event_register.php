<?php
require_once __DIR__ . "/session_check.php";
require_once __DIR__ . "/menu.php";

if ($_SESSION["role"] !== "student") {
    http_response_code(403);
    exit("Unauthorized access.");
}
?>
<?php
require_once "../db.php";

$message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $student_id = trim($_POST["student_id"] ?? "");
    $event_id = trim($_POST["event_id"] ?? "");


    if ($student_id == "") {

        $message = "Please enter Student ID.";

    }

    elseif ($event_id == "") {

        $message = "Please select an event.";

    }

    else {

        try {

            $student = $conn->prepare(
                "SELECT student_id
                 FROM students
                 WHERE student_id = :student_id"
            );

            $student->execute([
                ":student_id" => $student_id
            ]);


            if (!$student->fetch()) {

                $message = "Student ID not found.";

            }

            else {

                $check = $conn->prepare(
                    "SELECT registration_id
                     FROM registrations
                     WHERE student_id = :student_id
                     AND event_id = :event_id"
                );

                $check->execute([
                    ":student_id" => $student_id,
                    ":event_id" => $event_id
                ]);


                if ($check->fetch()) {

                    $message =
                        "Student is already registered for this event.";

                }

                else {

                    $sql = "INSERT INTO registrations
                            (student_id, event_id)
                            VALUES
                            (:student_id, :event_id)";

                    $stmt = $conn->prepare($sql);

                    $stmt->execute([
                        ":student_id" => $student_id,
                        ":event_id" => $event_id
                    ]);


                    $message =
                        "Student registered successfully!";

                }

            }

        }

        catch (PDOException $e) {

            $message = "Database error.";

        }

    }

}


try {

    $events = $conn->query(
        "SELECT event_id, event_name, event_date, description
         FROM events
         ORDER BY event_date"
    );

}

catch (PDOException $e) {

    $events = false;

    $message = "Unable to load events.";

}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Event Registration | Student HUB Portal</title>


    <link
        rel="stylesheet"
        href="../css/style.css">

</head>


<body>

    <div class="app-shell reveal">

        <header class="topbar">
            <div class="topbar-left">
                <div class="logo">
                    <img src="../images/charusat.png" alt="CHARUSAT Logo">
                    <span class="logo-wordmark">Student <strong>HUB</strong></span>
                </div>
            </div>
            <div class="topbar-center">
                <div class="page-title-wrap reveal" style="animation-delay:0.05s">
                    <span class="kicker">Campus Events</span>
                    <h1>Event Registration</h1>
                    <p style="margin-top:6px;color:var(--ink-secondary);font-size:0.9rem;">
                        Register for upcoming campus events
                    </p>
                </div>
            </div>
            <div class="topbar-right">
                <a href="logout.php" class="btn btn--ghost">Sign out</a>
            </div>
        </header>

        <aside class="sidebar" id="sidebar">
            <div class="sidebar-inner">
                <div class="sidebar-brand">
                    <img src="../images/charusat.png" alt="CHARUSAT Logo">
                    <span>Student <strong>HUB</strong></span>
                </div>
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

        <main class="main reveal" style="animation-delay:0.1s">
            <div class="main-inner">

                <div class="page-header">
                    <div class="kicker">Event Registration</div>
                    <h1>Register for Event</h1>
                    <p>Use your Student ID to register for an upcoming campus event.</p>
                </div>

                <div class="content-grid reveal" style="animation-delay:0.2s">
                    <div class="panel" style="max-width:560px;">
                        <?php

                        if ($message != "") {

                            $type = strpos($message, "successfully") !== false ? "success" : "error";
                            echo "<div class=\"form-message form-message--{$type}\">";
                            echo htmlspecialchars($message);
                            echo "</div>";

                        }

                        ?>


                        <form
                            method="POST"
                            action="event_register.php">


                            <div class="form-group reveal">

                                <label for="student_id">

                                    Student ID:

                                </label>

                                <input
                                    type="text"
                                    id="student_id"
                                    name="student_id"
                                    placeholder="Example: 25CE015"
                                    maxlength="8"
                                    required>

                            </div>



                            <div class="form-group reveal">

                                <label for="event_id">

                                    Select Event:

                                </label>

                                <select
                                    id="event_id"
                                    name="event_id"
                                    required>


                                    <option value="">

                                        Select Event

                                    </option>


                                    <?php

                                    if ($events !== false) {

                                        while (
                                            $event =
                                            $events->fetch(PDO::FETCH_ASSOC)
                                        ) {

                                            echo "<option value='"
                                                . htmlspecialchars(
                                                    $event["event_id"]
                                                )
                                                . "'>";

                                            echo htmlspecialchars(
                                                $event["event_name"]
                                            );

                                            echo " - ";

                                            echo htmlspecialchars(
                                                $event["event_date"]
                                            );

                                            echo "</option>";

                                        }

                                    }

                                    ?>


                                </select>

                            </div>



                            <div class="btn-group reveal" style="margin-top:10px;">
                                <button type="submit" class="btn btn--accent">Register for Event</button>
                                <button type="reset" class="btn btn--ghost">Reset</button>
                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </main>

    </div>

    <footer>
        <p>&copy; 2026 CHARUSAT University &bull; Student HUB Portal</p>
    </footer>


</body>

</html>
