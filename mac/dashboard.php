<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/constants.php';

$totalStudents = (int) $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
$newLast7Days = (int) $pdo->query('SELECT COUNT(*) FROM students WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)')->fetchColumn();

$courseDistribution = [];
foreach (CAMPUS_COURSES as $courseName) {
    $courseDistribution[$courseName] = 0;
}

$courseStmt = $pdo->query('SELECT course, COUNT(*) AS total FROM students GROUP BY course ORDER BY total DESC, course ASC');
while ($row = $courseStmt->fetch()) {
    $course = (string) $row['course'];
    $count = (int) $row['total'];
    if (!array_key_exists($course, $courseDistribution)) {
        $courseDistribution[$course] = 0;
    }
    $courseDistribution[$course] = $count;
}
arsort($courseDistribution);

$genderDistribution = [];
foreach (CAMPUS_GENDERS as $genderName) {
    $genderDistribution[$genderName] = 0;
}

$genderStmt = $pdo->query('SELECT gender, COUNT(*) AS total FROM students GROUP BY gender ORDER BY total DESC, gender ASC');
while ($row = $genderStmt->fetch()) {
    $gender = (string) $row['gender'];
    $count = (int) $row['total'];
    if (!array_key_exists($gender, $genderDistribution)) {
        $genderDistribution[$gender] = 0;
    }
    $genderDistribution[$gender] = $count;
}

$topCourse = 'No data yet';
if ($courseDistribution !== []) {
    $topCourse = (string) array_key_first($courseDistribution);
    if ($courseDistribution[$topCourse] === 0) {
        $topCourse = 'No data yet';
    }
}

$recentStudentsStmt = $pdo->query(
    'SELECT nic, name, course, created_at
     FROM students
     ORDER BY created_at DESC
     LIMIT 5'
);
$recentStudents = $recentStudentsStmt->fetchAll();

$activityStmt = $pdo->query(
    'SELECT action, student_nic, admin_username, details_text, created_at
     FROM student_activity
     ORDER BY created_at DESC
     LIMIT 8'
);
$recentActivity = $activityStmt->fetchAll();

function activity_badge_class(string $action): string
{
    return match ($action) {
        'CREATE' => 'badge-create',
        'UPDATE' => 'badge-update',
        'DELETE' => 'badge-delete',
        'LOGIN' => 'badge-login',
        default => 'badge-default',
    };
}

$pageTitle = 'Dashboard';
$showNav = true;
require __DIR__ . '/includes/header.php';
?>
<section class="page-header dashboard-header">
    <div>
        <h1>Campus Insights Dashboard</h1>
        <p>Welcome back, <?= e((string) $_SESSION['admin_username']) ?>. Track registrations and recent activity in one place.</p>
    </div>
    <div class="quick-links">
        <a class="btn btn-secondary" href="register.php">Register Student</a>
        <a class="btn btn-secondary" href="students.php">Open Directory</a>
    </div>
</section>

<section class="dashboard-hero-image">
    <picture>
        <source srcset="assets/images/image4-hero.webp" type="image/webp">
        <img src="assets/images/image4-hero.jpg" alt="University campus buildings and student walkways" loading="eager" decoding="async">
    </picture>
</section>

<section class="stats-grid">
    <article class="stat-card">
        <p class="stat-label">Total Students</p>
        <p class="stat-number" data-count="<?= e((string) $totalStudents) ?>">0</p>
    </article>

    <article class="stat-card">
        <p class="stat-label">New in Last 7 Days</p>
        <p class="stat-number" data-count="<?= e((string) $newLast7Days) ?>">0</p>
    </article>

    <article class="stat-card">
        <p class="stat-label">Top Course</p>
        <p class="stat-copy"><?= e($topCourse) ?></p>
    </article>

    <article class="stat-card">
        <p class="stat-label">Tracked Activity Events</p>
        <p class="stat-number" data-count="<?= e((string) count($recentActivity)) ?>">0</p>
    </article>
</section>

<section class="insights-grid">
    <article class="form-card chart-card">
        <h2>Course Distribution</h2>
        <div class="bar-chart">
            <?php foreach ($courseDistribution as $courseName => $count): ?>
                <?php $pct = $totalStudents > 0 ? (($count / $totalStudents) * 100) : 0; ?>
                <div class="bar-row">
                    <div class="bar-meta">
                        <span><?= e((string) $courseName) ?></span>
                        <strong><?= e((string) $count) ?></strong>
                    </div>
                    <div class="bar-track" role="img" aria-label="<?= e((string) $courseName) ?> share <?= e((string) round($pct, 1)) ?> percent">
                        <span class="bar-fill" style="--bar-width: <?= e((string) round($pct, 2)) ?>%;"></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="form-card chart-card">
        <h2>Gender Distribution</h2>
        <div class="bar-chart alt">
            <?php foreach ($genderDistribution as $genderName => $count): ?>
                <?php $pct = $totalStudents > 0 ? (($count / $totalStudents) * 100) : 0; ?>
                <div class="bar-row">
                    <div class="bar-meta">
                        <span><?= e((string) $genderName) ?></span>
                        <strong><?= e((string) $count) ?></strong>
                    </div>
                    <div class="bar-track" role="img" aria-label="<?= e((string) $genderName) ?> share <?= e((string) round($pct, 1)) ?> percent">
                        <span class="bar-fill" style="--bar-width: <?= e((string) round($pct, 2)) ?>%;"></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </article>
</section>

<section class="content-grid">
    <article class="result-card">
        <div class="card-head">
            <h2>Recent Registrations</h2>
            <a class="btn btn-ghost" href="students.php">View All</a>
        </div>

        <?php if ($recentStudents): ?>
            <div class="table-wrap">
                <table class="data-table data-table-wide">
                    <thead>
                        <tr>
                            <th>NIC</th>
                            <th>Name</th>
                            <th>Course</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentStudents as $student): ?>
                            <tr>
                                <td><?= e((string) $student['nic']) ?></td>
                                <td><?= e((string) $student['name']) ?></td>
                                <td><?= e((string) $student['course']) ?></td>
                                <td><?= e((string) $student['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="muted">No students have been registered yet.</p>
        <?php endif; ?>
    </article>

    <article class="result-card">
        <div class="card-head">
            <h2>Recent Activity</h2>
        </div>

        <?php if ($recentActivity): ?>
            <ul class="activity-list">
                <?php foreach ($recentActivity as $event): ?>
                    <li>
                        <span class="activity-badge <?= e(activity_badge_class((string) $event['action'])) ?>"><?= e((string) $event['action']) ?></span>
                        <div>
                            <p>
                                <strong><?= e((string) $event['admin_username']) ?></strong>
                                <?php if (!empty($event['student_nic'])): ?>
                                    <span>on NIC <?= e((string) $event['student_nic']) ?></span>
                                <?php endif; ?>
                            </p>
                            <small><?= e((string) $event['created_at']) ?></small>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="muted">Activity feed is empty right now.</p>
        <?php endif; ?>
    </article>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
