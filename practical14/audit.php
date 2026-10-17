<?php

function log_audit(
    PDO $conn,
    string $action,
    ?string $entity = null,
    ?string $entityId = null,
    ?string $details = null
): void {
    try {
        $stmt = $conn->prepare(
            "INSERT INTO audit_logs
                (student_id, role, action, entity, entity_id, details, ip_address)
             VALUES
                (:student_id, :role, :action, :entity, :entity_id, :details, :ip)"
        );

        $stmt->execute([
            ":student_id" => $_SESSION["student_id"] ?? null,
            ":role" => $_SESSION["role"] ?? null,
            ":action" => substr($action, 0, 100),
            ":entity" => $entity !== null ? substr($entity, 0, 50) : null,
            ":entity_id" => $entityId !== null ? substr($entityId, 0, 50) : null,
            ":details" => $details,
            ":ip" => $_SERVER["REMOTE_ADDR"] ?? null
        ]);
    } catch (Throwable $e) {
        error_log("Audit logging failed: " . $e->getMessage());
    }
}

?>
