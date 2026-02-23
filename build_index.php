<?php
$html = <<<'HTMLDOC'
<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Application | Extra Property Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
</head>
<body>
<div class="application-container">
    <div class="step-header">
        <h1 class="brand-title">EXTRA PROPERTY MANAGEMENT</h1>
        <p class="text-muted mt-2">Rental Application Form</p>
    </div>
    <div class="form-card">
        <div class="step-indicator mb-5">
            <div class="step active" data-step="1">1</div>
            <div class="step" data-step="2">2</div>
            <div class="step" data-step="3">3</div>
            <div class="step" data-step="4">4</div>
            <div class="step" data-step="5">5</div>
            <div class="step" data-step="6">6</div>
            <div class="step" data-step="7">7</div>
            <div class="step" data-step="8">💳</div>
        </div>
        <form id="multiStepForm" action="submit.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

            <!-- STEP 1 -->
            <div class="form-step" id="step1">
                <h4 class="section-title">Get Started</h4>
                <div class="row g-4">
                    <div class="col-md-12">
                        <label class="form-label">Rental Unit *</label>
                        <select name="unit" id="unit_select" class="form-select" required>
                            <option value="">Select a vacant unit...</option>
                            <option value="123 Main St, Apt 4">123 Main St, Apt 4</option>
                            <option value="456 Oak Ave">456 Oak Ave</option>
                            <option value="789 Pine Rd, Unit B">789 Pine Rd, Unit B</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Desired Move-In Date *</label>
                        <input type="date" name="move_in" id="move_in_date" class="form-control" required>
                    </div>
                </div>
                <div class="mt-5 d-flex justify-content-end">
                    <button type="button" class="btn btn-primary next-step px-4" id="next1">Save &amp; Continue</button>
                </div>
            </div>

            <!-- STEP 2 -->
            <div class="form-step d-none" id="step2">
                <h4 class="section-title">Personal Information</h4>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">First Name *</label><input type="text" name="first_name" id="first_name" class="form-control" required></div>
                    <div class="col-md-2"><label class="form-label">Middle</label><input type="text" name="middle_name" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Last Name *</label><input type="text" name="last_name" id="last_name" class="form-control" required></div>
                    <div class="col-md-2"><label class="form-label">Suffix</label><select name="suffix" class="form-select"><option value=""></option><option>Jr.</option><option>Sr.</option><option>II</option><option>III</option></select></div>
                    <div class="col-md-4"><label class="form-label">Date of Birth *</label><input type="date" name="dob" id="dob" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">Email *</label><input type="email" name="email" id="email" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control" placeholder="(555) 000-0000"></div>
                    <div class="col-md-6"><label class="form-label">SSN / ITIN *</label><input type="password" name="ssn" id="ssn" class="form-control" placeholder="XXX-XX-XXXX" required></div>
                    <div class="col-md-6"><label class="form-label">Confirm SSN *</label><input type="password" id="ssn_confirm" class="form-control" placeholder="Re-enter SSN" required></div>
                    <div class="col-md-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="sms_opt_in" value="1" id="smsOpt">
                            <label class="form-check-label" for="smsOpt">I agree to receive text message updates about this application</label>
                        </div>
                    </div>
                </div>
                <div class="mt-5 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary prev-step px-4">Back</button>
                    <button type="button" class="btn btn-primary next-step px-4" id="next2">Save &amp; Continue</button>
                </div>
            </div>

            <!-- STEP 3 -->
            <div class="form-step d-none" id="step3">
                <h4 class="section-title">Residential History</h4>
                <p class="text-muted small mb-3">Please provide your address history for the past 3 years, starting with your current address.</p>
                <div id="addressList">
                    <div class="dynamic-item p-3 border rounded shadow-sm bg-white mb-3">
                        <h6 class="fw-semibold text-primary mb-3">Current Address</h6>
                        <div class="row g-3">
                            <div class="col-md-12"><label>Street Address *</label><input type="text" name="addresses[0][address]" id="curr_addr" class="form-control" required></div>
                            <div class="col-md-4"><label>City</label><input type="text" name="addresses[0][city]" class="form-control"></div>
                            <div class="col-md-4"><label>State</label><input type="text" name="addresses[0][state]" class="form-control"></div>
                            <div class="col-md-4"><label>Zip</label><input type="text" name="addresses[0][zip]" class="form-control"></div>
                            <div class="col-md-6"><label>Monthly Rent</label><input type="number" name="addresses[0][rent]" class="form-control" placeholder="$"></div>
                            <div class="col-md-6"><label>Duration of Stay</label><input type="text" name="addresses[0][duration]" class="form-control" placeholder="e.g. 2 years"></div>
                            <div class="col-md-12"><label>Reason for Leaving</label><input type="text" name="addresses[0][reason]" class="form-control"></div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addAddress">+ Add Previous Address</button>
                <div class="mt-5 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary prev-step px-4">Back</button>
                    <button type="button" class="btn btn-primary next-step px-4" id="next3">Save &amp; Continue</button>
                </div>
            </div>

            <!-- STEP 4 -->
            <div class="form-step d-none" id="step4">
                <h4 class="section-title">Income &amp; Employment</h4>
                <div id="employmentList">
                    <div class="dynamic-item p-3 border rounded mb-3 bg-white">
                        <h6 class="fw-semibold text-primary mb-3">Primary Employer</h6>
                        <div class="row g-3">
                            <div class="col-md-6"><label>Employer Name</label><input type="text" name="employment[0][employer]" class="form-control"></div>
                            <div class="col-md-6"><label>Job Title</label><input type="text" name="employment[0][title]" class="form-control"></div>
                            <div class="col-md-6"><label>Employer Phone</label><input type="tel" name="employment[0][phone]" class="form-control"></div>
                            <div class="col-md-6"><label>Monthly Income</label><input type="number" name="employment[0][income]" class="form-control" placeholder="$"></div>
                            <div class="col-md-6"><label>Employment Start Date</label><input type="date" name="employment[0][start_date]" class="form-control"></div>
                            <div class="col-md-6"><label>Employment Type</label><select name="employment[0][type]" class="form-select"><option>Full-Time</option><option>Part-Time</option><option>Self-Employed</option><option>Contract</option></select></div>
                        </div>
                    </div>
                </div>
                <h6 class="mt-3 fw-semibold">Additional Income Sources</h6>
                <div class="row g-3 mt-1">
                    <div class="col-md-6"><label>Source (e.g. Alimony, Child Support)</label><input type="text" name="add_income_source" class="form-control"></div>
                    <div class="col-md-6"><label>Monthly Amount</label><input type="number" name="add_income_amount" class="form-control" placeholder="$"></div>
                </div>
                <div class="mt-5 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary prev-step px-4">Back</button>
                    <button type="button" class="btn btn-primary next-step px-4" id="next4">Save &amp; Continue</button>
                </div>
            </div>

            <!-- STEP 5 -->
            <div class="form-step d-none" id="step5">
                <h4 class="section-title">Household Members</h4>
                <p class="text-muted small mb-3">List all persons who will reside in the unit, including co-applicants and dependents.</p>
                <div id="houseList">
                    <div class="dynamic-item p-3 border rounded mb-3 bg-white">
                        <div class="row g-3">
                            <div class="col-md-4"><label>Full Name</label><input type="text" name="household[0][name]" class="form-control"></div>
                            <div class="col-md-4"><label>Relationship</label><select name="household[0][rel]" class="form-select"><option>Spouse/Partner</option><option>Child</option><option>Parent</option><option>Other</option></select></div>
                            <div class="col-md-4"><label>Date of Birth</label><input type="date" name="household[0][dob]" class="form-control"></div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" id="addHousehold">+ Add Member</button>
                <h5 class="mt-4 fw-semibold">Pets</h5>
                <div id="petList">
                    <div class="dynamic-item p-3 border rounded mb-3 bg-white">
                        <div class="row g-3">
                            <div class="col-md-3"><label>Type</label><input type="text" name="pets[0][type]" class="form-control" placeholder="Dog, Cat..."></div>
                            <div class="col-md-3"><label>Breed</label><input type="text" name="pets[0][breed]" class="form-control"></div>
                            <div class="col-md-3"><label>Weight (lbs)</label><input type="number" name="pets[0][weight]" class="form-control"></div>
                            <div class="col-md-3"><label>Age</label><input type="text" name="pets[0][age]" class="form-control"></div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" id="addPet">+ Add Pet</button>
                <h5 class="mt-4 fw-semibold">Vehicles</h5>
                <div id="vehicleList">
                    <div class="dynamic-item p-3 border rounded mb-3 bg-white">
                        <div class="row g-3">
                            <div class="col-md-3"><label>Make</label><input type="text" name="vehicles[0][make]" class="form-control"></div>
                            <div class="col-md-3"><label>Model</label><input type="text" name="vehicles[0][model]" class="form-control"></div>
                            <div class="col-md-3"><label>Year</label><input type="text" name="vehicles[0][year]" class="form-control"></div>
                            <div class="col-md-3"><label>License Plate</label><input type="text" name="vehicles[0][plate]" class="form-control"></div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" id="addVehicle">+ Add Vehicle</button>
                <div class="mt-5 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary prev-step px-4">Back</button>
                    <button type="button" class="btn btn-primary next-step px-4" id="next5">Save &amp; Continue</button>
                </div>
            </div>

            <!-- STEP 6 -->
            <div class="form-step d-none" id="step6">
                <h4 class="section-title">Background Questions</h4>
                <div class="mb-3 p-3 border rounded">
                    <label class="fw-semibold d-block mb-2">Have you ever been evicted or asked to vacate a premises?</label>
                    <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="evicted" value="No" checked><label class="form-check-label">No</label></div>
                    <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="evicted" value="Yes"><label class="form-check-label">Yes</label></div>
                    <input type="text" name="evicted_explain" class="form-control mt-2" placeholder="If yes, please explain...">
                </div>
                <div class="mb-3 p-3 border rounded">
                    <label class="fw-semibold d-block mb-2">Have you ever been convicted of a felony within the past 7 years?</label>
                    <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="criminal" value="No" checked><label class="form-check-label">No</label></div>
                    <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="criminal" value="Yes"><label class="form-check-label">Yes</label></div>
                    <input type="text" name="criminal_explain" class="form-control mt-2" placeholder="If yes, please explain...">
                </div>
                <div class="mb-3 p-3 border rounded">
                    <label class="fw-semibold d-block mb-2">Have you ever declared bankruptcy?</label>
                    <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="bankruptcy" value="No" checked><label class="form-check-label">No</label></div>
                    <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="bankruptcy" value="Yes"><label class="form-check-label">Yes</label></div>
                </div>
                <h5 class="mt-4">Supporting Documents</h5>
                <p class="text-muted small">Upload pay stubs, bank statements, photo ID, or other supporting documents.</p>
                <div class="p-5 border-dashed rounded bg-light text-center cursor-pointer" id="drop-area">
                    <p class="fw-bold mb-1">Click or drag files here</p>
                    <small class="text-muted">Accepted: PDF, JPG, PNG (max 10MB each)</small>
                    <input type="file" name="documents[]" id="fileInput" multiple hidden accept=".pdf,.jpg,.jpeg,.png">
                </div>
                <div id="fileList" class="mt-3"></div>
                <div class="mt-5 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary prev-step px-4">Back</button>
                    <button type="button" class="btn btn-primary next-step px-4" id="next6">Save &amp; Continue</button>
                </div>
            </div>

            <!-- STEP 7 -->
            <div class="form-step d-none" id="step7">
                <h4 class="section-title">Review &amp; Sign</h4>
                <div class="alert alert-info mb-4">
                    <strong>Application Fee:</strong> A non-refundable application fee may be required. By submitting, you consent to a credit and background check.
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Electronic Signature (Type Full Legal Name) *</label>
                    <input type="text" name="signature" id="signature" class="form-control" style="font-family:serif; font-style:italic; font-size:1.5rem;" placeholder="Sign here..." required>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="authorized" id="auth_bg" value="1" required>
                    <label class="form-check-label" for="auth_bg">I authorize a credit and background check and certify that all information provided is true and accurate.</label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="terms" id="auth_terms" value="1" required>
                    <label class="form-check-label" for="auth_terms">I agree to the rental application terms and conditions.</label>
                </div>
                <div class="mt-5 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary prev-step px-4">Back</button>
                    <button type="button" class="btn btn-primary next-step px-4" id="next7">Pay &amp; Submit &rarr;</button>
                </div>
            </div>

            <!-- STEP 8: CASHAPP PAYMENT -->
            <div class="form-step d-none" id="step8">
                <h4 class="section-title">Application Fee Payment</h4>

                <!-- Fee Summary Card -->
                <div class="p-4 rounded-3 mb-4" style="background: linear-gradient(135deg, #00d632 0%, #00a825 100%); color: white;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="font-size: 3rem;">💵</div>
                        <div>
                            <div class="fw-bold" style="font-size: 1.1rem; opacity: .85;">Non-Refundable Application Fee</div>
                            <div style="font-size: 2.5rem; font-weight: 800; line-height: 1;">$50.00</div>
                        </div>
                    </div>
                </div>

                <!-- CashApp Instructions -->
                <div class="border rounded-3 p-4 mb-4 bg-white">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span style="background:#00d632; color:#fff; border-radius:50%; width:32px; height:32px; display:flex; align-items:center; justify-content:center; font-weight:bold;">1</span>
                        <p class="mb-0 fw-semibold">Open <strong>Cash App</strong> on your phone</p>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span style="background:#00d632; color:#fff; border-radius:50%; width:32px; height:32px; display:flex; align-items:center; justify-content:center; font-weight:bold;">2</span>
                        <p class="mb-0">Search for or scan the QR below to send <strong>$50.00</strong> to:</p>
                    </div>

                    <!-- CashTag Display -->
                    <div class="text-center my-4">
                        <div style="display:inline-block; background:#00d632; color:#fff; padding: 10px 28px; border-radius: 50px; font-size: 1.6rem; font-weight: 800; letter-spacing: 2px;">
                            $hrvasquez
                        </div>
                    </div>

                    <!-- QR Code using free API -->
                    <div class="text-center mb-3">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=https://cash.app/$hrvasquez/50"
                             alt="CashApp QR Code" class="img-fluid border rounded p-2" style="max-width:200px;">
                        <p class="text-muted small mt-2">Scan with your phone camera or Cash App</p>
                    </div>

                    <!-- Direct Link Button -->
                    <div class="text-center mb-2">
                        <a href="https://cash.app/$hrvasquez/50" target="_blank" rel="noopener"
                           class="btn btn-lg px-5 fw-bold"
                           style="background:#00d632; border:none; color:#fff; border-radius:50px;">
                            &#128247; Open in Cash App
                        </a>
                    </div>

                    <div class="d-flex align-items-center gap-2 mt-4">
                        <span style="background:#00d632; color:#fff; border-radius:50%; width:32px; height:32px; display:flex; align-items:center; justify-content:center; font-weight:bold;">3</span>
                        <p class="mb-0">In the <strong>memo/note</strong> field, enter your full name and the unit you applied for.</p>
                    </div>
                </div>

                <!-- Payment Confirmation Fields -->
                <div class="p-4 border rounded-3 bg-light">
                    <h6 class="fw-bold mb-3">Confirm Your Payment</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Your CashApp $Cashtag *</label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold" style="background:#00d632; color:#fff; border-color:#00d632;">$</span>
                                <input type="text" name="cashapp_cashtag" id="cashapp_cashtag" class="form-control" placeholder="YourCashtag" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Transaction ID *</label>
                            <input type="text" name="cashapp_txn_id" id="cashapp_txn_id" class="form-control" placeholder="e.g. 5N3XXXXXXX" required>
                            <div class="form-text">Found in Cash App &rarr; Activity &rarr; your payment.</div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Payment Screenshot (optional but recommended)</label>
                            <input type="file" name="payment_screenshot" id="payment_screenshot" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text">Upload a screenshot of the completed payment from Cash App.</div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning mt-4 d-flex align-items-start gap-2">
                    <span style="font-size:1.3rem;">&#9888;&#65039;</span>
                    <div><strong>Non-refundable:</strong> The $50 application fee is non-refundable regardless of approval status. Your application will not be reviewed until payment is confirmed.</div>
                </div>

                <div class="mt-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary prev-step px-4">Back</button>
                    <button type="submit" class="btn btn-success px-5 fw-bold" id="submit_final">
                        &#10003; Submit Application
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
let currentStep = 1;
const totalSteps = 8;

const showStep = (s) => {
    document.querySelectorAll('.form-step').forEach(el => el.classList.add('d-none'));
    const target = document.getElementById('step' + s);
    if(target) target.classList.remove('d-none');
    document.querySelectorAll('.step').forEach(el => {
        const val = parseInt(el.dataset.step);
        el.classList.remove('active', 'completed');
        if(val === s) el.classList.add('active');
        if(val < s) el.classList.add('completed');
    });
    window.scrollTo(0, 0);
};

document.querySelectorAll('.next-step').forEach(b => {
    b.addEventListener('click', () => {
        const stepEl = document.getElementById('step' + currentStep);
        const inputs = stepEl.querySelectorAll('[required]');
        let valid = true;
        inputs.forEach(i => {
            if(!i.value) { i.classList.add('is-invalid'); valid = false; }
            else i.classList.remove('is-invalid');
        });
        if(currentStep === 2) {
            const ssnVal = document.getElementById('ssn').value;
            const cVal = document.getElementById('ssn_confirm').value;
            if(ssnVal && cVal && ssnVal !== cVal) { alert('SSNs do not match. Please re-enter.'); valid = false; }
        }
        if(valid && currentStep < totalSteps) { currentStep++; showStep(currentStep); }
    });
});

document.querySelectorAll('.prev-step').forEach(b => {
    b.addEventListener('click', () => { if(currentStep > 1) { currentStep--; showStep(currentStep); } });
});

// Dynamic address
let addrCount = 1;
document.getElementById('addAddress').addEventListener('click', () => {
    const n = addrCount++;
    const d = document.createElement('div');
    d.className = 'dynamic-item p-3 border rounded mb-3 bg-white position-relative';
    d.innerHTML = '<button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-item"></button><h6 class="fw-semibold text-muted mb-3">Previous Address ' + n + '</h6><div class="row g-3"><div class="col-md-12"><label>Street Address</label><input type="text" name="addresses[' + n + '][address]" class="form-control"></div><div class="col-md-4"><label>City</label><input type="text" name="addresses[' + n + '][city]" class="form-control"></div><div class="col-md-4"><label>State</label><input type="text" name="addresses[' + n + '][state]" class="form-control"></div><div class="col-md-4"><label>Zip</label><input type="text" name="addresses[' + n + '][zip]" class="form-control"></div></div>';
    document.getElementById('addressList').appendChild(d);
    d.querySelector('.remove-item').onclick = () => d.remove();
});

// Dynamic household
let hhCount = 1;
document.getElementById('addHousehold').addEventListener('click', () => {
    const n = hhCount++;
    const d = document.createElement('div');
    d.className = 'dynamic-item p-3 border rounded mb-3 bg-white position-relative';
    d.innerHTML = '<button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-item"></button><div class="row g-3"><div class="col-md-4"><label>Full Name</label><input type="text" name="household[' + n + '][name]" class="form-control"></div><div class="col-md-4"><label>Relationship</label><input type="text" name="household[' + n + '][rel]" class="form-control"></div><div class="col-md-4"><label>Date of Birth</label><input type="date" name="household[' + n + '][dob]" class="form-control"></div></div>';
    document.getElementById('houseList').appendChild(d);
    d.querySelector('.remove-item').onclick = () => d.remove();
});

// Dynamic pets
let petCount = 1;
document.getElementById('addPet').addEventListener('click', () => {
    const n = petCount++;
    const d = document.createElement('div');
    d.className = 'dynamic-item p-3 border rounded mb-3 bg-white position-relative';
    d.innerHTML = '<button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-item"></button><div class="row g-3"><div class="col-md-3"><label>Type</label><input type="text" name="pets[' + n + '][type]" class="form-control"></div><div class="col-md-3"><label>Breed</label><input type="text" name="pets[' + n + '][breed]" class="form-control"></div><div class="col-md-3"><label>Weight</label><input type="number" name="pets[' + n + '][weight]" class="form-control"></div><div class="col-md-3"><label>Age</label><input type="text" name="pets[' + n + '][age]" class="form-control"></div></div>';
    document.getElementById('petList').appendChild(d);
    d.querySelector('.remove-item').onclick = () => d.remove();
});

// Dynamic vehicles
let vehCount = 1;
document.getElementById('addVehicle').addEventListener('click', () => {
    const n = vehCount++;
    const d = document.createElement('div');
    d.className = 'dynamic-item p-3 border rounded mb-3 bg-white position-relative';
    d.innerHTML = '<button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-item"></button><div class="row g-3"><div class="col-md-3"><label>Make</label><input type="text" name="vehicles[' + n + '][make]" class="form-control"></div><div class="col-md-3"><label>Model</label><input type="text" name="vehicles[' + n + '][model]" class="form-control"></div><div class="col-md-3"><label>Year</label><input type="text" name="vehicles[' + n + '][year]" class="form-control"></div><div class="col-md-3"><label>Plate</label><input type="text" name="vehicles[' + n + '][plate]" class="form-control"></div></div>';
    document.getElementById('vehicleList').appendChild(d);
    d.querySelector('.remove-item').onclick = () => d.remove();
});

// File drop
const dropArea = document.getElementById('drop-area');
const fileInput = document.getElementById('fileInput');
const fileList = document.getElementById('fileList');
if(dropArea) {
    dropArea.onclick = () => fileInput.click();
    dropArea.ondragover = (e) => { e.preventDefault(); dropArea.style.background = '#e8f4ff'; };
    dropArea.ondragleave = () => { dropArea.style.background = ''; };
    dropArea.ondrop = (e) => { e.preventDefault(); dropArea.style.background=''; fileInput.files = e.dataTransfer.files; updateFiles(); };
    fileInput.onchange = updateFiles;
}
function updateFiles() {
    fileList.innerHTML = '';
    Array.from(fileInput.files).forEach(f => {
        const d = document.createElement('div');
        d.className = 'small p-2 border rounded mb-1 bg-white d-flex align-items-center gap-2';
        d.innerHTML = '<span>📎</span><span>' + f.name + '</span><small class="text-muted ms-auto">' + (f.size/1024).toFixed(1) + ' KB</small>';
        fileList.appendChild(d);
    });
}
</script>
</body>
</html>
HTMLDOC;

$result = file_put_contents(__DIR__ . '/index.php', $html);
if ($result !== false) {
    echo "SUCCESS: index.php written (" . $result . " bytes)";
}
else {
    echo "FAILED: Could not write index.php";
}
?>

