<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';
require_admin();

ensure_properties_table($pdo);

$errors = [];
$editProperty = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $pdo->prepare("DELETE FROM properties WHERE id = ?")->execute([(int)($_POST['id'] ?? 0)]);
        header('Location: properties.php?deleted=1');
        exit;
    }

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $name = clean_str($_POST['name'] ?? null);
        $address = clean_str($_POST['address'] ?? null);
        $rent = clean_str($_POST['rent_amount'] ?? null);
        $bedrooms = clean_str($_POST['bedrooms'] ?? null);
        $bathrooms = clean_str($_POST['bathrooms'] ?? null);
        $status = clean_str($_POST['status'] ?? null) ?? 'Available';

        if (!$name) $errors[] = 'Property name is required.';
        if (!$address) $errors[] = 'Address is required.';
        if ($rent === null || !is_numeric($rent) || (float)$rent < 0) $errors[] = 'Rent amount must be a valid number.';
        if ($bedrooms === null || !ctype_digit((string)$bedrooms)) $errors[] = 'Bedrooms must be a whole number.';
        if ($bathrooms === null || !is_numeric($bathrooms) || (float)$bathrooms < 0) $errors[] = 'Bathrooms must be a valid number.';
        if (!in_array($status, ['Available', 'Rented'], true)) $errors[] = 'Invalid status.';

        if (!$errors) {
            if ($id > 0) {
                $pdo->prepare("UPDATE properties SET name = ?, address = ?, rent_amount = ?, bedrooms = ?, bathrooms = ?, status = ? WHERE id = ?")
                    ->execute([$name, $address, (float)$rent, (int)$bedrooms, (float)$bathrooms, $status, $id]);
                header('Location: properties.php?saved=1');
                exit;
            }

            $pdo->prepare("INSERT INTO properties (name, address, rent_amount, bedrooms, bathrooms, status) VALUES (?, ?, ?, ?, ?, ?)")
                ->execute([$name, $address, (float)$rent, (int)$bedrooms, (float)$bathrooms, $status]);
            header('Location: properties.php?created=1');
            exit;
        }

        $editProperty = [
            'id' => $id,
            'name' => $name,
            'address' => $address,
            'rent_amount' => $rent,
            'bedrooms' => $bedrooms,
            'bathrooms' => $bathrooms,
            'status' => $status,
        ];
    }
}

if (!$editProperty && isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT id, name, address, rent_amount, bedrooms, bathrooms, status FROM properties WHERE id = ?");
    $stmt->execute([$id]);
    $editProperty = $stmt->fetch();
}

$properties = get_properties($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Properties | Rentor Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; }
        .sidebar { width: 240px; min-height: 100vh; background: #1a1f36; position: fixed; top:0; left:0; z-index:100; }
        .sidebar .logo { padding: 24px 20px; border-bottom: 1px solid rgba(255,255,255,.08); }
        .sidebar .logo h5 { color:#fff; font-weight:800; margin:0; font-size:.95rem; letter-spacing:.5px; }
        .sidebar .logo small { color:rgba(255,255,255,.4); font-size:.75rem; }
        .sidebar nav a { display:flex; align-items:center; gap:10px; padding:12px 20px; color:rgba(255,255,255,.65); text-decoration:none; font-size:.875rem; transition:.2s; }
        .sidebar nav a:hover, .sidebar nav a.active { background:rgba(255,255,255,.08); color:#fff; }
        .main { margin-left:240px; padding:30px; }
        .card-panel { background:#fff; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.06); border:1px solid #e9ecef; }
        @media(max-width:768px) { .sidebar{display:none;} .main{margin-left:0;} }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="logo">
        <h5>🏠 Rentor Pro</h5>
        <small>Admin Dashboard</small>
    </div>
    <nav class="mt-2">
        <a href="index.php"><span>📋</span> Applications</a>
        <a href="properties.php" class="active"><span>🏘️</span> Properties</a>
        <a href="logout.php"><span>🚪</span> Logout</a>
    </nav>
</div>

<div class="main">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0">Property Management</h4>
    </div>

    <?php if (isset($_GET['created'])): ?><div class="alert alert-success">Property added successfully.</div><?php endif; ?>
    <?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Property updated successfully.</div><?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Property deleted successfully.</div><?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <div class="card-panel p-4 mb-4">
        <h6 class="fw-semibold mb-3"><?= $editProperty ? 'Edit Property' : 'Add Property' ?></h6>
        <form method="POST" class="row g-3">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= (int)($editProperty['id'] ?? 0) ?>">
            <div class="col-md-6">
                <label class="form-label">Property Name</label>
                <input class="form-control" name="name" required value="<?= htmlspecialchars((string)($editProperty['name'] ?? '')) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Address</label>
                <input class="form-control" name="address" required value="<?= htmlspecialchars((string)($editProperty['address'] ?? '')) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Rent Amount</label>
                <input class="form-control" type="number" step="0.01" min="0" name="rent_amount" required value="<?= htmlspecialchars((string)($editProperty['rent_amount'] ?? '')) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Bedrooms</label>
                <input class="form-control" type="number" min="0" name="bedrooms" required value="<?= htmlspecialchars((string)($editProperty['bedrooms'] ?? '')) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Bathrooms</label>
                <input class="form-control" type="number" step="0.5" min="0" name="bathrooms" required value="<?= htmlspecialchars((string)($editProperty['bathrooms'] ?? '')) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status" required>
                    <?php $currStatus = $editProperty['status'] ?? 'Available'; ?>
                    <option value="Available" <?= $currStatus === 'Available' ? 'selected' : '' ?>>Available</option>
                    <option value="Rented" <?= $currStatus === 'Rented' ? 'selected' : '' ?>>Rented</option>
                </select>
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-primary"><?= $editProperty ? 'Save Property' : 'Add Property' ?></button>
                <?php if ($editProperty): ?><a href="properties.php" class="btn btn-outline-secondary">Cancel</a><?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card-panel">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Rent</th>
                        <th>Beds</th>
                        <th>Baths</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($properties as $p): ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= htmlspecialchars($p['address']) ?></td>
                        <td>$<?= number_format((float)$p['rent_amount'], 2) ?></td>
                        <td><?= (int)$p['bedrooms'] ?></td>
                        <td><?= htmlspecialchars((string)$p['bathrooms']) ?></td>
                        <td>
                            <span class="badge <?= $p['status'] === 'Available' ? 'bg-success' : 'bg-secondary' ?>">
                                <?= htmlspecialchars($p['status']) ?>
                            </span>
                        </td>
                        <td class="d-flex gap-2">
                            <a href="properties.php?edit=<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form method="POST" onsubmit="return confirm('Delete this property?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$properties): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No properties yet. Add your first property above.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
