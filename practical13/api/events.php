<?php
declare(strict_types=1);

require_once __DIR__ . "/config.php";

require_login();

$method = $_SERVER["REQUEST_METHOD"];
$id = (int) ($_GET["id"] ?? 0);

function event_fields(array $row): array
{
    return [
        "event_id" => (int) $row["event_id"],
        "event_name" => (string) $row["event_name"],
        "event_date" => (string) $row["event_date"],
        "description" => (string) ($row["description"] ?? ""),
        "category" => (string) ($row["category"] ?? ""),
        "status" => (string) ($row["status"] ?? "Open"),
        "poster_path" => ($row["poster_path"] ?? null) !== null
            ? basename((string) $row["poster_path"])
            : null
    ];
}

function validate_event_input(array $input): array
{
    $name = trim((string) ($input["event_name"] ?? ""));
    $date = trim((string) ($input["event_date"] ?? ""));
    $description = trim((string) ($input["description"] ?? ""));
    $category = trim((string) ($input["category"] ?? ""));
    $status = (string) ($input["status"] ?? "Open");

    $errors = [];

    if ($name === "" || mb_strlen($name) > 150) {
        $errors["event_name"] = "Enter an event name up to 150 characters.";
    }

    $parsed = DateTime::createFromFormat("!Y-m-d", $date);
    if (!$parsed || $parsed->format("Y-m-d") !== $date) {
        $errors["event_date"] = "Please select a valid event date.";
    }

    if (mb_strlen($description) > 5000) {
        $errors["description"] = "Description is too long.";
    }

    if (mb_strlen($category) > 100) {
        $errors["category"] = "Category must be under 100 characters.";
    }

    if (!in_array($status, ["Open", "Closed"], true)) {
        $errors["status"] = "Invalid event status.";
    }

    return [$name, $date, $description, $category, $status, $errors];
}

try {
    if ($method === "GET") {
        if ($id > 0) {
            $stmt = $conn->prepare("SELECT * FROM events WHERE event_id = :id");
            $stmt->execute([":id" => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                json_error("Event not found.", 404);
            }

            json_response([
                "success" => true,
                "data" => event_fields($row)
            ]);
        }

        $search = trim((string) ($_GET["search"] ?? ""));
        $status = (string) ($_GET["status"] ?? "");

        $where = [];
        $params = [];

        if ($search !== "") {
            $where[] = "(event_name LIKE :search OR category LIKE :search)";
            $params[":search"] = "%" . $search . "%";
        }

        if (in_array($status, ["Open", "Closed"], true)) {
            $where[] = "status = :status";
            $params[":status"] = $status;
        }

        $whereSql = $where ? " WHERE " . implode(" AND ", $where) : "";

        $stmt = $conn->prepare(
            "SELECT * FROM events" . $whereSql . "
             ORDER BY event_date DESC, event_id DESC"
        );
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        json_response([
            "success" => true,
            "data" => array_map("event_fields", $rows),
            "total" => count($rows)
        ]);
    }

    if ($method === "POST") {
        require_admin();
        verify_api_csrf($_SERVER["HTTP_X_CSRF_TOKEN"] ?? null);

        $input = read_json_body();
        [$name, $date, $description, $category, $status, $errors] =
            validate_event_input($input);

        if ($errors) {
            json_error("Please fix the highlighted fields.", 422, ["errors" => $errors]);
        }

        $stmt = $conn->prepare(
            "INSERT INTO events
                (event_name, event_date, description, category, status)
             VALUES
                (:name, :date, :description, :category, :status)"
        );

        $stmt->execute([
            ":name" => $name,
            ":date" => $date,
            ":description" => $description,
            ":category" => $category,
            ":status" => $status
        ]);

        json_response([
            "success" => true,
            "message" => "Event added successfully.",
            "data" => [
                "event_id" => (int) $conn->lastInsertId(),
                "event_name" => $name,
                "event_date" => $date,
                "description" => $description,
                "category" => $category,
                "status" => $status,
                "poster_path" => null
            ]
        ], 201);
    }

    if ($method === "PUT") {
        require_admin();
        verify_api_csrf($_SERVER["HTTP_X_CSRF_TOKEN"] ?? null);

        $input = read_json_body();
        $eventId = (int) ($input["event_id"] ?? $id);

        if ($eventId < 1) {
            json_error("Event ID is required.", 422);
        }

        [$name, $date, $description, $category, $status, $errors] =
            validate_event_input($input);

        if ($errors) {
            json_error("Please fix the highlighted fields.", 422, ["errors" => $errors]);
        }

        $exists = $conn->prepare("SELECT event_id FROM events WHERE event_id = :id");
        $exists->execute([":id" => $eventId]);

        if (!$exists->fetch()) {
            json_error("Event not found.", 404);
        }

        $stmt = $conn->prepare(
            "UPDATE events
             SET event_name = :name,
                 event_date = :date,
                 description = :description,
                 category = :category,
                 status = :status
             WHERE event_id = :id"
        );

        $stmt->execute([
            ":name" => $name,
            ":date" => $date,
            ":description" => $description,
            ":category" => $category,
            ":status" => $status,
            ":id" => $eventId
        ]);

        json_response([
            "success" => true,
            "message" => "Event updated successfully.",
            "data" => [
                "event_id" => $eventId,
                "event_name" => $name,
                "event_date" => $date,
                "description" => $description,
                "category" => $category,
                "status" => $status
            ]
        ]);
    }

    if ($method === "DELETE") {
        require_admin();
        verify_api_csrf($_SERVER["HTTP_X_CSRF_TOKEN"] ?? null);

        $eventId = $id;

        if ($eventId < 1) {
            $input = read_json_body();
            $eventId = (int) ($input["event_id"] ?? 0);
        }

        if ($eventId < 1) {
            json_error("Event ID is required.", 422);
        }

        $stmt = $conn->prepare("DELETE FROM events WHERE event_id = :id");
        $stmt->execute([":id" => $eventId]);

        if ($stmt->rowCount() === 0) {
            json_error("Event not found.", 404);
        }

        json_response([
            "success" => true,
            "message" => "Event deleted successfully.",
            "data" => ["event_id" => $eventId]
        ]);
    }

    json_error("Method not allowed.", 405);
} catch (PDOException $e) {
    error_log("Events API error: " . $e->getMessage());

    if ($e->getCode() === "23000") {
        json_error("Operation failed. This event is linked to registrations.", 409);
    }

    json_error("Database operation failed.", 500);
}
