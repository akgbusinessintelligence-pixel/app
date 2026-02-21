<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$db_host = "localhost";
$db_name = "rentorpro_application";
$db_user = "rentorpro_appuser";
$db_pass = "vl%abRRz4!^Gd#Zm";

echo "Testing connection to {$db_host}...\n";

try {
    $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_TIMEOUT => 5, // 5 seconds timeout
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "SUCCESS: Connected to database '{$db_name}'.\n";

    $stmt = $pdo->query("SELECT VERSION()");
    $version = $stmt->fetchColumn();
    echo "MySQL Version: {$version}\n";

    // Check if admin_users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'admin_users'");
    if ($stmt->fetch()) {
        echo "Table 'admin_users' exists.\n";
    }
    else {
        echo "WARNING: Table 'admin_users' does NOT exist.\n";
    }

}
catch (PDOException $e) {
    echo "FAILURE: Could not connect to the database.\n";
    echo "Error Code: " . $e->getCode() . "\n";
    echo "Error Message: " . $e->getMessage() . "\n";
}
catch (Throwable $e) {
    echo "GLOBAL FAILURE: " . $e->getMessage() . "\n";
}
