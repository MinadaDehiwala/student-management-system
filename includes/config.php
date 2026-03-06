<?php

declare(strict_types=1);

$host = '127.0.0.1';
$db = 'campus_db';
$user = 'campus_user';
$pass = 'Ga$9vL!2QxR#8tPm';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $exception) {
    http_response_code(500);
    exit('Database connection failed. Please check database settings.');
}
