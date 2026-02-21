<?php
/**
 * cPanel Environment Diagnostics
 * Upload this file to your server and visit it in your browser.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><title>cPanel Diagnostics</title>";
echo "<style>body{font-family:sans-serif;line-height:1.5;max-width:800px;margin:20px auto;padding:0 20px;}";
echo ".status{padding:3px 8px;border-radius:3px;font-weight:bold;font-size:0.9em;}";
echo ".ok{background:#d4edda;color:#155724;}";
echo ".fail{background:#f8d7da;color:#721c24;}";
echo ".warn{background:#fff3cd;color:#856404;}";
echo "h2{border-bottom:1px solid #eee;padding-bottom:10px;margin-top:30px;}";
echo "pre{background:#f8f9fa;padding:10px;border:1px solid #ddd;overflow-x:auto;}";
echo "</style></head><body>";

echo "<h1>cPanel Hosting Diagnostics</h1>";

// 1. PHP Version
echo "<h2>1. PHP Environment</h2>";
echo "PHP Version: " . PHP_VERSION . " ";
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    echo "<span class='status ok'>OK</span>";
}
else {
    echo "<span class='status fail'>FAIL (Requires 7.4+)</span>";
}

// 2. Extensions
$required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'dom', 'gd', 'fileinfo', 'session'];
echo "<ul>";
foreach ($required_extensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "<li>Extension <code>$ext</code>: " . ($loaded ? "<span class='status ok'>LOADED</span>" : "<span class='status fail'>MISSING</span>") . "</li>";
}
echo "</ul>";

// 3. File System
echo "<h2>2. File System Permissions</h2>";
$dirs = [
    'uploads' => __DIR__ . '/uploads',
    'pdfs' => __DIR__ . '/pdfs',
    'vendor' => __DIR__ . '/vendor'
];

echo "<ul>";
foreach ($dirs as $name => $path) {
    if (!is_dir($path)) {
        echo "<li><code>$name/</code>: <span class='status fail'>NOT FOUND</span> (Expected at $path)</li>";
        continue;
    }
    $writable = is_writable($path);
    echo "<li><code>$name/</code>: " . ($writable ? "<span class='status ok'>WRITABLE</span>" : "<span class='status fail'>NOT WRITABLE</span>") . "</li>";
}

$autoloader = __DIR__ . '/vendor/autoload.php';
echo "<li><code>vendor/autoload.php</code>: " . (file_exists($autoloader) ? "<span class='status ok'>FOUND</span>" : "<span class='status fail'>MISSING</span>") . "</li>";
echo "</ul>";

// 4. Database Connection
echo "<h2>3. Database Connectivity</h2>";
$configFile = __DIR__ . '/config.php';

if (!file_exists($configFile)) {
    echo "<span class='status fail'>config.php NOT FOUND</span>";
}
else {
    echo "<p>Found <code>config.php</code>. Attempting connection...</p>";
    try {
        // We include config.php which should establish $pdo
        // Note: session_start() might trigger a warning if headers sent, but we suppressed with @ if needed or just let it be.
        ob_start();
        include $configFile;
        ob_end_clean();

        if (isset($pdo) && $pdo instanceof PDO) {
            $stmt = $pdo->query("SELECT VERSION()");
            $ver = $stmt->fetchColumn();
            echo "<span class='status ok'>SUCCESS</span> connected to database.<br>";
            echo "MySQL Version: <code>$ver</code>";
        }
        else {
            echo "<span class='status fail'>FAIL</span> \$pdo object not found after including config.php.";
        }
    }
    catch (Throwable $e) {
        echo "<span class='status fail'>CONNECTION FAILED</span><br>";
        echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
        echo "<p><b>Troubleshooting Tips:</b>";
        echo "<ul><li>Ensure <b>Remote MySQL</b> (if applicable) allows your current IP.</li>";
        echo "<li>Ensure database name, user, and password are correct in cPanel.</li>";
        echo "<li>Check if 'localhost' is allowed or if your cPanel uses a specific DB host.</li></ul></p>";
    }
}

echo "<h2>4. Server Info</h2>";
echo "<pre>";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
echo "Script Path: " . __FILE__ . "\n";
echo "</pre>";

echo "<p style='margin-top:50px;font-size:0.8em;color:#888;'>Delete this file after testing for security.</p>";
echo "</body></html>";
