<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/csrf.php';

$nic = '';
$student = null;
$error = '';
$searched = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid request token. Please try again.';
    } else {
        $nic = trim((string) ($_POST['nic'] ?? ''));
        $searched = true;
    }
} elseif (isset($_GET['nic'])) {
    $nic = trim((string) $_GET['nic']);
    $searched = true;
}

if ($searched && $nic === '' && $error === '') {
    $error = 'Please enter a NIC number to search.';
}

if ($searched && $nic !== '' && $error === '') {
    $stmt = $pdo->prepare('SELECT nic, name, gender, address, contact, email, course, created_at FROM students WHERE nic = :nic LIMIT 1');
    $stmt->execute(['nic' => $nic]);
    $student = $stmt->fetch();
}

$pageTitle = 'Search Student';
$showNav = true;
require __DIR__ . '/includes/header.php';
?>
<section class="page-header">
    <div>
        <h1>Quick NIC Search</h1>
        <p>Find and manage student records instantly by NIC.</p>
    </div>
    <div class="quick-links">
        <a class="btn btn-secondary" href="students.php">Open Full Directory</a>
    </div>
</section>

<section class="form-card compact">
    <form method="post" action="search.php" class="inline-form" data-validate-form novalidate>
        <?= csrf_input() ?>
        <div class="form-group grow">
            <label for="nic">NIC Number</label>
            <input type="text" id="nic" name="nic" value="<?= e($nic) ?>" required>
        </div>
        <div class="actions align-end">
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
    </form>
</section>

<?php if ($error !== ''): ?>
    <div class="alert alert-error" role="alert"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($searched && $error === ''): ?>
    <?php if ($student): ?>
        <section class="result-card">
            <h2>Student Details</h2>
            <table class="data-table">
                <tr><th>NIC</th><td><?= e($student['nic']) ?></td></tr>
                <tr><th>Name</th><td><?= e($student['name']) ?></td></tr>
                <tr><th>Gender</th><td><?= e($student['gender']) ?></td></tr>
                <tr><th>Address</th><td><?= e($student['address']) ?></td></tr>
                <tr><th>Contact</th><td><?= e($student['contact']) ?></td></tr>
                <tr><th>Email</th><td><?= e($student['email']) ?></td></tr>
                <tr><th>Course</th><td><?= e($student['course']) ?></td></tr>
                <tr><th>Created At</th><td><?= e($student['created_at']) ?></td></tr>
            </table>

            <div class="actions">
                <a class="btn btn-secondary" href="edit.php?nic=<?= urlencode($student['nic']) ?>">Edit</a>
                <form method="post" action="delete.php" class="inline js-confirm-delete" data-confirm-message="Delete this student record? This cannot be undone.">
                    <?= csrf_input() ?>
                    <input type="hidden" name="nic" value="<?= e($student['nic']) ?>">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </section>
    <?php else: ?>
        <div class="alert alert-info" role="status">
            No student found for NIC <strong><?= e($nic) ?></strong>.
        </div>
    <?php endif; ?>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
