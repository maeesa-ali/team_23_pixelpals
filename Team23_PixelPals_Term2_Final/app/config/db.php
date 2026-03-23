<?php
require_once __DIR__ . '/config.php';

$db = null;

// Allow environment overrides later for hosting, but still fall back to the local config constants.
$dbHost = getenv('DB_HOST') ?: DB_HOST;
$dbName = getenv('DB_NAME') ?: DB_NAME;
$dbUser = getenv('DB_USER') ?: DB_USER;
$dbPass = getenv('DB_PASS');
$dbPass = $dbPass !== false ? $dbPass : DB_PASS;

// Try the configured host first, then the usual localhost/127.0.0.1 alternative if needed.
$hostsToTry = array_values(array_unique([$dbHost, $dbHost === '127.0.0.1' ? 'localhost' : '127.0.0.1']));

foreach ($hostsToTry as $host) {
    try {
        $db = new PDO(
            'mysql:host=' . $host . ';dbname=' . $dbName . ';charset=utf8mb4',
            $dbUser,
            $dbPass
        );
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        break;
    } catch (PDOException $e) {
        $db = null;
    }
}
