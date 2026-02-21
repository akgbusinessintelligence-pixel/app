<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Rental Application</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { background:#f5f6f8; }
.form-section { background:#fff; padding:20px; border-radius:6px; margin-bottom:20px; }
.section-title { font-weight:600; border-bottom:1px solid #ddd; padding-bottom:6px; margin-bottom:15px; }
#drop-area { background:#fafafa; border:2px dashed #cfd4da; cursor:pointer; transition:.2s; }
#drop-area.dragover { background:#eef6ff; border-color:#0d6efd; }
#fileList div { font-size:14px; padding:4px 0; }
</style>
</head>

<body>
<div class="container my-5">
  <h3 class="text-center mb-4">RENTOR PRO<br>RENTAL APPLICATION</h3>

  <form action="submit.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

    <!-- UNIT -->
    <div class="form-section">
      <div class="row">
        <div class="col-md-6">
          <label class="form-label">Unit *</label>
          <select name="unit" class="form-select" required>
            <option value="">Browse Vacant Units</option>
            <option>Unit 1A</option>
            <option>Unit 2B</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Desired Move-In *</label>
          <input type="date" name="move_in" class="form-control" required>
        </div>
      </div>
    </div>

    <!-- APPLICANT -->
    <div class="form-section">
      <div class="section-title">Applicant Information</div>
      <div class="row">
        <div class="col-md-2">
          <label>Salutation</label>
          <select name="salutation" class="form-select">
            <option></option><option>Mr</option><option>Ms</option><option>Mrs</option>
          </select>
        </div>
        <div class="col-md-4">
          <label>Legal First Name *</label>
          <input type="text" name="first_name" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label>Middle Name</label>
          <input type="text" name="middle_name" class="form-control">
        </div>
        <div class="col-md-3">
          <label>Last Name *</label>
          <input type="text" name="last_name" class="form-control" required>
        </div>
      </div>
    </div>

    <!-- CONTACT -->
    <div class="form-section">
      <div class="section-title">Contact Information</div>
      <div class="row">
        <div class="col-md-6">
          <label>Phone *</label>
          <input type="text" name="phone" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label>Email *</label>
          <input type="email" name="email" class="form-control" required>
        </div>
      </div>
    </div>

    <!-- RESIDENTIAL -->
    <div class="form-section">
      <div class="section-title">Residential History</div>
      <input type="text" name="address1" class="form-control mb-2" placeholder="Current Address *" required>
      <input type="text" name="address2" class="form-control mb-2" placeholder="Apt / Unit">

      <div class="row">
        <div class="col-md-4"><input type="text" name="city" class="form-control" placeholder="City"></div>
        <div class="col-md-4"><input type="text" name="state" class="form-control" placeholder="State"></div>
        <div class="col-md-4"><input type="text" name="zip" class="form-control" placeholder="Zip"></div>
      </div>

      <div class="row mt-3">
        <div class="col-md-3"><label>From</label><input type="month" name="res_from" class="form-control"></div>
        <div class="col-md-3"><label>To</label><input type="month" name="res_to" class="form-control"></div>
        <div class="col-md-3"><label>Monthly Rent</label><input type="number" step="0.01" name="rent" class="form-control"></div>
      </div>

      <div class="row mt-3">
        <div class="col-md-4"><label>Landlord Name</label><input type="text" name="landlord" class="form-control"></div>
        <div class="col-md-4"><label>Landlord Phone</label><input type="text" name="landlord_phone" class="form-control"></div>
        <div class="col-md-4"><label>Landlord Email</label><input type="email" name="landlord_email" class="form-control"></div>
      </div>

      <label class="mt-3">Reason for Leaving</label>
      <textarea name="reason" class="form-control"></textarea>
    </div>

    <!-- PERSONAL -->
    <div class="form-section">
      <div class="section-title">Personal Information</div>
      <div class="row">
        <div class="col-md-4"><label>Date of Birth</label><input type="date" name="dob" class="form-control"></div>
        <div class="col-md-4"><label>SSN / ITIN</label><input type="password" name="ssn" class="form-control"></div>
        <div class="col-md-4"><label>Government ID</label><input type="text" name="gov_id" class="form-control"></div>
      </div>
      <div class="row mt-3">
        <div class="col-md-4"><label>Issuing State</label><input type="text" name="id_state" class="form-control"></div>
      </div>
    </div>

    <!-- EMPLOYMENT -->
    <div class="form-section">
      <div class="section-title">Employment Details</div>
      <div class="row">
        <div class="col-md-6"><label>Employer Name</label><input type="text" name="employer" class="form-control"></div>
        <div class="col-md-6"><label>Monthly Salary</label><input type="number" step="0.01" name="salary" class="form-control"></div>
      </div>
    </div>

    <!-- ADDITIONAL INCOME -->
    <div class="form-section">
      <div class="section-title">Additional Income</div>
      <div class="row">
        <div class="col-md-6">
          <label>Monthly Income</label>
          <input type="number" step="0.01" name="additional_income" class="form-control" placeholder="$0.00">
        </div>
        <div class="col-md-6">
          <label>Source</label>
          <input type="text" name="income_source" class="form-control" placeholder="Benefits, side job, child support, etc.">
        </div>
      </div>
    </div>

    <!-- CO-APPLICANT TOGGLE + SECTION -->
    <div class="form-section">
      <div class="section-title">Co-Applicant</div>

      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="coEnabled" name="co_enabled" value="1">
        <label class="form-check-label" for="coEnabled">Add a Co-Applicant</label>
      </div>

      <div id="coBlock" class="mt-3" style="display:none;">
        <div class="row">
          <div class="col-md-4"><label>First Name</label><input type="text" name="co_first_name" class="form-control"></div>
          <div class="col-md-4"><label>Middle Name</label><input type="text" name="co_middle_name" class="form-control"></div>
          <div class="col-md-4"><label>Last Name</label><input type="text" name="co_last_name" class="form-control"></div>
        </div>

        <div class="row mt-3">
          <div class="col-md-6"><label>Phone</label><input type="text" name="co_phone" class="form-control"></div>
          <div class="col-md-6"><label>Email</label><input type="email" name="co_email" class="form-control"></div>
        </div>

        <div class="row mt-3">
          <div class="col-md-4"><label>Date of Birth</label><input type="date" name="co_dob" class="form-control"></div>
          <div class="col-md-4"><label>SSN / ITIN</label><input type="password" name="co_ssn" class="form-control"></div>
          <div class="col-md-4"><label>Government ID</label><input type="text" name="co_gov_id" class="form-control"></div>
        </div>
      </div>
    </div>

    <!-- BACKGROUND -->
    <div class="form-section">
      <div class="section-title">Background Questions</div>
      <label>Have you ever been evicted?</label><br>
      <input type="radio" name="evicted" value="Yes"> Yes
      <input type="radio" name="evicted" value="No" checked> No

      <hr>

      <label>Any criminal activity in the last 5 years?</label><br>
      <input type="radio" name="criminal" value="Yes"> Yes
      <input type="radio" name="criminal" value="No" checked> No
    </div>

    <!-- ATTACHMENTS -->
    <div class="form-section">
      <div class="section-title">Attachments</div>

      <div id="drop-area" class="p-4 text-center rounded">
        <p class="fw-semibold mb-1">Drag & drop files here</p>
        <p class="text-muted mb-2">or click to upload</p>

        <input type="file" id="fileInput" name="documents[]" multiple hidden>
        <div id="fileList" class="mt-3 text-start"></div>
        <small class="text-muted d-block mt-2">Allowed: PDF, JPG, PNG. Max 10MB per file.</small>
      </div>
    </div>

    <div class="text-center">
      <button class="btn btn-primary px-5 py-2">Submit Application</button>
    </div>

  </form>
</div>

<script>
const coEnabled = document.getElementById('coEnabled');
const coBlock = document.getElementById('coBlock');
coEnabled.addEventListener('change', () => {
  coBlock.style.display = coEnabled.checked ? 'block' : 'none';
});

const dropArea = document.getElementById('drop-area');
const fileInput = document.getElementById('fileInput');
const fileList = document.getElementById('fileList');

dropArea.addEventListener('click', () => fileInput.click());
dropArea.addEventListener('dragover', e => { e.preventDefault(); dropArea.classList.add('dragover'); });
dropArea.addEventListener('dragleave', () => dropArea.classList.remove('dragover'));
dropArea.addEventListener('drop', e => {
  e.preventDefault();
  dropArea.classList.remove('dragover');
  fileInput.files = e.dataTransfer.files;
  showFiles();
});
fileInput.addEventListener('change', showFiles);

function showFiles() {
  fileList.innerHTML = '';
  Array.from(fileInput.files).forEach(file => {
    const div = document.createElement('div');
    div.textContent = '📎 ' + file.name;
    fileList.appendChild(div);
  });
}
</script>
</body>
</html>
