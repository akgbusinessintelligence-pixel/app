<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ?");
$stmt->execute([$id]);
$app = $stmt->fetch();
if (!$app) exit("Not found.");

$files = $pdo->prepare("SELECT * FROM application_files WHERE application_id = ? ORDER BY id DESC");
$files->execute([$id]);
$frows = $files->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Application #<?= (int)$app['id'] ?></title>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Application #<?= (int)$app['id'] ?></h4>
    <?php if (!empty($app['pdf_path'])): ?>
      <a class="btn btn-success" href="../<?= htmlspecialchars($app['pdf_path']) ?>" target="_blank">Download PDF</a>
    <?php endif; ?>
  </div>

  <div class="card p-3 mb-3">
    <div><b>Unit:</b> <?= htmlspecialchars($app['unit']) ?></div>
    <div><b>Move-In:</b> <?= htmlspecialchars($app['move_in']) ?></div>
    <div><b>Applicant:</b> <?= htmlspecialchars(trim(($app['first_name'] ?? '').' '.($app['middle_name'] ?? '').' '.($app['last_name'] ?? ''))) ?></div>
    <div><b>Email:</b> <?= htmlspecialchars($app['email']) ?> | <b>Phone:</b> <?= htmlspecialchars($app['phone']) ?></div>
    <hr>
    <div><b>Address:</b> <?= htmlspecialchars($app['address1']) ?> <?= htmlspecialchars($app['address2'] ?? '') ?></div>
    <div><b>City/State/Zip:</b> <?= htmlspecialchars($app['city'] ?? '') ?>, <?= htmlspecialchars($app['state'] ?? '') ?> <?= htmlspecialchars($app['zip'] ?? '') ?></div>
    <hr>
    <div><b>Employer:</b> <?= htmlspecialchars($app['employer'] ?? '') ?> | <b>Salary:</b> <?= htmlspecialchars((string)($app['salary'] ?? '')) ?></div>
    <div><b>Additional Income:</b> <?= htmlspecialchars((string)($app['additional_income'] ?? '')) ?> | <b>Source:</b> <?= htmlspecialchars($app['income_source'] ?? '') ?></div>
    <hr>
    <div><b>Evicted:</b> <?= htmlspecialchars($app['evicted']) ?> | <b>Criminal:</b> <?= htmlspecialchars($app['criminal']) ?></div>
  </div>

  <div class="card p-3">
    <h6 class="mb-2">Uploaded Files</h6>
    <?php if (!$frows): ?>
      <div class="text-muted">No uploads.</div>
    <?php else: ?>
      <ul class="mb-0">
        <?php foreach ($frows as $f): ?>
          <li>
            <?= htmlspecialchars($f['original_name']) ?> —
            <a href="../<?= htmlspecialchars($f['file_path']) ?>" target="_blank">Open</a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
