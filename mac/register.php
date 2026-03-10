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

$errors = [];
$formData = [
    'nic' => '',
    'name' => '',
    'gender' => '',
    'address' => '',
    'contact' => '',
    'email' => '',
    'course' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid request token. Please refresh and try again.';
    } else {
        foreach ($formData as $key => $_unused) {
            $formData[$key] = trim((string) ($_POST[$key] ?? ''));
        }

        if ($formData['nic'] === '' || $formData['name'] === '' || $formData['gender'] === '' || $formData['address'] === '' || $formData['contact'] === '' || $formData['email'] === '' || $formData['course'] === '') {
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
            $existsStmt = $pdo->prepare('SELECT nic FROM students WHERE nic = :nic LIMIT 1');
            $existsStmt->execute(['nic' => $formData['nic']]);
            if ($existsStmt->fetch()) {
                $errors[] = 'This NIC is already registered.';
            }
        }

        if (!$errors) {
            $insertStmt = $pdo->prepare(
                'INSERT INTO students (nic, name, gender, address, contact, email, course) VALUES (:nic, :name, :gender, :address, :contact, :email, :course)'
            );

            $insertStmt->execute([
                'nic' => $formData['nic'],
                'name' => $formData['name'],
                'gender' => $formData['gender'],
                'address' => $formData['address'],
                'contact' => $formData['contact'],
                'email' => $formData['email'],
                'course' => $formData['course'],
            ]);

            log_activity(
                $pdo,
                'CREATE',
                $formData['nic'],
                (string) $_SESSION['admin_username'],
                [
                    'name' => $formData['name'],
                    'course' => $formData['course'],
                    'email' => $formData['email'],
                ]
            );

            set_flash('success', 'Student registered successfully.');
            header('Location: register.php');
            exit;
        }
    }
}

$pageTitle = 'Register Student';
$showNav = true;
require __DIR__ . '/includes/header.php';
?>
<section class="page-header">
    <h1>Register Student</h1>
    <p>Add a new student record to the system.</p>
</section>

<figure class="context-image-card context-image-card-compact">
    <picture>
        <source srcset="assets/images/image2-card.webp" type="image/webp">
        <img src="assets/images/image2-card.jpg" alt="Students attending a classroom session" loading="lazy" decoding="async">
    </picture>
</figure>

<?php if ($errors): ?>
    <div class="alert alert-error" role="alert">
        <?= e(implode(' ', $errors)) ?>
    </div>
<?php endif; ?>

<section class="form-card">
    <form method="post" action="register.php" class="form-grid" data-validate-form novalidate>
        <?= csrf_input() ?>

        <div class="form-group">
            <label for="nic">NIC Number</label>
            <input type="text" id="nic" name="nic" value="<?= e($formData['nic']) ?>" required>
        </div>

        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?= e($formData['name']) ?>" required>
        </div>

        <div class="form-group">
            <label for="gender">Gender</label>
            <select id="gender" name="gender" required>
                <option value="">Select</option>
                <?php foreach ($genders as $gender): ?>
                    <option value="<?= e($gender) ?>" <?= $formData['gender'] === $gender ? 'selected' : '' ?>><?= e($gender) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="course">Course</label>
            <select id="course" name="course" required>
                <option value="">Select</option>
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
            <button type="submit" class="btn btn-primary">Save Student</button>
        </div>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
