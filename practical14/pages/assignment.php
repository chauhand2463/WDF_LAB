<?php
require_once __DIR__ . "/session_check.php";
require_once __DIR__ . "/menu.php";

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

    <link
        rel="stylesheet"
        href="../css/style.css">

    <title>Assignments | Student HUB Portal</title>

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

    <!-- Assignment Section -->

    <main>

        <div class="page-title-wrap reveal">
            <h1>Assignments</h1>
            <span class="tag">COURSEWORK</span>
        </div>

        <div class="page-intro reveal">View your upcoming assignments, due dates, and submission status.</div>

        <div class="assignment-card box reveal" style="max-width:900px;margin:0 auto 44px auto;">

            <h2>Assignments</h2>

            <table>

                <tr>

                    <th>
                        Subject
                    </th>

                    <th>
                        Assignment
                    </th>

                    <th>
                        Due Date
                    </th>

                    <th>
                        Status
                    </th>

                </tr>


                <tr>

                    <td>
                        Mathematics
                    </td>

                    <td>
                        Assignment 1
                    </td>

                    <td>
                        20-Aug-2026
                    </td>

                    <td>
                        <span class="status status--pending">Pending</span>
                    </td>

                </tr>


                <tr>

                    <td>
                        Computer Engineering
                    </td>

                    <td>
                        Assignment 2
                    </td>

                    <td>
                        22-Aug-2026
                    </td>

                    <td>
                        <span class="status status--paid">Submitted</span>
                    </td>

                </tr>


                <tr>

                    <td>
                        Physics
                    </td>

                    <td>
                        Assignment 3
                    </td>

                    <td>
                        25-Aug-2026
                    </td>

                    <td>
                        <span class="status status--pending">Pending</span>
                    </td>

                </tr>


                <tr>

                    <td>
                        English
                    </td>

                    <td>
                        Assignment 1
                    </td>

                    <td>
                        28-Aug-2026
                    </td>

                    <td>
                        <span class="status status--paid">Submitted</span>
                    </td>

                </tr>

            </table>

        </div>

    </main>

    </body>

</html>
