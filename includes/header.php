<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$pageTitle = $pageTitle ?? 'Private Campus Student Management';
$showNav = $showNav ?? false;
$flash = get_flash();
$currentPath = basename((string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?? ''));
$allowedFlashTypes = ['success', 'error', 'info'];

$navItems = [
    'dashboard.php' => 'Dashboard',
    'students.php' => 'Students',
    'register.php' => 'Register',
    'search.php' => 'Quick Search',
    'logout.php' => 'Logout',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="<?= $showNav && is_logged_in() ? 'app-shell' : 'auth-shell' ?>">
<div class="bg-layer" aria-hidden="true"></div>

<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="dashboard.php" aria-label="Private Campus home">
            <span>Private Campus</span>
            <small>Student Intelligence Portal</small>
        </a>

        <?php if ($showNav && is_logged_in()): ?>
            <nav class="main-nav" aria-label="Main navigation">
                <?php foreach ($navItems as $href => $label): ?>
                    <?php $isActive = $currentPath === $href; ?>
                    <a href="<?= e($href) ?>" class="<?= $isActive ? 'is-active' : '' ?>"><?= e($label) ?></a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>
    </div>
</header>

<main class="container main-content">
    <?php if ($flash): ?>
        <?php
        $flashType = (string) ($flash['type'] ?? 'info');
        if (!in_array($flashType, $allowedFlashTypes, true)) {
            $flashType = 'info';
        }
        $isError = $flashType === 'error';
        ?>
        <div class="alert alert-<?= e($flashType) ?> js-alert" role="<?= $isError ? 'alert' : 'status' ?>">
            <p><?= e((string) ($flash['message'] ?? '')) ?></p>
            <button type="button" class="alert-close" data-dismiss-alert aria-label="Dismiss notification">&times;</button>
        </div>
    <?php endif; ?>
