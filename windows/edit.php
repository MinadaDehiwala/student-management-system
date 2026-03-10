<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/constants.php';
require_once __DIR__ . '/includes/activity.php';

$courses = CAMPUS_COURSES;
$genders = CAMPUS_GENDERS;

$nic = trim((string) ($_GET['nic'] ?? $_POST['nic'] ?? ''));
if ($nic === '') {
    set_flash('error', 'NIC is required to edit a student.');
    header('Location: search.php');
    exit;
}

$loadStmt = $pdo->prepare('SELECT nic, name, gender, address, contact, email, course FROM students WHERE nic = :nic LIMIT 1');
$loadStmt->execute(['nic' => $nic]);
$student = $loadStmt->fetch();

if (!$student) {
    set_flash('error', 'Student not found.');
    header('Location: search.php');
    exit;
}

$errors = [];
$formData = $student;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid request token. Please refresh and try again.';
    } else {
        $formData['name'] = trim((string) ($_POST['name'] ?? ''));
        $formData['gender'] = trim((string) ($_POST['gender'] ?? ''));
        $formData['address'] = trim((string) ($_POST['address'] ?? ''));
        $formData['contact'] = trim((string) ($_POST['contact'] ?? ''));
        $formData['email'] = trim((string) ($_POST['email'] ?? ''));
        $formData['course'] = trim((string) ($_POST['course'] ?? ''));

        if ($formData['name'] === '' || $formData['gender'] === '' || $formData['address'] === '' || $formData['contact'] === '' || $formData['email'] === '' || $formData['course'] === '') {
            $errors[] = 'All fields are required.';
        }

        if (!in_array($formData['gender'], $genders, true)) {
            $errors[] = 'Please select a valid gender.';
        }

        if (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }

        if (!preg_match('/^[0-9+\-\s]{7,20}$/', $formData['contact'])) {
            $errors[] = 'Contact number must be 7-20 characters and contain only digits, spaces, + or -.';
        }

        if (!in_array($formData['course'], $courses, true)) {
            $errors[] = 'Please select a valid course.';
        }

        if (!$errors) {
            $changedFields = [];
            foreach (['name', 'gender', 'address', 'contact', 'email', 'course'] as $field) {
                if ((string) $student[$field] !== (string) $formData[$field]) {
                    $changedFields[$field] = [
                        'from' => (string) $student[$field],
                        'to' => (string) $formData[$field],
                    ];
                }
            }

            $updateStmt = $pdo->prepare(
                'UPDATE students SET name = :name, gender = :gender, address = :address, contact = :contact, email = :email, course = :course WHERE nic = :nic'
            );

            $updateStmt->execute([
                'name' => $formData['name'],
                'gender' => $formData['gender'],
                'address' => $formData['address'],
                'contact' => $formData['contact'],
                'email' => $formData['email'],
                'course' => $formData['course'],
                'nic' => $nic,
            ]);

            if ($changedFields !== []) {
                log_activity(
                    $pdo,
                    'UPDATE',
                    $nic,
                    (string) $_SESSION['admin_username'],
                    [
                        'changed_fields' => $changedFields,
                    ]
                );
            }

            set_flash('success', 'Student details updated successfully.');
            header('Location: search.php?nic=' . urlencode($nic));
            exit;
        }
    }
}

$pageTitle = 'Edit Student';
$showNav = true;
require __DIR__ . '/includes/header.php';
?>
<section class="page-header">
    <h1>Edit Student</h1>
    <p>Update details for NIC <?= e($nic) ?>.</p>
</section>

<figure class="context-image-card context-image-card-compact">
    <picture>
        <source srcset="assets/images/image3-card.webp" type="image/webp">
        <img src="assets/images/image3-card.jpg" alt="Campus administration staff reviewing student data" loading="lazy" decoding="async">
    </picture>
</figure>

<?php if ($errors): ?>
    <div class="alert alert-error" role="alert">
        <?= e(implode(' ', $errors)) ?>
    </div>
<?php endif; ?>

<section class="form-card">
    <form method="post" action="edit.php" class="form-grid" data-validate-form novalidate>
        <?= csrf_input() ?>
        <input type="hidden" name="nic" value="<?= e($nic) ?>">

        <div class="form-group">
            <label for="nic_view">NIC Number</label>
            <input type="text" id="nic_view" value="<?= e($nic) ?>" disabled>
        </div>

        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?= e($formData['name']) ?>" required>
        </div>

        <div class="form-group">
            <label for="gender">Gender</label>
            <select id="gender" name="gender" required>
                <?php foreach ($genders as $gender): ?>
                    <option value="<?= e($gender) ?>" <?= $formData['gender'] === $gender ? 'selected' : '' ?>><?= e($gender) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="course">Course</label>
            <select id="course" name="course" required>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= e($course) ?>" <?= $formData['course'] === $course ? 'selected' : '' ?>><?= e($course) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group full-span">
            <label for="address">Address</label>
            <textarea id="address" name="address" rows="3" required><?= e($formData['address']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="contact">Contact Number</label>
            <input type="text" id="contact" name="contact" value="<?= e($formData['contact']) ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= e($formData['email']) ?>" required>
        </div>

        <div class="actions full-span">
            <button type="submit" class="btn btn-primary">Update Student</button>
            <a class="btn btn-secondary" href="search.php?nic=<?= urlencode($nic) ?>">Cancel</a>
        </div>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
