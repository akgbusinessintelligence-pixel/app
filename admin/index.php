<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';
require_admin();

$rows = $pdo->query("SELECT id, created_at, unit, first_name, last_name, email, phone FROM applications ORDER BY id DESC LIMIT 200")->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Admin Dashboard</title>
</head>
<body class="bg-light">
<div class="container py-4">
  <h4 class="mb-3">Applications</h4>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped mb-0">
        <thead>
          <tr>
            <th>ID</th><th>Date</th><th>Unit</th><th>Name</th><th>Email</th><th>Phone</th><th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= htmlspecialchars($r['created_at']) ?></td>
            <td><?= htmlspecialchars($r['unit']) ?></td>
            <td><?= htmlspecialchars($r['first_name'].' '.$r['last_name']) ?></td>
            <td><?= htmlspecialchars($r['email']) ?></td>
            <td><?= htmlspecialchars($r['phone']) ?></td>
            <td><a class="btn btn-sm btn-outline-primary" href="view.php?id=<?= (int)$r['id'] ?>">View</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
