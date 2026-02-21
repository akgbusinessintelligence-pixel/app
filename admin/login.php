<?php
require_once __DIR__ . '/../config.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';

  $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ?");
  $stmt->execute([$email]);
  $u = $stmt->fetch();

  if ($u && password_verify($pass, $u['password_hash'])) {
    $_SESSION['admin_id'] = $u['id'];
    header("Location: index.php");
    exit;
  }
  $error = "Invalid login.";
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Admin Login</title>
</head>
<body class="bg-light">
<div class="container py-5" style="max-width:420px;">
  <div class="card p-4">
    <h5 class="mb-3">Admin Login</h5>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
      <div class="mb-3">
        <label>Email</label>
        <input class="form-control" name="email" type="email" required>
      </div>
      <div class="mb-3">
        <label>Password</label>
        <input class="form-control" name="password" type="password" required>
      </div>
      <button class="btn btn-primary w-100">Login</button>
    </form>
  </div>
</div>
</body>
</html>
