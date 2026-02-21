<?php
/**
 * File Inspector - Use this to see exactly what files are on your server.
 * Upload to public_html and visit via browser.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<html><head><title>Server File Inspector</title>";
echo "<style>body{font-family:monospace;background:#222;color:#0f0;padding:20px;} li{margin:5px 0;} .dir{color:#55f;font-weight:bold;}</style>";
echo "</head><body>";

echo "<h1>File Inspector</h1>";
echo "<strong>Current Directory:</strong> " . __DIR__ . "<br><br>";

function list_files($dir)
{
    $files = scandir($dir);
    echo "<ul>";
    foreach ($files as $file) {
        if ($file === '.' || $file === '..')
            continue;

        $path = $dir . DIRECTORY_SEPARATOR . $file;
        $is_dir = is_dir($path);

        echo "<li>";
        if ($is_dir) {
            echo "<span class='dir'>[DIR] $file</span>";
        // Optional: recurse one level for clarity
        // list_files($path); 
        }
        else {
            echo "[FILE] $file (" . number_format(filesize($path)) . " bytes)";
        }
        echo "</li>";
    }
    echo "</ul>";
}

list_files(__DIR__);

echo "<p style='color:#888;margin-top:50px;'>Delete this file immediately after use.</p>";
echo "</body></html>";
