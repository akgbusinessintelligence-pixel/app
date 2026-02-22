<?php
$content = <<<'PHP'
<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_csrf();

require_once __DIR__ . '/vendor/autoload.php';
use Dompdf\Dompdf;

/* -----------------------------
   1. BASIC REQUIRED FIELDS
--------------------------------*/
$required = ['first_name', 'last_name', 'email'];
foreach ($required as $r) {
    if (empty($_POST[$r])) {
        exit("Missing required field: {$r}");
    }
}

/* -----------------------------
   2. SANITIZE INPUTS
--------------------------------*/
function v($k) {
    return isset($_POST[$k]) ? clean_str($_POST[$k]) : null;
}

$ssn_hash = v('ssn') ? password_hash(v('ssn'), PASSWORD_DEFAULT) : null;

$co_enabled = isset($_POST['co_enabled']) ? 1 : 0;
$co_ssn_hash = $co_enabled && v('co_ssn')
    ? password_hash(v('co_ssn'), PASSWORD_DEFAULT)
    : null;

/* -----------------------------
   3. INSERT APPLICATION
--------------------------------*/
$stmt = $pdo->prepare("
INSERT INTO applications (
    unit, move_in,
    salutation, first_name, middle_name, last_name,
    phone, email,
    address1, address2, city, state, zip,
    res_from, res_to, rent,
    landlord, landlord_phone, landlord_email, reason,
    dob, ssn_hash, gov_id, id_state,
    employer, salary,
    additional_income, income_source,
    evicted, criminal,
    co_enabled, co_first_name, co_middle_name, co_last_name,
    co_phone, co_email, co_dob, co_ssn_hash, co_gov_id
) VALUES (
    :unit, :move_in,
    :salutation, :first_name, :middle_name, :last_name,
    :phone, :email,
    :address1, :address2, :city, :state, :zip,
    :res_from, :res_to, :rent,
    :landlord, :landlord_phone, :landlord_email, :reason,
    :dob, :ssn_hash, :gov_id, :id_state,
    :employer, :salary,
    :additional_income, :income_source,
    :evicted, :criminal,
    :co_enabled, :co_first_name, :co_middle_name, :co_last_name,
    :co_phone, :co_email, :co_dob, :co_ssn_hash, :co_gov_id
)");
$stmt->execute([
    ':unit'          => v('unit')    ?? 'N/A',
    ':move_in'       => v('move_in') ?? null,
    ':salutation'    => v('salutation'),
    ':first_name'    => v('first_name'),
    ':middle_name'   => v('middle_name'),
    ':last_name'     => v('last_name'),
    ':phone'         => v('phone'),
    ':email'         => v('email'),
    ':address1'      => v('address1') ?? (isset($_POST['addresses'][0]['address']) ? clean_str($_POST['addresses'][0]['address']) : null),
    ':address2'      => v('address2'),
    ':city'          => v('city')   ?? (isset($_POST['addresses'][0]['city'])    ? clean_str($_POST['addresses'][0]['city'])    : null),
    ':state'         => v('state')  ?? (isset($_POST['addresses'][0]['state'])   ? clean_str($_POST['addresses'][0]['state'])   : null),
    ':zip'           => v('zip')    ?? (isset($_POST['addresses'][0]['zip'])     ? clean_str($_POST['addresses'][0]['zip'])     : null),
    ':res_from'      => v('res_from'),
    ':res_to'        => v('res_to'),
    ':rent'          => v('rent')   ?? (isset($_POST['addresses'][0]['rent'])    ? clean_str($_POST['addresses'][0]['rent'])    : null),
    ':landlord'      => v('landlord'),
    ':landlord_phone'=> v('landlord_phone'),
    ':landlord_email'=> v('landlord_email'),
    ':reason'        => v('reason') ?? (isset($_POST['addresses'][0]['reason'])  ? clean_str($_POST['addresses'][0]['reason'])  : null),
    ':dob'           => v('dob'),
    ':ssn_hash'      => $ssn_hash,
    ':gov_id'        => v('gov_id'),
    ':id_state'      => v('id_state'),
    ':employer'      => v('employer') ?? (isset($_POST['employment'][0]['employer']) ? clean_str($_POST['employment'][0]['employer']) : null),
    ':salary'        => v('salary')   ?? (isset($_POST['employment'][0]['income'])   ? clean_str($_POST['employment'][0]['income'])   : null),
    ':additional_income' => v('additional_income') ?? v('add_income_amount'),
    ':income_source'     => v('income_source')     ?? v('add_income_source'),
    ':evicted'       => ($_POST['evicted']    ?? 'No') === 'Yes' ? 'Yes' : 'No',
    ':criminal'      => ($_POST['criminal']   ?? 'No') === 'Yes' ? 'Yes' : 'No',
    ':co_enabled'    => $co_enabled,
    ':co_first_name' => $co_enabled ? v('co_first_name')  : null,
    ':co_middle_name'=> $co_enabled ? v('co_middle_name') : null,
    ':co_last_name'  => $co_enabled ? v('co_last_name')   : null,
    ':co_phone'      => $co_enabled ? v('co_phone')       : null,
    ':co_email'      => $co_enabled ? v('co_email')       : null,
    ':co_dob'        => $co_enabled ? v('co_dob')         : null,
    ':co_ssn_hash'   => $co_ssn_hash,
    ':co_gov_id'     => $co_enabled ? v('co_gov_id')      : null,
]);

$app_id = (int)$pdo->lastInsertId();

/* -----------------------------
   3b. SAVE CASHAPP PAYMENT INFO
--------------------------------*/
$cashapp_cashtag = isset($_POST['cashapp_cashtag']) ? clean_str($_POST['cashapp_cashtag']) : null;
$cashapp_txn_id  = isset($_POST['cashapp_txn_id'])  ? clean_str($_POST['cashapp_txn_id'])  : null;

if ($cashapp_cashtag || $cashapp_txn_id) {
    try { $pdo->exec("ALTER TABLE applications ADD COLUMN cashapp_cashtag TEXT"); }   catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE applications ADD COLUMN cashapp_txn_id TEXT");  }   catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE applications ADD COLUMN payment_status TEXT DEFAULT 'pending'"); } catch (PDOException $e) {}

    $upd = $pdo->prepare("UPDATE applications SET cashapp_cashtag=:tag, cashapp_txn_id=:txn, payment_status='pending_review' WHERE id=:id");
    $upd->execute([':tag' => $cashapp_cashtag, ':txn' => $cashapp_txn_id, ':id' => $app_id]);
}

/* -----------------------------
   4. FILE UPLOADS
--------------------------------*/
$fileRows = [];
if (!empty($_FILES['documents']['name'][0])) {
    foreach ($_FILES['documents']['tmp_name'] as $i=>$tmp) {
        if ($_FILES['documents']['error'][$i] !== UPLOAD_ERR_OK) continue;

        $size = $_FILES['documents']['size'][$i];
        if ($size > MAX_FILE_SIZE) continue;

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);
        if (!in_array($mime, $ALLOWED_MIME)) continue;

        $ext = match($mime){
            'application/pdf'=>'pdf',
            'image/jpeg'=>'jpg',
            'image/png'=>'png',
            default=>'bin'
        };

        $stored = "app{$app_id}_".bin2hex(random_bytes(6)).".$ext";
        move_uploaded_file($tmp, UPLOAD_DIR.'/'.$stored);

        $pdo->prepare("
          INSERT INTO application_files
          (application_id, original_name, stored_name, mime_type, file_size, file_path)
          VALUES (?,?,?,?,?,?)
        ")->execute([
            $app_id,
            $_FILES['documents']['name'][$i],
            $stored,
            $mime,
            $size,
            "uploads/".$stored
        ]);

        $fileRows[] = ['original_name'=>$_FILES['documents']['name'][$i]];
    }
}

/* Payment screenshot */
if (!empty($_FILES['payment_screenshot']['tmp_name'])) {
    $tmp  = $_FILES['payment_screenshot']['tmp_name'];
    $size = $_FILES['payment_screenshot']['size'];
    if ($_FILES['payment_screenshot']['error'] === UPLOAD_ERR_OK && $size <= MAX_FILE_SIZE) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($tmp);
        if (in_array($mime, $ALLOWED_MIME)) {
            $ext    = match($mime){ 'application/pdf'=>'pdf','image/jpeg'=>'jpg','image/png'=>'png',default=>'bin' };
            $stored = "payment{$app_id}_".bin2hex(random_bytes(6)).".$ext";
            move_uploaded_file($tmp, UPLOAD_DIR.'/'.$stored);
            $pdo->prepare("INSERT INTO application_files (application_id,original_name,stored_name,mime_type,file_size,file_path) VALUES (?,?,?,?,?,?)")
                ->execute([$app_id, 'payment_screenshot.'.$ext, $stored, $mime, $size, "uploads/".$stored]);
        }
    }
}

/* -----------------------------
   5. FETCH DATA FOR PDF
--------------------------------*/
$app = $pdo->query("SELECT * FROM applications WHERE id={$app_id}")->fetch();

/* -----------------------------
   6. FULL PDF TEMPLATE
--------------------------------*/
ob_start(); ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body{font-family:DejaVu Sans;font-size:12px}
h2{margin-bottom:10px}
.section{border:1px solid #ccc;padding:10px;margin-bottom:10px}
.label{font-weight:bold;width:170px;display:inline-block}
</style>
</head>
<body>

<h2>Rental Application</h2>

<div class="section">
<div><span class="label">Application ID:</span><?= $app['id'] ?></div>
<div><span class="label">Unit:</span><?= $app['unit'] ?></div>
<div><span class="label">Move-In:</span><?= $app['move_in'] ?></div>
</div>

<div class="section">
<h4>Applicant</h4>
<div><span class="label">Name:</span><?= "{$app['first_name']} {$app['middle_name']} {$app['last_name']}" ?></div>
<div><span class="label">Phone:</span><?= $app['phone'] ?></div>
<div><span class="label">Email:</span><?= $app['email'] ?></div>
<div><span class="label">DOB:</span><?= $app['dob'] ?></div>
</div>

<div class="section">
<h4>Address</h4>
<div><?= "{$app['address1']} {$app['address2']}" ?></div>
<div><?= "{$app['city']} {$app['state']} {$app['zip']}" ?></div>
</div>

<div class="section">
<h4>Employment &amp; Income</h4>
<div><span class="label">Employer:</span><?= $app['employer'] ?></div>
<div><span class="label">Salary:</span>$<?= $app['salary'] ?></div>
<div><span class="label">Additional Income:</span>$<?= $app['additional_income'] ?></div>
<div><span class="label">Source:</span><?= $app['income_source'] ?></div>
</div>

<?php if ($app['co_enabled']): ?>
<div class="section">
<h4>Co-Applicant</h4>
<div><?= "{$app['co_first_name']} {$app['co_middle_name']} {$app['co_last_name']}" ?></div>
<div><?= $app['co_phone'] ?> | <?= $app['co_email'] ?></div>
</div>
<?php endif; ?>

<div class="section">
<h4>Payment</h4>
<div><span class="label">CashApp Tag:</span>$<?= htmlspecialchars($app['cashapp_cashtag'] ?? '') ?></div>
<div><span class="label">Transaction ID:</span><?= htmlspecialchars($app['cashapp_txn_id'] ?? '') ?></div>
<div><span class="label">Payment Status:</span><?= htmlspecialchars($app['payment_status'] ?? 'pending') ?></div>
</div>

<div class="section">
<h4>Documents</h4>
<ul>
<?php foreach ($fileRows as $f): ?>
<li><?= htmlspecialchars($f['original_name']) ?></li>
<?php endforeach; ?>
</ul>
</div>

</body>
</html>
<?php
$html = ob_get_clean();

/* -----------------------------
   7. GENERATE PDF
--------------------------------*/
$pdf = new Dompdf();
$pdf->loadHtml($html);
$pdf->setPaper('letter','portrait');
$pdf->render();

$pdfName = "application_{$app_id}.pdf";
file_put_contents(PDF_DIR.'/'.$pdfName, $pdf->output());

$pdo->prepare("UPDATE applications SET pdf_path=? WHERE id=?")
    ->execute(["pdfs/".$pdfName, $app_id]);

/* -----------------------------
   8. DONE
--------------------------------*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application Submitted</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="application-container">
    <div class="step-header">
        <h1 class="brand-title">EXTRA PROPERTY MANAGEMENT</h1>
    </div>
    <div class="form-card text-center py-5">
        <div style="font-size:4rem;">✅</div>
        <h2 class="mt-3 fw-bold text-success">Application Submitted!</h2>
        <p class="text-muted mt-2">Your Application ID is <strong>#<?= $app_id ?></strong></p>
        <hr class="my-4">
        <p>We have received your rental application and payment information.</p>
        <p>Our team will verify your <strong>CashApp payment</strong> and be in touch within <strong>1–2 business days</strong>.</p>
        <div class="mt-4 p-3 rounded-3 d-inline-block" style="background:#f0fdf4; border:1px solid #bbf7d0;">
            <strong>CashApp:</strong> $<?= htmlspecialchars($cashapp_cashtag ?? '—') ?><br>
            <strong>Transaction ID:</strong> <?= htmlspecialchars($cashapp_txn_id ?? '—') ?>
        </div>
        <div class="mt-4">
            <a href="index.php" class="btn btn-outline-primary">Submit Another Application</a>
        </div>
    </div>
</div>
</body>
</html>
PHP;

$result = file_put_contents(__DIR__ . '/submit.php', $content);
if ($result !== false) {
    echo "SUCCESS: submit.php written ($result bytes)";
}
else {
    echo "FAILED: Could not write submit.php";
}
?>
