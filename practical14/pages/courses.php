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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Courses | Student HUB Portal</title>

    <link rel="stylesheet" href="../css/style.css">
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
                    <span class="kicker">Courses</span>
                    <h1>Enrolled Subjects</h1>
                    <p style="margin-top:6px;color:var(--ink-secondary);font-size:0.9rem;">
                        Browse your registered courses for this semester
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
                    <div class="kicker">Course Catalogue</div>
                    <h1>Courses</h1>
                    <p>Select a course to view its details.</p>
                </div>

                <div class="content-grid reveal" style="animation-delay:0.2s">
                    <div class="panel">
                        <form>
                            <div class="form-group">
                                <label for="Courses">Select Course</label>
                                <select name="Courses" id="Courses" required>
                                    <option value="---Select---">---Select---</option>
                                    <option value="c++">C++</option>
                                    <option value="c">C</option>
                                    <option value="Java">Java</option>
                                    <option value="Java Script">Java Script</option>
                                    <option value="Python">Python</option>
                                    <option value="html">HTML</option>
                                    <option value="php">PHP</option>
                                    <option value="CSS">CSS</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="content-grid content-grid--two reveal" style="animation-delay:0.3s">
                    <div class="panel">
                        <h2>Web Designing</h2>
                        <p>Basics of HTML, CSS, and JavaScript</p>
                    </div>
                    <div class="panel">
                        <h2>Data Structure</h2>
                        <p>Array, Linked list, Time &amp; space complexity</p>
                    </div>
                    <div class="panel">
                        <h2>OOP</h2>
                        <p>Logic building and simple applications</p>
                    </div>
                </div>

            </div>
        </main>

    </div>

    <footer>
        <p>&copy; 2026 CHARUSAT University &bull; Student HUB Portal</p>
    </footer>

    <script src="../js/index.js"></script>

</body>

</html>
