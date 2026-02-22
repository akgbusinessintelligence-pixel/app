<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$app = $pdo->prepare("SELECT * FROM applications WHERE id = ?");
$app->execute([$id]);
$app = $app->fetch();
if (!$app) exit("Application not found.");

$files = $pdo->prepare("SELECT * FROM application_files WHERE application_id = ? ORDER BY id");
$files->execute([$id]);
$frows = $files->fetchAll();

// Handle status update from view page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $pdo->prepare("UPDATE applications SET status=?, admin_notes=? WHERE id=?")
        ->execute([clean_str($_POST['status']), clean_str($_POST['notes'] ?? null), $id]);
    header("Location: view.php?id=$id&saved=1"); exit;
}

function row($label, $value) {
    if (empty($value)) return;
    echo "<tr><td class='text-muted fw-semibold' style='width:180px;'>$label</td><td>" . htmlspecialchars((string)$value) . "</td></tr>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application #<?= $id ?> | Rentor Pro Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family:'Inter',sans-serif; background:#f0f2f5; }
        .sidebar { width:240px; min-height:100vh; background:#1a1f36; position:fixed; top:0; left:0; z-index:100; }
        .sidebar .logo { padding:24px 20px; border-bottom:1px solid rgba(255,255,255,.08); }
        .sidebar .logo h5 { color:#fff; font-weight:800; margin:0; font-size:.95rem; }
        .sidebar .logo small { color:rgba(255,255,255,.4); font-size:.75rem; }
        .sidebar nav a { display:flex; align-items:center; gap:10px; padding:12px 20px; color:rgba(255,255,255,.65); text-decoration:none; font-size:.875rem; }
        .sidebar nav a:hover { background:rgba(255,255,255,.08); color:#fff; }
        .main { margin-left:240px; padding:30px; }
        .section-card { background:#fff; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.06); border:1px solid #e9ecef; padding:24px; margin-bottom:20px; }
        .section-title { font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:#6c757d; margin-bottom:16px; padding-bottom:8px; border-bottom:1px solid #f0f0f0; }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="logo"><h5>🏠 Rentor Pro</h5><small>Admin Dashboard</small></div>
    <nav class="mt-2">
        <a href="index.php"><span>📋</span> Applications</a>
        <a href="logout.php"><span>🚪</span> Logout</a>
    </nav>
</div>

<div class="main">
    <?php if(isset($_GET['saved'])): ?>
        <div class="alert alert-success alert-dismissible fade show"><strong>Saved!</strong> Status updated. <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="index.php" class="btn btn-outline-secondary btn-sm">← Back</a>
        <div>
            <h4 class="mb-0 fw-bold">Application #<?= $id ?></h4>
            <small class="text-muted">Submitted <?= date('F j, Y \a\t g:ia', strtotime($app['created_at'])) ?></small>
        </div>
        <?php if (!empty($app['pdf_path'])): ?>
        <a href="../<?= htmlspecialchars($app['pdf_path']) ?>" target="_blank" class="btn btn-success ms-auto">📄 Download PDF</a>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Property -->
            <div class="section-card">
                <div class="section-title">🏠 Property</div>
                <table class="table table-sm table-borderless mb-0">
                    <?php row('Unit', $app['unit']); row('Move-In Date', $app['move_in']); ?>
                </table>
            </div>

            <!-- Applicant -->
            <div class="section-card">
                <div class="section-title">👤 Applicant</div>
                <table class="table table-sm table-borderless mb-0">
                    <?php
                    row('Full Name', trim(($app['first_name']??'').' '.($app['middle_name']??'').' '.($app['last_name']??'')));
                    row('Date of Birth', $app['dob'] ?? null);
                    row('Email', $app['email'] ?? null);
                    row('Phone', $app['phone'] ?? null);
                    ?>
                </table>
            </div>

            <!-- Address -->
            <div class="section-card">
                <div class="section-title">📍 Address History</div>
                <table class="table table-sm table-borderless mb-0">
                    <?php
                    row('Street', $app['address1'] ?? null);
                    if (!empty($app['address2'])) row('Address 2', $app['address2']);
                    row('City / State / Zip', trim(($app['city']??'').' '.($app['state']??'').' '.($app['zip']??'')));
                    row('Monthly Rent', $app['rent'] ? '$'.number_format((float)$app['rent'],2) : null);
                    row('Reason for Leaving', $app['reason'] ?? null);
                    ?>
                </table>
            </div>

            <!-- Employment -->
            <div class="section-card">
                <div class="section-title">💼 Employment & Income</div>
                <table class="table table-sm table-borderless mb-0">
                    <?php
                    row('Employer', $app['employer'] ?? null);
                    row('Monthly Income', $app['salary'] ? '$'.number_format((float)$app['salary'],2) : null);
                    row('Additional Income', $app['additional_income'] ? '$'.number_format((float)$app['additional_income'],2) : null);
                    row('Income Source', $app['income_source'] ?? null);
                    ?>
                </table>
            </div>

            <!-- Background -->
            <div class="section-card">
                <div class="section-title">📋 Background</div>
                <table class="table table-sm table-borderless mb-0">
                    <?php
                    row('Evicted', $app['evicted'] ?? 'No');
                    row('Criminal History', $app['criminal'] ?? 'No');
                    row('Bankruptcy', $app['bankruptcy'] ?? 'No');
                    ?>
                </table>
            </div>

            <!-- CashApp Payment -->
            <div class="section-card">
                <div class="section-title">💳 CashApp Payment</div>
                <table class="table table-sm table-borderless mb-0">
                    <?php
                    row('CashApp Tag', !empty($app['cashapp_cashtag']) ? '$'.$app['cashapp_cashtag'] : null);
                    row('Transaction ID', $app['cashapp_txn_id'] ?? null);
                    row('Payment Status', $app['payment_status'] ?? 'pending');
                    ?>
                </table>
                <?php if (empty($app['cashapp_txn_id'])): ?>
                    <p class="text-warning small mb-0">⚠ No payment transaction ID on record.</p>
                <?php endif; ?>
            </div>

            <!-- Documents -->
            <div class="section-card">
                <div class="section-title">📎 Uploaded Documents</div>
                <?php if (!$frows): ?>
                    <p class="text-muted mb-0">No documents uploaded.</p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-2">
                    <?php foreach ($frows as $f): ?>
                        <div class="d-flex align-items-center justify-content-between p-2 border rounded">
                            <div>
                                <span class="fw-semibold small"><?= htmlspecialchars($f['original_name']) ?></span>
                                <span class="text-muted small ms-2"><?= round($f['file_size']/1024, 1) ?> KB</span>
                            </div>
                            <a href="../<?= htmlspecialchars($f['file_path']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">Open</a>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar: Status + Notes -->
        <div class="col-lg-4">
            <div class="section-card position-sticky" style="top:20px;">
                <div class="section-title">⚡ Quick Actions</div>
                <form method="POST">
                    <label class="form-label fw-semibold">Application Status</label>
                    <select name="status" class="form-select mb-3">
                        <option value="pending"  <?= ($app['status']??'pending')==='pending'?'selected':'' ?>>⏳ Pending</option>
                        <option value="review"   <?= ($app['status']??'')==='review'?'selected':'' ?>>🔍 In Review</option>
                        <option value="approved" <?= ($app['status']??'')==='approved'?'selected':'' ?>>✅ Approved</option>
                        <option value="denied"   <?= ($app['status']??'')==='denied'?'selected':'' ?>>❌ Denied</option>
                    </select>
                    <label class="form-label fw-semibold">Admin Notes</label>
                    <textarea name="notes" class="form-control mb-3" rows="5" placeholder="Internal notes about this applicant..."><?= htmlspecialchars($app['admin_notes'] ?? '') ?></textarea>
                    <button class="btn btn-primary w-100 fw-semibold">Save Changes</button>
                </form>
                <hr>
                <a href="index.php?status=<?= urlencode($app['status'] ?? '') ?>" class="btn btn-outline-secondary w-100 btn-sm">← Back to List</a>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>