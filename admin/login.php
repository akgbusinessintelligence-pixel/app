<?php
require_once __DIR__ . '/../config.php';
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $stmt  = $pdo->prepare("SELECT * FROM admin_users WHERE email = ?");
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    if ($u && password_verify($pass, $u['password_hash'])) {
        $_SESSION['admin_id'] = $u['id'];
        header("Location: index.php"); exit;
    }
    $error = "Invalid email or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login | Rentor Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family:'Inter',sans-serif; background:linear-gradient(135deg,#1a1f36 0%,#2d3561 100%); min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .login-card { background:#fff; border-radius:16px; padding:40px; width:100%; max-width:400px; box-shadow:0 20px 60px rgba(0,0,0,.3); }
        .login-card h4 { font-weight:800; color:#1a1f36; }
        .btn-primary { background:#1a1f36; border-color:#1a1f36; }
        .btn-primary:hover { background:#2d3561; border-color:#2d3561; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="text-center mb-4">
        <div style="font-size:2.5rem;">🏠</div>
        <h4 class="mt-2">Rentor Pro Admin</h4>
        <p class="text-muted small">Sign in to manage applications</p>
    </div>
    <?php if ($error): ?>
        <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label fw-semibold">Email Address</label>
            <input class="form-control" name="email" type="email" placeholder="admin@example.com" required autofocus>
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold">Password</label>
            <input class="form-control" name="password" type="password" placeholder="••••••••" required>
        </div>
        <button class="btn btn-primary w-100 fw-semibold py-2">Sign In →</button>
    </form>
</div>
</body>
</html>