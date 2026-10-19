<?php
require_once "session_check.php";
require_once "menu.php";

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

    <title>Student HUB Portal | Dashboard</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

    <!-- =========================================
         HEADER
    ========================================== -->

    <header class="header reveal">

        <div class="header-inner reveal" style="animation-delay:0.05s">

            <div class="logo reveal" style="animation-delay:0.1s">
                <img src="../images/charusat.png" alt="CHARUSAT Logo">
                <span class="logo-wordmark">Student HUB</span>
            </div>

            <button id="menuBtn" class="menu-btn" aria-label="Open Menu">
                ☰
            </button>

            <nav id="navbar">
                <?= render_dynamic_menu("student") ?>
            </nav>

            <div class="logout-text reveal" style="animation-delay:0.2s">
                <a href="logout.php">Logout</a>
            </div>

        </div>

    </header>

    <div id="notification" class="notification reveal" style="animation-delay:0.25s">
        <span><strong>🔔</strong> Welcome back to Student HUB Portal</span>
        <button id="closeNotification" aria-label="Close notification">✕</button>
    </div>

    <main>

        <div class="page-title-wrap reveal">
            <h1>Welcome back.</h1>
            <span class="tag">STUDENT DASHBOARD</span>
        </div>

        <div class="page-intro reveal">
            Your semester at a glance — timetable, attendance, fees,
            results, and the latest announcements.
        </div>

        <div class="top-buttons reveal" style="animation-delay:0.3s">
            <div class="btn-group">
                <button id="themeBtn" class="theme-toggle">
                    ☀️ Light Mode
                </button>
                <button id="openModal" class="btn btn--accent">
                    👤 Student Information
                </button>
            </div>
        </div>

        <div class="dashboard-grid reveal">

            <!-- TIME TABLE -->
            <section>
                <div class="box">
                    <h2>Time Table</h2>
                    <table>
                        <tr>
                            <th>Time</th>
                            <th>Course</th>
                        </tr>
                        <tr>
                            <td>9:10 AM</td>
                            <td>Network</td>
                        </tr>
                        <tr>
                            <td>10:20 AM</td>
                            <td>Java Programming</td>
                        </tr>
                        <tr>
                            <td>11:30 AM</td>
                            <td>WDF</td>
                        </tr>
                    </table>
                </div>
            </section>

            <section>
                <div class="box">
                    <h2>Attendance</h2>
                    <table>
                        <tr>
                            <th>Course</th>
                            <th>Attendance</th>
                        </tr>
                        <tr>
                            <td>Network</td>
                            <td><span class="status status--paid">85%</span></td>
                        </tr>
                        <tr>
                            <td>Java</td>
                            <td><span class="status status--paid">90%</span></td>
                        </tr>
                        <tr>
                            <td>WDF</td>
                            <td><span class="status status--pending">80%</span></td>
                        </tr>
                    </table>
                </div>
            </section>

            <section>
                <div class="box">
                    <h2>Fees Details</h2>
                    <table>
                        <tr>
                            <th>Semester</th>
                            <th>Status</th>
                        </tr>
                        <tr>
                            <td>Semester 1</td>
                            <td><span class="status status--paid">Paid</span></td>
                        </tr>
                        <tr>
                            <td>Semester 2</td>
                            <td><span class="status status--paid">Paid</span></td>
                        </tr>
                    </table>
                </div>
            </section>

            <section>
                <div class="box">
                    <h2>University Result</h2>
                    <table>
                        <tr>
                            <th>Semester</th>
                            <th>SGPA</th>
                        </tr>
                        <tr>
                            <td>Semester 1</td>
                            <td>7.26</td>
                        </tr>
                        <tr>
                            <td>Semester 2</td>
                            <td>7.95</td>
                        </tr>
                    </table>
                </div>
            </section>

        </div>


        <section class="slider-section reveal">
            <h2>📢 Announcements</h2>
            <div class="slider">
                <button id="prevBtn" aria-label="Previous announcement">❮</button>
                <div class="slide">
                    <h3 id="slideTitle">Java Exam</h3>
                    <p id="slideText">Java Programming examination will be conducted on Monday.</p>
                </div>
                <button id="nextBtn" aria-label="Next announcement">❯</button>
            </div>
        </section>


        <section class="faq-section reveal">
            <h2>❓ Frequently Asked Questions</h2>

            <div class="faq">
                <button class="faq-question">
                    <span>How can I check my attendance?</span>
                    <span>+</span>
                </button>
                <div class="faq-answer">
                    <p>Go to the Attendance section from the navigation menu.</p>
                </div>
            </div>

            <div class="faq">
                <button class="faq-question">
                    <span>How can I check my result?</span>
                    <span>+</span>
                </button>
                <div class="faq-answer">
                    <p>Click on the Result option in the navigation menu.</p>
                </div>
            </div>

            <div class="faq">
                <button class="faq-question">
                    <span>How can I check my courses?</span>
                    <span>+</span>
                </button>
                <div class="faq-answer">
                    <p>Click on Courses to view all registered courses.</p>
                </div>
            </div>
        </section>

    </main>


    <div id="modal" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">

        <div class="modal-content">

            <button id="closeModal" class="close-modal" aria-label="Close">✕</button>

            <h2 id="modalTitle">Student Information</h2>

            <p><strong>Name:</strong> <span id="studentName">Loading…</span></p>
            <p><strong>Course:</strong> <span id="studentCourse">Loading…</span></p>
            <p><strong>University:</strong> <span id="studentUniversity">Loading…</span></p>
            <p><strong>Semester:</strong> <span id="studentSemester">Loading…</span></p>
            <p><strong>Email:</strong> <span id="studentEmail">Loading…</span></p>
            <p><strong>Mobile:</strong> <span id="studentMobile">Loading…</span></p>

        </div>
    </div>


    <footer>
        <h1>@2026 Student HUB Portal</h1>
    </footer>


    <script src="../js/index.js"></script>
    <script src="../js/json-test.js"></script>

</body>

</html>
