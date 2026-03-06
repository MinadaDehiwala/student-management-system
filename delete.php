<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/activity.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Invalid request token. Delete request rejected.');
    header('Location: search.php');
    exit;
}

$nic = trim((string) ($_POST['nic'] ?? ''));
if ($nic === '') {
    set_flash('error', 'NIC is required for deletion.');
    header('Location: search.php');
    exit;
}

$loadStmt = $pdo->prepare('SELECT nic, name, course, email FROM students WHERE nic = :nic LIMIT 1');
$loadStmt->execute(['nic' => $nic]);
$student = $loadStmt->fetch();

$deleteStmt = $pdo->prepare('DELETE FROM students WHERE nic = :nic');
$deleteStmt->execute(['nic' => $nic]);

if ($deleteStmt->rowCount() > 0) {
    log_activity(
        $pdo,
        'DELETE',
        $nic,
        (string) $_SESSION['admin_username'],
        [
            'name' => $student['name'] ?? '',
            'course' => $student['course'] ?? '',
            'email' => $student['email'] ?? '',
        ]
    );
    set_flash('success', 'Student record deleted successfully.');
} else {
    set_flash('info', 'No student record found for NIC ' . $nic . '.');
}

header('Location: search.php');
exit;
