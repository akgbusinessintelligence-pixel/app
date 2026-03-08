<?php
// config.php
declare(strict_types = 1)
;

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$db_host = "localhost"; // cPanel usually uses 'localhost' or '127.0.0.1'
$db_name = "rentorpro_application";
$db_user = "rentorpro_appuser";
$db_pass = "vl%abRRz4!^Gd#Zm";

try {
  // Use SQLite if database.sqlite exists (local development)
  if (file_exists(__DIR__ . '/database.sqlite')) {
    $pdo = new PDO("sqlite:" . __DIR__ . '/database.sqlite', null, null, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
    ]);
  }
  else {
    // MySQL for Production (cPanel)
    $pdo = new PDO(
      "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
      $db_user,
      $db_pass,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
    ]
      );
  }
}
catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}

// Upload + validation settings
define('UPLOAD_DIR', __DIR__ . '/uploads');
define('PDF_DIR', __DIR__ . '/pdfs');

define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10 MB per file
$ALLOWED_MIME = [
  'application/pdf',
  'image/jpeg',
  'image/png'
];

if (!is_dir(UPLOAD_DIR))
  mkdir(UPLOAD_DIR, 0775, true);
if (!is_dir(PDF_DIR))
  mkdir(PDF_DIR, 0775, true);

function csrf_token(): string
{
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf'];
}

function require_csrf(): void
{
  $ok = isset($_POST['csrf']) && hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf']);
  if (!$ok) {
    http_response_code(403);
    exit("Invalid CSRF token.");
  }
}

function clean_str(?string $v): ?string
{
  if ($v === null)
    return null;
  $v = trim($v);
  return $v === '' ? null : $v;
}

function ensure_properties_table(PDO $pdo): void
{
  static $initialized = false;
  if ($initialized) {
    return;
  }

  $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
  if ($driver === 'sqlite') {
    $pdo->exec("CREATE TABLE IF NOT EXISTS properties (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT NOT NULL,
      address TEXT NOT NULL,
      rent_amount REAL NOT NULL,
      bedrooms INTEGER NOT NULL,
      bathrooms REAL NOT NULL,
      status TEXT NOT NULL DEFAULT 'Available',
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");
  }
  else {
    $pdo->exec("CREATE TABLE IF NOT EXISTS properties (
      id INT(11) NOT NULL AUTO_INCREMENT,
      name VARCHAR(150) NOT NULL,
      address VARCHAR(255) NOT NULL,
      rent_amount DECIMAL(10,2) NOT NULL,
      bedrooms TINYINT UNSIGNED NOT NULL,
      bathrooms DECIMAL(3,1) NOT NULL,
      status ENUM('Available','Rented') NOT NULL DEFAULT 'Available',
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  }

  $initialized = true;
}

function get_properties(PDO $pdo, ?string $status = null): array
{
  ensure_properties_table($pdo);
  $sql = "SELECT id, name, address, rent_amount, bedrooms, bathrooms, status FROM properties";
  $params = [];
  if ($status !== null) {
    $sql .= " WHERE status = ?";
    $params[] = $status;
  }
  $sql .= " ORDER BY id DESC";

  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  return $stmt->fetchAll();
}
