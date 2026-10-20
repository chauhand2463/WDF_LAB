<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Choose Role | Student HUB</title>

    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <div class="auth-shell reveal">

        <div class="auth-card" style="max-width: 520px;">

            <div class="logo">
                <img src="../images/charusat.png" alt="CHARUSAT Logo">
                <span class="logo-wordmark">Student <strong>HUB</strong></span>
            </div>

            <h1>Choose Your Role</h1>
            <p>Select your account type to proceed with registration.</p>

            <div style="display: flex; flex-direction: column; gap: 16px; margin: 24px 0;">

                <!-- Student Option -->
                <a href="register.php" style="display: block; padding: 20px; border: 1px solid var(--border); border-radius: var(--radius); background: var(--surface); transition: all var(--duration-ui) var(--ease-ui); text-align: left;" onmouseover="this.style.borderColor='var(--accent)'; this.style.boxShadow='var(--shadow-soft)'" onmouseout="this.style.borderColor='var(--border)'; this.style.boxShadow='none'">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                        <span style="font-weight: 700; font-size: 1.1rem; color: var(--ink);">Student Account</span>
                        <span class="status status--success" style="font-size: 0.75rem;">Instant Access</span>
                    </div>
                    <p style="color: var(--ink-secondary); font-size: 0.88rem; line-height: 1.5; margin: 0;">
                        For currently enrolled university students. Access your semester timetable, coursework submissions, attendance logs, and event registrations.
                    </p>
                </a>

                <!-- Admin Option -->
                <a href="admin_register.php" style="display: block; padding: 20px; border: 1px solid var(--border); border-radius: var(--radius); background: var(--surface); transition: all var(--duration-ui) var(--ease-ui); text-align: left;" onmouseover="this.style.borderColor='var(--accent)'; this.style.boxShadow='var(--shadow-soft)'" onmouseout="this.style.borderColor='var(--border)'; this.style.boxShadow='none'">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                        <span style="font-weight: 700; font-size: 1.1rem; color: var(--ink);">Administrator / Faculty</span>
                        <span class="status status--warning" style="font-size: 0.75rem;">Admin Privileges</span>
                    </div>
                    <p style="color: var(--ink-secondary); font-size: 0.88rem; line-height: 1.5; margin: 0;">
                        For departmental coordinators and faculty. Manage student rosters, organize events, perform AJAX CRUD operations, and view audit logs.
                    </p>
                </a>

            </div>

            <div style="text-align: center; font-size: 0.9rem; color: var(--ink-secondary); border-top: 1px solid var(--border); padding-top: 18px;">
                Already have an account? <a href="login.php" style="color: var(--accent); font-weight: 600;">Sign in here</a>
            </div>

        </div>

    </div>

    <footer>
        <p>&copy; 2026 CHARUSAT University &bull; Student HUB Portal</p>
    </footer>

</body>

</html>