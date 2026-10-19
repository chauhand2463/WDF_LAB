<?php

function build_dynamic_menu(string $role, string $base = ""): array
{
    $menus = [
        "student" => [
            ["label" => "Dashboard", "href" => $base . "index.php"],
            ["label" => "Profile", "href" => $base . "profile.php"],
            ["label" => "Attendance", "href" => $base . "attendance.php"],
            ["label" => "Assignment", "href" => $base . "assignment.php"],
            ["label" => "Courses", "href" => $base . "courses.php"],
            ["label" => "Event Registration", "href" => $base . "event_register.php"],
            ["label" => "Contact", "href" => $base . "contact.php"]
        ],
        "admin" => [
            ["label" => "Dashboard", "href" => $base . "Admin.php"],
            ["label" => "Manage Students", "href" => $base . "adminpages/manage_students.php"],
            ["label" => "Manage Events", "href" => $base . "adminpages/events.php"],
            ["label" => "AJAX CRUD", "href" => $base . "ajax_crud.php"],
            ["label" => "Audit Log", "href" => $base . "adminpages/audit_logs.php"]
        ]
    ];

    $items = $menus[$role] ?? [];
    $items[] = ["label" => "Logout", "href" => $base . "logout.php"];

    return $items;
}

function render_dynamic_menu(string $role, string $base = ""): string
{
    $html = "<ul class=\"nav-list\">";

    foreach (build_dynamic_menu($role, $base) as $item) {
        $href = htmlspecialchars($item["href"], ENT_QUOTES, "UTF-8");
        $label = htmlspecialchars($item["label"], ENT_QUOTES, "UTF-8");
        $current = $item["label"] === "Dashboard" ? ' aria-current="page"' : "";

        $html .= "<li><a href=\"" . $href . "\"" . $current . ">" . $label .
            "</a></li>";
    }

    $html .= "</ul>";

    return $html;
}

?>
