<?php
require_once __DIR__ . '/config.php';

$email = "admin@yourdomain.com";
$password = "ChangeMeNow123!";

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO admin_users (email, password_hash) VALUES (?, ?)");
$stmt->execute([$email, $hash]);

echo "Admin created: {$email} / {$password} (DELETE THIS FILE NOW)";
