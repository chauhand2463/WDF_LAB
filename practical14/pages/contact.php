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

    <title>Contact Us | Student HUB Portal</title>

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

    <!-- Contact Section -->

    <main>

        <div class="page-title-wrap reveal">
            <h1>Contact Us</h1>
            <span class="tag">GET IN TOUCH</span>
        </div>

        <div class="page-intro reveal">Have a question or need support? Send us a message and we’ll respond shortly.</div>

        <section class="contact box reveal" style="max-width:640px;margin:0 auto 44px auto;">

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

                    <div class="form-group reveal">

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

        </section>

    </main>

</body>

</html>
