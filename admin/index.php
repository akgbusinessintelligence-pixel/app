<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';
require_admin();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $pdo->prepare("UPDATE applications SET status=? WHERE id=?")->execute([
            clean_str($_POST['status']), (int)$_POST['id']
        ]);
    }
    if ($_POST['action'] === 'delete') {
        $pdo->prepare("DELETE FROM applications WHERE id=?")->execute([(int)$_POST['id']]);
    }
    header("Location: index.php"); exit;
}

// Stats
$total     = $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();
$pending   = $pdo->query("SELECT COUNT(*) FROM applications WHERE status IS NULL OR status='pending'")->fetchColumn();
$approved  = $pdo->query("SELECT COUNT(*) FROM applications WHERE status='approved'")->fetchColumn();
$denied    = $pdo->query("SELECT COUNT(*) FROM applications WHERE status='denied'")->fetchColumn();
$today     = $pdo->query("SELECT COUNT(*) FROM applications WHERE DATE(created_at)=DATE('now')")->fetchColumn();

// Search + filter
$search  = clean_str($_GET['q'] ?? null) ?? '';
$filter  = clean_str($_GET['status'] ?? null) ?? '';
$where   = []; $params = [];
if ($search) { $where[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR unit LIKE ?)"; $params = array_merge($params, ["%$search%","%$search%","%$search%","%$search%"]); }
if ($filter) { $where[] = "status = ?"; $params[] = $filter; }
$sql  = "SELECT id, created_at, unit, first_name, last_name, email, phone, status, cashapp_txn_id FROM applications";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY id DESC LIMIT 300";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$rows = $stmt->fetchAll();

function statusBadge($s) {
    return match($s ?? 'pending') {
        'approved' => '<span class="badge bg-success">Approved</span>',
        'denied'   => '<span class="badge bg-danger">Denied</span>',
        'review'   => '<span class="badge bg-warning text-dark">In Review</span>',
        default    => '<span class="badge bg-secondary">Pending</span>',
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Rentor Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; }
        .sidebar { width: 240px; min-height: 100vh; background: #1a1f36; position: fixed; top:0; left:0; padding: 0; z-index:100; }
        .sidebar .logo { padding: 24px 20px; border-bottom: 1px solid rgba(255,255,255,.08); }
        .sidebar .logo h5 { color:#fff; font-weight:800; margin:0; font-size:.95rem; letter-spacing:.5px; }
        .sidebar .logo small { color:rgba(255,255,255,.4); font-size:.75rem; }
        .sidebar nav a { display:flex; align-items:center; gap:10px; padding:12px 20px; color:rgba(255,255,255,.65); text-decoration:none; font-size:.875rem; transition:.2s; }
        .sidebar nav a:hover, .sidebar nav a.active { background:rgba(255,255,255,.08); color:#fff; }
        .sidebar nav a .icon { width:20px; text-align:center; }
        .main { margin-left:240px; padding:30px; }
        .stat-card { background:#fff; border-radius:12px; padding:20px 24px; box-shadow:0 1px 4px rgba(0,0,0,.06); border:1px solid #e9ecef; }
        .stat-card .label { font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:#6c757d; margin-bottom:4px; }
        .stat-card .value { font-size:2rem; font-weight:800; line-height:1; }
        .stat-card .sub { font-size:.75rem; color:#6c757d; margin-top:4px; }
        .card-panel { background:#fff; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.06); border:1px solid #e9ecef; }
        .table th { font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:#6c757d; border-bottom:2px solid #e9ecef; }
        .table td { font-size:.875rem; vertical-align:middle; }
        .top-bar { display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; }
        .top-bar h4 { font-weight:700; margin:0; }
        .search-bar { max-width:320px; }
        .btn-icon { padding:6px 10px; }
        .status-select { font-size:.75rem; padding:2px 6px; border-radius:6px; }
        @media(max-width:768px) { .sidebar{display:none;} .main{margin-left:0;} }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo">
        <h5>🏠 Rentor Pro</h5>
        <small>Admin Dashboard</small>
    </div>
    <nav class="mt-2">
        <a href="index.php" class="active"><span class="icon">📋</span> Applications</a>
        <a href="index.php?status=pending"><span class="icon">⏳</span> Pending</a>
        <a href="index.php?status=review"><span class="icon">🔍</span> In Review</a>
        <a href="index.php?status=approved"><span class="icon">✅</span> Approved</a>
        <a href="index.php?status=denied"><span class="icon">❌</span> Denied</a>
        <hr style="border-color:rgba(255,255,255,.08); margin:10px 0;">
        <a href="logout.php"><span class="icon">🚪</span> Logout</a>
    </nav>
</div>

<!-- MAIN -->
<div class="main">
    <div class="top-bar">
        <div>
            <h4>Applications</h4>
            <small class="text-muted"><?= $today ?> new today &bull; <?= $total ?> total</small>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <form method="GET" class="d-flex gap-2">
                <input name="q" type="search" class="form-control form-control-sm search-bar" placeholder="Search name, email, unit..." value="<?= htmlspecialchars($search) ?>">
                <select name="status" class="form-select form-select-sm" style="width:130px;">
                    <option value="">All Status</option>
                    <option value="pending" <?= $filter==='pending'?'selected':'' ?>>Pending</option>
                    <option value="review" <?= $filter==='review'?'selected':'' ?>>In Review</option>
                    <option value="approved" <?= $filter==='approved'?'selected':'' ?>>Approved</option>
                    <option value="denied" <?= $filter==='denied'?'selected':'' ?>>Denied</option>
                </select>
                <button class="btn btn-sm btn-primary px-3">Filter</button>
                <?php if($search||$filter): ?><a href="index.php" class="btn btn-sm btn-outline-secondary">Clear</a><?php endif; ?>
            </form>
        </div>
    </div>

    <!-- STATS -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="label">Total</div>
                <div class="value text-primary"><?= $total ?></div>
                <div class="sub">All applications</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="label">Pending</div>
                <div class="value text-secondary"><?= $pending ?></div>
                <div class="sub">Awaiting review</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="label">Approved</div>
                <div class="value text-success"><?= $approved ?></div>
                <div class="sub">Ready to move in</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="label">Denied</div>
                <div class="value text-danger"><?= $denied ?></div>
                <div class="sub">Not approved</div>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="card-panel">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Unit</th>
                        <th>Applicant</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>CashApp TXN</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td class="fw-semibold">#<?= (int)$r['id'] ?></td>
                        <td><?= date('M j, Y', strtotime($r['created_at'])) ?></td>
                        <td><?= htmlspecialchars($r['unit'] ?? '—') ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars(trim($r['first_name'].' '.$r['last_name'])) ?></td>
                        <td><?= htmlspecialchars($r['email'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($r['phone'] ?? '—') ?></td>
                        <td>
                            <?php if(!empty($r['cashapp_txn_id'])): ?>
                                <code class="small"><?= htmlspecialchars($r['cashapp_txn_id']) ?></code>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= statusBadge($r['status'] ?? null) ?></td>
                        <td>
                            <div class="d-flex gap-1 align-items-center">
                                <a href="view.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-primary btn-icon" title="View">👁</a>
                                <!-- Quick status update -->
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                    <select name="status" class="form-select form-select-sm status-select" onchange="this.form.submit()" title="Change status">
                                        <option value="pending" <?= ($r['status']??'pending')==='pending'?'selected':'' ?>>Pending</option>
                                        <option value="review"  <?= ($r['status']??'')==='review'?'selected':'' ?>>Review</option>
                                        <option value="approved"<?= ($r['status']??'')==='approved'?'selected':'' ?>>Approve</option>
                                        <option value="denied"  <?= ($r['status']??'')==='denied'?'selected':'' ?>>Deny</option>
                                    </select>
                                </form>
                                <form method="POST" onsubmit="return confirm('Delete application #<?= (int)$r['id'] ?>?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger btn-icon" title="Delete">🗑</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$rows): ?>
                    <tr><td colspan="9" class="text-center text-muted py-5">No applications found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>