<?php

declare(strict_types=1);

function log_activity(PDO $pdo, string $action, ?string $studentNic, string $adminUsername, array $details = []): void
{
    $allowedActions = ['CREATE', 'UPDATE', 'DELETE', 'LOGIN'];
    if (!in_array($action, $allowedActions, true) || $adminUsername === '') {
        return;
    }

    $detailsText = null;
    if ($details !== []) {
        $encoded = json_encode($details, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (is_string($encoded)) {
            $detailsText = $encoded;
        }
    }

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO student_activity (action, student_nic, admin_username, details_text)
             VALUES (:action, :student_nic, :admin_username, :details_text)'
        );

        $stmt->execute([
            'action' => $action,
            'student_nic' => $studentNic !== null ? trim($studentNic) : null,
            'admin_username' => $adminUsername,
            'details_text' => $detailsText,
        ]);
    } catch (PDOException $exception) {
        // Activity logging should not block core user operations.
    }
}
