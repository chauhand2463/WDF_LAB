<?php

function get_current_script(): string
{
    $script = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '';
    return basename($script);
}

function build_dynamic_menu(string $role, string $base = ""): array
{
    $menus = [
        "student" => [
            ["label" => "Dashboard", "href" => $base . "index.php", "file" => "index.php"],
            ["label" => "Profile", "href" => $base . "profile.php", "file" => "profile.php"],
            ["label" => "Attendance", "href" => $base . "attendance.php", "file" => "attendance.php"],
            ["label" => "Assignment", "href" => $base . "assignment.php", "file" => "assignment.php"],
            ["label" => "Courses", "href" => $base . "courses.php", "file" => "courses.php"],
            ["label" => "Event Registration", "href" => $base . "event_register.php", "file" => "event_register.php"],
            ["label" => "Contact", "href" => $base . "contact.php", "file" => "contact.php"]
        ],
        "admin" => [
            ["label" => "Dashboard", "href" => $base . "Admin.php", "file" => "Admin.php"],
            ["label" => "Manage Students", "href" => $base . "adminpages/manage_students.php", "file" => "manage_students.php"],
            ["label" => "Manage Events", "href" => $base . "adminpages/events.php", "file" => "events.php"],
            ["label" => "AJAX CRUD", "href" => $base . "ajax_crud.php", "file" => "ajax_crud.php"],
            ["label" => "Audit Log", "href" => $base . "adminpages/audit_logs.php", "file" => "audit_logs.php"]
        ]
    ];

    $items = $menus[$role] ?? [];
    $items[] = ["label" => "Logout", "href" => $base . "logout.php", "file" => "logout.php"];

    return $items;
}

function render_dynamic_menu(string $role, string $base = "", ?string $activeFile = null): string
{
    $current = $activeFile ?? get_current_script();
    if ($current === "" || $current === "practical14") {
        $current = ($role === "admin") ? "Admin.php" : "index.php";
    }

    $html = "";

    foreach (build_dynamic_menu($role, $base) as $item) {
        $href = htmlspecialchars($item["href"], ENT_QUOTES, "UTF-8");
        $label = htmlspecialchars($item["label"], ENT_QUOTES, "UTF-8");
        $targetFile = $item["file"] ?? basename($item["href"]);

        $isActive = (strcasecmp($current, $targetFile) === 0);
        $attr = $isActive ? ' aria-current="page" class="active"' : '';

        $html .= '<li><a href="' . $href . '"' . $attr . '>' . $label . '</a></li>' . "\n";
    }

    return $html;
}
?>
