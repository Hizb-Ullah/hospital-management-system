/* ================================================
   MediCare — Hospital Management System
   main.js  |  Group 16
   ================================================ */


/* ── PASSWORD TOGGLE ─────────────────────────── */
function togglePassword(inputId, btn) {
  const input = document.getElementById(inputId);
  if (!input) return;
  if (input.type === 'password') {
    input.type = 'text';
    btn.textContent = '🙈';
  } else {
    input.type = 'password';
    btn.textContent = '👁';
  }
}


/* ── ROLE CARD SELECTION ─────────────────────── */
function initRoleCards() {
  const cards = document.querySelectorAll('.role-card');
  cards.forEach(card => {
    card.addEventListener('click', () => {
      cards.forEach(c => c.classList.remove('selected'));
      card.classList.add('selected');
      const radio = card.querySelector('input[type="radio"]');
      if (radio) radio.checked = true;
    });
  });
}


/* ── ROLE EXTRA FIELDS (Register Step 3) ──────── */
function getRoleExtraFields(roleValue) {
  switch (roleValue) {
    case '1': // Doctor
      return `
        <div class="form-group">
          <label>Specialization</label>
          <select name="specialization" class="form-control">
            <option value="">Select specialization</option>
            <option>Cardiology</option>
            <option>Neurology</option>
            <option>Orthopedics</option>
            <option>Pediatrics</option>
            <option>Oncology</option>
            <option>General Medicine</option>
            <option>Dermatology</option>
            <option>Psychiatry</option>
            <option>ENT</option>
            <option>Urology</option>
          </select>
        </div>
        <div class="form-group">
          <label>Department</label>
          <select name="department_id" class="form-control">
            <option value="">Select department</option>
            <option value="1">Cardiology</option>
            <option value="2">Neurology</option>
            <option value="3">Orthopedics</option>
            <option value="4">Pediatrics</option>
            <option value="5">Oncology</option>
            <option value="6">General Medicine</option>
          </select>
        </div>`;

    case '2': // Nurse
      return `
        <div class="form-group">
          <label>Department</label>
          <select name="department_id" class="form-control">
            <option value="">Select department</option>
            <option value="1">Cardiology</option>
            <option value="2">Neurology</option>
            <option value="3">Orthopedics</option>
            <option value="4">Pediatrics</option>
            <option value="5">Oncology</option>
            <option value="6">General Medicine</option>
          </select>
        </div>`;

    case '3': // Pharmacist
      return `
        <div class="form-group">
          <label>Pharmacy License Number</label>
          <input type="text" name="license_no" class="form-control" placeholder="e.g. PH-2024-001">
        </div>`;

    case '4': // Technician
      return `
        <div class="form-group">
          <label>Lab Type</label>
          <select name="lab_type" class="form-control">
            <option value="">Select lab type</option>
            <option>Blood Lab</option>
            <option>Radiology</option>
            <option>Microbiology</option>
            <option>Pathology</option>
            <option>Biochemistry</option>
          </select>
        </div>`;

    case '5': // Patient
      return `
        <div class="form-group">
          <label>Home Address</label>
          <input type="text" name="address" class="form-control" placeholder="Street, City">
        </div>`;

    case '6': // Receptionist
      return `
        <div class="form-group">
          <label>Employee ID <span style="color:var(--muted);font-weight:300">(optional)</span></label>
          <input type="text" name="employee_id" class="form-control" placeholder="e.g. EMP-001">
        </div>`;

    default:
      return '';
  }
}


/* ── FORM VALIDATION HELPERS ─────────────────── */
function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function showFieldError(input, message) {
  if (!input) return;
  input.style.borderColor = 'var(--danger)';
  // Remove old error if any
  const old = input.parentNode.querySelector('.form-error');
  if (old) old.remove();
  const err = document.createElement('p');
  err.className = 'form-error';
  err.style.display = 'block';
  err.textContent = message;
  input.parentNode.appendChild(err);
}

function clearAllErrors() {
  document.querySelectorAll('.form-error').forEach(el => el.remove());
  document.querySelectorAll('.form-control').forEach(el => {
    el.style.borderColor = '';
  });
}


/* ── SET MIN DATE TO TODAY ───────────────────── */
function initDatePickers() {
  const today = new Date().toISOString().split('T')[0];
  document.querySelectorAll('input[type="date"]').forEach(input => {
    if (!input.getAttribute('min')) {
      input.setAttribute('min', today);
    }
    if (!input.value) {
      input.value = today;
    }
  });
}


/* ── ACTIVE NAV LINK ─────────────────────────── */
function initActiveNav() {
  const page = window.location.pathname.split('/').pop();
  document.querySelectorAll('.nav-links a').forEach(link => {
    if (link.getAttribute('href') === page ||
        link.getAttribute('href') === './' + page) {
      link.classList.add('active');
    }
  });
}


/* ── INIT ON DOM READY ───────────────────────── */
document.addEventListener('DOMContentLoaded', function () {
  initRoleCards();
  initDatePickers();
  initActiveNav();
});
