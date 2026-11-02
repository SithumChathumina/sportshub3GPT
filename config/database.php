<?php
// XAMPP uses root with an empty password by default.
// Change these four values when using your own hosting account.
$databaseHost = 'localhost';
$databaseName = 'sportshub3_db';
$databaseUser = 'root';
$databasePassword = '';

try {
    $connection = new PDO(
        "mysql:host=$databaseHost;dbname=$databaseName;charset=utf8mb4",
        $databaseUser,
        $databasePassword
    );
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    exit('Database connection failed. Check your settings, start MySQL, and import database/sportshub.sql.');
}
