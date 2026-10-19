<?php
require_once "session_check.php";

if ($_SESSION["role"] !== "student") {
    http_response_code(403);
    exit("Unauthorized access.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Attendance | Student HUB Portal</title>

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

    <!-- Attendance -->

    <main>

        <div class="page-title-wrap reveal">
            <h1>Attendance</h1>
            <span class="tag">CLASS RECORDS</span>
        </div>

        <div class="page-intro reveal">Track your attendance across all registered courses this semester.</div>

        <div class="box reveal" style="max-width:700px;margin:0 auto 44px auto;">

            <table>

                <tr>

                    <th>
                        Subject
                    </th>

                    <th>
                        Attendance
                    </th>

                </tr>


                <tr>

                    <td>
                        Computer Network
                    </td>

                    <td>
                        <span class="status status--paid">85%</span>
                    </td>

                </tr>


                <tr>

                    <td>
                        Java Programming
                    </td>

                    <td>
                        <span class="status status--paid">90%</span>
                    </td>

                </tr>


                <tr>

                    <td>
                        Web Development
                    </td>

                    <td>
                        <span class="status status--pending">80%</span>
                    </td>

                </tr>

            </table>


        </div>

    </main>


</body>

</html>
