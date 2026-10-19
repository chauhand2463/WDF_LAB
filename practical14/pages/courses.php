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

    <title>Courses | Student HUB Portal</title>

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

    <!-- Courses -->

    <main>

        <div class="page-title-wrap reveal">
            <h1>Courses</h1>
            <span class="tag">ENROLLED SUBJECTS</span>
        </div>

        <div class="page-intro reveal">Browse your registered courses and view details for each subject.</div>

        <div class="box reveal" style="max-width:700px;margin:0 auto 30px auto;">

            <label for="Courses" style="font-family:var(--font-mono);font-weight:700;font-size:0.82rem;text-transform:uppercase;letter-spacing:0.06em;color:var(--accent);display:block;margin-bottom:10px;">
                Select a course
            </label>

            <select
                name="Courses"
                id="Courses"
                required
                style="width:100%;padding:12px 16px;background:var(--surface-elevated);border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text);font-family:var(--font-body);font-size:0.95rem;">

                <option value="---Select---">
                    ---Select---
                </option>

                <option value="c++">
                    C++
                </option>

                <option value="c">
                    C
                </option>

                <option value="Java">
                    Java
                </option>

                <option value="Java Script">
                    Java Script
                </option>

                <option value="Python">
                    Python
                </option>

                <option value="html">
                    HTML
                </option>

                <option value="php">
                    PHP
                </option>

                <option value="CSS">
                    CSS
                </option>

            </select>

        </div>

        <!-- Course Cards -->

        <div class="dashboard-grid reveal" style="max-width:900px;margin:0 auto 44px auto;grid-template-columns:repeat(3,1fr);gap:20px;">

            <div class="card reveal" style="animation-delay:0.1s;text-align:center;">
                <h3>Web Designing</h3>
                <p>Basics of HTML, CSS, and JavaScript</p>
            </div>

            <div class="card reveal" style="animation-delay:0.2s;text-align:center;">
                <h3>Data Structure</h3>
                <p>Array, Linked list, Time &amp; space complexity</p>
            </div>

            <div class="card reveal" style="animation-delay:0.3s;text-align:center;">
                <h3>OOP</h3>
                <p>Logic building and simple applications</p>
            </div>

        </div>

    </main>

    </body>

</html>
