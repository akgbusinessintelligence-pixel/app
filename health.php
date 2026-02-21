<?php
ini_set('display_errors','1');
error_reporting(E_ALL);

echo "PHP OK<br>";
echo "PHP Version: " . PHP_VERSION . "<br>";

echo "Fileinfo (finfo): " . (class_exists('finfo') ? "YES" : "NO") . "<br>";
echo "mbstring: " . (extension_loaded('mbstring') ? "YES" : "NO") . "<br>";
echo "gd: " . (extension_loaded('gd') ? "YES" : "NO") . "<br>";

echo "uploads writable: " . (is_writable(__DIR__.'/uploads') ? "YES" : "NO") . "<br>";
echo "pdfs writable: " . (is_writable(__DIR__.'/pdfs') ? "YES" : "NO") . "<br>";

require_once __DIR__ . "/config.php";
echo "config.php loaded<br>";

try {
  $pdo->query("SELECT 1");
  echo "DB OK<br>";
} catch (Throwable $e) {
  echo "DB FAIL: " . $e->getMessage();
}
