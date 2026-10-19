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

    <title>Contact Us | Student HUB Portal</title>

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
                    <span class="kicker">Contact</span>
                    <h1>Contact Us</h1>
                    <p style="margin-top:6px;color:var(--ink-secondary);font-size:0.9rem;">
                        Send us a message and we&rsquo;ll respond shortly
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
                    <div class="kicker">Get in Touch</div>
                    <h1>Contact Us</h1>
                    <p>Have a question or need support? Send us a message.</p>
                </div>

                <div class="content-grid reveal" style="animation-delay:0.2s">
                    <div class="panel" style="max-width:640px;">
                        <div class="contact-container">

                            <form>

                                <div class="form-group reveal">

                                    <input
                                        type="text"
                                        placeholder="Your Name"
                                        required>

                                </div>

                                <div class="form-group reveal">

                                    <input
                                        type="email"
                                        placeholder="Your Email"
                                        required>

                                </div>

                                <div class="form-group reveal">

                                    <input
                                        type="text"
                                        placeholder="Subject"
                                        required>

                                </div>

                                <div class="form-group reveal full-width">

                                    <textarea
                                        rows="6"
                                        placeholder="Your Message"
                                        required></textarea>

                                </div>

                                <div class="btn-group reveal" style="margin-top:8px;">
                                    <button type="submit" class="btn btn--accent">Send Message</button>
                                </div>

                            </form>

                        </div>
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
