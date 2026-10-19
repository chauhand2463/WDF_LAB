<?php
require_once "session_check.php";

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


    <!-- Event Registration -->

    <main>

        <div class="page-title-wrap reveal">
            <h1>Event Registration</h1>
            <span class="tag">CAMPUS EVENTS</span>
        </div>

        <div class="page-intro reveal">Register for upcoming campus events using your Student ID.</div>

        <section class="box reveal" style="max-width:640px;margin:0 auto 44px auto;">

            <?php

            if ($message != "") {

                $type = strpos($message, "successfully") !== false ? "success" : "error";
                echo "<p class=\"login-message login-message--{$type}\">";
                echo htmlspecialchars($message);
                echo "</p>";

            }

            ?>


            <form
                method="POST"
                action="event_register.php">


                <!-- Student ID -->

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



                <!-- Event -->

                <div class="form-group reveal">

                    <label for="event_id">

                        Select Event:

                    </label>

                    <br>

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



                <!-- Submit Button -->

                <div class="btn-group reveal" style="margin-top:10px;">
                    <button type="submit" class="btn btn--accent">Register for Event</button>
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
