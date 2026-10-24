<?php
$host = getenv('DB_HOST') ?: getenv('MYSQL_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: getenv('MYSQL_USER') ?: 'root';
$password = getenv('DB_PASS') ?: getenv('MYSQL_PASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: getenv('MYSQL_DATABASE') ?: 'queuezy_db';

date_default_timezone_set('Asia/Manila');

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    error_log('Queuezy database connection failed: ' . $conn->connect_error);
    $conn = null;
} elseif (!$conn->query("SET time_zone = '+08:00'")) {
    error_log('Queuezy database timezone setup failed: ' . $conn->error);
    $conn->close();
    $conn = null;
}

function ensureQueueCallCountColumn(mysqli $connection): bool
{
    $columnResult = $connection->query("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'queue' AND COLUMN_NAME = 'call_count' LIMIT 1");
    if (!$columnResult) {
        return false;
    }

    $columnExists = $columnResult->num_rows > 0;
    $columnResult->free();
    if ($columnExists) {
        return true;
    }

    if ($connection->query('ALTER TABLE queue ADD COLUMN call_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER started_at')) {
        return true;
    }

    $columnResult = $connection->query("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'queue' AND COLUMN_NAME = 'call_count' LIMIT 1");
    if (!$columnResult) {
        return false;
    }

    $columnExists = $columnResult->num_rows > 0;
    $columnResult->free();
    return $columnExists;
}

?>
