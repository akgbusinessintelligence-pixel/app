<?php
// config.php
declare(strict_types=1);

session_start();

$db_host = "localhost";
$db_name = "rentorpro_application";
$db_user = "rentorpro_appuser";
$db_pass = "vl%abRRz4!^Gd#Zm";

$pdo = new PDO(
  "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
  $db_user,
  $db_pass,
  [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]
);

// Upload + validation settings
define('UPLOAD_DIR', __DIR__ . '/uploads');
define('PDF_DIR', __DIR__ . '/pdfs');

define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10 MB per file
$ALLOWED_MIME = [
  'application/pdf',
  'image/jpeg',
  'image/png'
];

if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0775, true);
if (!is_dir(PDF_DIR)) mkdir(PDF_DIR, 0775, true);

function csrf_token(): string {
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf'];
}

function require_csrf(): void {
  $ok = isset($_POST['csrf']) && hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf']);
  if (!$ok) {
    http_response_code(403);
    exit("Invalid CSRF token.");
  }
}

function clean_str(?string $v): ?string {
  if ($v === null) return null;
  $v = trim($v);
  return $v === '' ? null : $v;
}
