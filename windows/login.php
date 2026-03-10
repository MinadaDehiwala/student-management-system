<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/activity.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid request token. Please try again.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            $error = 'Username and password are required.';
        } else {
            $stmt = $pdo->prepare('SELECT id, username, password_hash FROM admins WHERE username = :username LIMIT 1');
            $stmt->execute(['username' => $username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = (int) $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                log_activity($pdo, 'LOGIN', null, (string) $admin['username'], [
                    'source' => 'login_form',
                ]);
                set_flash('success', 'Welcome back, ' . $admin['username'] . '.');
                header('Location: dashboard.php');
                exit;
            }

            $error = 'Invalid username or password.';
        }
    }
}

$pageTitle = 'Admin Login';
$showNav = false;
require __DIR__ . '/includes/header.php';
?>
<section class="auth-wrap">
    <div class="auth-card">
        <figure class="auth-hero-image">
            <picture>
                <source srcset="assets/images/image3-hero.webp" type="image/webp">
                <img src="assets/images/image3-hero.jpg" alt="Campus graduates celebrating after a ceremony" loading="eager" decoding="async">
            </picture>
        </figure>
        <div class="auth-badge">Admin Portal</div>
        <h1>Campus Control Center</h1>
        <p class="muted">Sign in to manage student records, analytics, and activity insights.</p>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php" data-validate-form novalidate>
            <?= csrf_input() ?>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?= e($username) ?>" autocomplete="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" autocomplete="current-password" required>
            </div>

            <button type="submit" class="btn btn-primary full-width">Login</button>
        </form>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
