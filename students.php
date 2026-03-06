<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/constants.php';

$search = trim((string) ($_GET['q'] ?? ''));
$course = trim((string) ($_GET['course'] ?? ''));
$gender = trim((string) ($_GET['gender'] ?? ''));
$sort = trim((string) ($_GET['sort'] ?? 'newest'));
$page = max(1, (int) ($_GET['page'] ?? 1));

if ($course !== '' && !in_array($course, CAMPUS_COURSES, true)) {
    $course = '';
}

if ($gender !== '' && !in_array($gender, CAMPUS_GENDERS, true)) {
    $gender = '';
}

$sortSqlMap = [
    'newest' => 'created_at DESC',
    'oldest' => 'created_at ASC',
    'name_asc' => 'name ASC',
    'name_desc' => 'name DESC',
];

if (!array_key_exists($sort, $sortSqlMap)) {
    $sort = 'newest';
}

$whereParts = [];
$params = [];

if ($search !== '') {
    $whereParts[] = '(nic LIKE :search OR name LIKE :search OR email LIKE :search)';
    $params['search'] = '%' . $search . '%';
}

if ($course !== '') {
    $whereParts[] = 'course = :course';
    $params['course'] = $course;
}

if ($gender !== '') {
    $whereParts[] = 'gender = :gender';
    $params['gender'] = $gender;
}

$whereSql = $whereParts ? (' WHERE ' . implode(' AND ', $whereParts)) : '';

$countSql = 'SELECT COUNT(*) FROM students' . $whereSql;
$countStmt = $pdo->prepare($countSql);
foreach ($params as $key => $value) {
    $countStmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
}
$countStmt->execute();
$totalItems = (int) $countStmt->fetchColumn();

$perPage = 10;
$totalPages = max(1, (int) ceil($totalItems / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;

$listSql = 'SELECT nic, name, gender, course, email, created_at FROM students'
    . $whereSql
    . ' ORDER BY ' . $sortSqlMap[$sort]
    . ' LIMIT :limit OFFSET :offset';
$listStmt = $pdo->prepare($listSql);
foreach ($params as $key => $value) {
    $listStmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
}
$listStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$listStmt->execute();
$students = $listStmt->fetchAll();

$baseQuery = [
    'q' => $search,
    'course' => $course,
    'gender' => $gender,
    'sort' => $sort,
];

$buildPageUrl = static function (int $targetPage) use ($baseQuery): string {
    $query = $baseQuery;
    $query['page'] = $targetPage;

    return 'students.php?' . http_build_query(array_filter($query, static fn ($value): bool => $value !== ''));
};

$pageTitle = 'Student Directory';
$showNav = true;
require __DIR__ . '/includes/header.php';
?>
<section class="page-header">
    <div>
        <h1>Student Directory</h1>
        <p>Filter, sort, and manage student records quickly.</p>
    </div>
    <div class="quick-links">
        <a class="btn btn-secondary" href="register.php">New Registration</a>
        <a class="btn btn-secondary" href="search.php">Quick NIC Search</a>
    </div>
</section>

<section class="form-card compact">
    <form method="get" action="students.php" class="filter-grid" data-validate-form novalidate>
        <div class="form-group">
            <label for="q">Search</label>
            <input type="text" id="q" name="q" value="<?= e($search) ?>" placeholder="NIC, name, or email">
        </div>

        <div class="form-group">
            <label for="course">Course</label>
            <select id="course" name="course">
                <option value="">All courses</option>
                <?php foreach (CAMPUS_COURSES as $courseName): ?>
                    <option value="<?= e($courseName) ?>" <?= $course === $courseName ? 'selected' : '' ?>><?= e($courseName) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="gender">Gender</label>
            <select id="gender" name="gender">
                <option value="">All genders</option>
                <?php foreach (CAMPUS_GENDERS as $genderName): ?>
                    <option value="<?= e($genderName) ?>" <?= $gender === $genderName ? 'selected' : '' ?>><?= e($genderName) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="sort">Sort</label>
            <select id="sort" name="sort">
                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest</option>
                <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name A-Z</option>
                <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Name Z-A</option>
            </select>
        </div>

        <div class="actions full-span">
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a class="btn btn-ghost" href="students.php">Reset</a>
        </div>
    </form>
</section>

<section class="result-card">
    <div class="card-head">
        <h2>Results</h2>
        <p class="muted"><?= e((string) $totalItems) ?> student(s) found.</p>
    </div>

    <?php if ($students): ?>
        <div class="table-wrap">
            <table class="data-table data-table-wide">
                <thead>
                    <tr>
                        <th>NIC</th>
                        <th>Name</th>
                        <th>Gender</th>
                        <th>Course</th>
                        <th>Email</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= e((string) $student['nic']) ?></td>
                            <td><?= e((string) $student['name']) ?></td>
                            <td><?= e((string) $student['gender']) ?></td>
                            <td><?= e((string) $student['course']) ?></td>
                            <td><?= e((string) $student['email']) ?></td>
                            <td><?= e((string) $student['created_at']) ?></td>
                            <td>
                                <div class="actions actions-tight">
                                    <a class="btn btn-ghost" href="edit.php?nic=<?= urlencode((string) $student['nic']) ?>">Edit</a>
                                    <form method="post" action="delete.php" class="inline js-confirm-delete" data-confirm-message="Delete this student from the directory?">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="nic" value="<?= e((string) $student['nic']) ?>">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info" role="status">
            No students match the selected filters.
        </div>
    <?php endif; ?>

    <?php if ($totalPages > 1): ?>
        <nav class="pagination" aria-label="Student directory pages">
            <?php if ($page > 1): ?>
                <a class="btn btn-ghost" href="<?= e($buildPageUrl($page - 1)) ?>">Previous</a>
            <?php endif; ?>
            <span>Page <?= e((string) $page) ?> of <?= e((string) $totalPages) ?></span>
            <?php if ($page < $totalPages): ?>
                <a class="btn btn-ghost" href="<?= e($buildPageUrl($page + 1)) ?>">Next</a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
