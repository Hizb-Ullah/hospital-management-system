<?php
// hospital/pages/admin_dashboard.php
$page_title = 'Medicare Portal Dashboard';
$include_dashboard_css = true;
$include_dashboard_js = true;
require_once '../includes/header.php';
require_once '../includes/db.php';

// Auth checks
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['role'];
if ($role === 'patient') {
    header('Location: patient_dashboard.php');
    exit;
}

$user_initials = strtoupper(substr($_SESSION['first_name'], 0, 1) . substr($_SESSION['last_name'], 0, 1));

// Retrieve list of departments for doctor registry creation
$departments = [];
$patients = [];
try {
    $departments = $pdo->query("SELECT * FROM departments ORDER BY name ASC")->fetchAll();
    $patients = $pdo->query("
        SELECT p.id, u.first_name, u.last_name 
        FROM patients p 
        JOIN users u ON p.user_id = u.id 
        ORDER BY u.first_name ASC
    ")->fetchAll();
} catch (PDOException $e) {
    // Suppress
}
?>

<div class="dashboard-container fade-in">
    <!-- Dynamic Sidebar Navigation -->
    <aside class="dashboard-sidebar">
        <div class="user-profile-summary">
            <div class="user-avatar-placeholder"><?php echo $user_initials; ?></div>
            <div class="user-profile-info">
                <h3><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></h3>
                <span style="text-transform:uppercase; font-size:0.75rem; letter-spacing:0.5px; font-weight:700; color:var(--primary);"><?php echo $role; ?> Portal</span>
            </div>
        </div>
        
        <ul class="sidebar-menu">
            <li><a class="sidebar-link active" data-tab="overview-tab">📊 Overview</a></li>
            <li><a class="sidebar-link" data-tab="appointments-tab">📅 Appointments</a></li>
            
            <?php if ($role === 'admin'): ?>
                <li><a class="sidebar-link" data-tab="doctors-tab">👨‍⚕️ Manage Doctors</a></li>
                <li><a class="sidebar-link" data-tab="departments-tab">🏥 Manage Departments</a></li>
            <?php endif; ?>
            
            <li><a class="sidebar-link" data-tab="records-tab">🧬 Medical History</a></li>
            <li><a class="sidebar-link" data-tab="billing-tab">💳 Billing & Finance</a></li>
        </ul>
    </aside>

    <!-- Main Content Area -->
    <main class="dashboard-main">
        <div class="dashboard-header">
            <div>
                <h1>Medicare System Portal</h1>
                <p>Welcome back! Below are your real-time clinic operations statistics and tasks.</p>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: OVERVIEW -->
        <!-- ========================================== -->
        <div id="overview-tab" class="dashboard-tab-content active">
            <!-- Counters System -->
            <div class="stats-cards-grid">
                <?php if ($role === 'admin'): ?>
                    <div class="stat-card">
                        <div class="stat-card-info">
                            <h4>Total Patients</h4>
                            <div class="stat-value" id="stat-admin-patients">0</div>
                        </div>
                        <div class="stat-card-icon">👥</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-info">
                            <h4>Doctors Registry</h4>
                            <div class="stat-value" id="stat-admin-doctors">0</div>
                        </div>
                        <div class="stat-card-icon">👨‍⚕️</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-info">
                            <h4>Total Bookings</h4>
                            <div class="stat-value" id="stat-admin-appts">0</div>
                        </div>
                        <div class="stat-card-icon">📅</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-info">
                            <h4>Paid Revenue</h4>
                            <div class="stat-value" id="stat-admin-revenue">$0.00</div>
                        </div>
                        <div class="stat-card-icon">💰</div>
                    </div>
                <?php elseif ($role === 'doctor'): ?>
                    <div class="stat-card">
                        <div class="stat-card-info">
                            <h4>Visits Scheduled Today</h4>
                            <div class="stat-value" id="stat-doc-today">0</div>
                        </div>
                        <div class="stat-card-icon">📅</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-info">
                            <h4>Pending Approval</h4>
                            <div class="stat-value" id="stat-doc-pending">0</div>
                        </div>
                        <div class="stat-card-icon">⏳</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-info">
                            <h4>Treated Patients</h4>
                            <div class="stat-value" id="stat-doc-treated">0</div>
                        </div>
                        <div class="stat-card-icon">🧬</div>
                    </div>
                <?php elseif ($role === 'nurse'): ?>
                    <div class="stat-card">
                        <div class="stat-card-info">
                            <h4>Pending Bookings</h4>
                            <div class="stat-value" id="stat-doc-pending">0</div>
                        </div>
                        <div class="stat-card-icon">⏳</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-info">
                            <h4>Hospital Patients</h4>
                            <div class="stat-value" id="stat-admin-patients">0</div>
                        </div>
                        <div class="stat-card-icon">👥</div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Double split summary tables -->
            <div class="dashboard-grid-split">
                <!-- Recent Appointments table -->
                <div class="table-container" style="margin-bottom:0;">
                    <div class="table-header-actions">
                        <h3>Recent Appointments Activity</h3>
                    </div>
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="recent-appointments-list">
                            <tr>
                                <td colspan="5" style="text-align:center;">Loading recent activity...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Admin specific recent payments -->
                <?php if ($role === 'admin'): ?>
                    <div class="table-container" style="margin-bottom:0;">
                        <div class="table-header-actions">
                            <h3>Received Payments (Recent)</h3>
                        </div>
                        <table class="custom-table">
                            <thead>
                                <th>Txn ID</th>
                                <th>Patient</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Date</th>
                            </thead>
                            <tbody id="recent-payments-list">
                                <tr>
                                    <td colspan="5" style="text-align:center;">Loading recent transactions...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="card-panel">
                        <h3>Hospital Overview Summary</h3>
                        <p style="margin-bottom:1rem;">Manage active appointments, review incoming patients diagnostic files, and verify prescription details. Always verify patient IDs before completing clinical visits.</p>
                        <div style="background:var(--primary-soft); padding:1rem; border-radius:var(--radius-md); border-left:4px solid var(--primary);">
                            <strong>💡 Quick Tip:</strong> Click the "Appointments" tab in your sidebar to approve, cancel, or complete patient consultations.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: APPOINTMENTS -->
        <!-- ========================================== -->
        <div id="appointments-tab" class="dashboard-tab-content">
            <div class="table-container">
                <div class="table-header-actions">
                    <h3>Consultations Register</h3>
                </div>
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="full-appointments-list">
                        <tr>
                            <td colspan="7" style="text-align:center;">Loading appointments...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 3: MANAGE DOCTORS (ADMIN ONLY) -->
        <!-- ========================================== -->
        <?php if ($role === 'admin'): ?>
            <div id="doctors-tab" class="dashboard-tab-content">
                <div class="dashboard-grid-split" style="grid-template-columns: 1fr 1.2fr; margin-top:0;">
                    <!-- Add Doctor Form -->
                    <div class="card-panel">
                        <h3>Register New Doctor</h3>
                        <form id="admin-add-doctor-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="doc-first_name">First Name *</label>
                                    <input type="text" id="doc-first_name" name="first_name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="doc-last_name">Last Name *</label>
                                    <input type="text" id="doc-last_name" name="last_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="doc-email">Email Address *</label>
                                <input type="email" id="doc-email" name="email" class="form-control" required autocomplete="email">
                            </div>
                            <div class="form-group">
                                <label for="doc-department">Department Specialty *</label>
                                <select id="doc-department" name="department_id" class="form-control" required>
                                    <option value="">Select Specialty...</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="doc-specialization">Sub-specialization Details</label>
                                <input type="text" id="doc-specialization" name="specialization" class="form-control" placeholder="e.g. Heart Transplant Surgery">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="doc-exp">Experience (Years)</label>
                                    <input type="number" id="doc-exp" name="experience_years" class="form-control" min="0" value="1">
                                </div>
                                <div class="form-group">
                                    <label for="doc-fee">Consultation Fee ($) *</label>
                                    <input type="number" step="0.01" id="doc-fee" name="consultation_fee" class="form-control" value="80.00" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="doc-bio">Physician Bio</label>
                                <textarea id="doc-bio" name="bio" class="form-control" rows="2" placeholder="Briefly describe background..."></textarea>
                            </div>
                            <h4 style="margin-top:1.5rem; margin-bottom:1rem; border-bottom: 1px dashed var(--border-color); padding-bottom: 0.5rem; font-size: 0.95rem;">Credentials</h4>
                            <div class="form-row" style="margin-bottom:1.5rem;">
                                <div class="form-group">
                                    <label for="doc-user">Username *</label>
                                    <input type="text" id="doc-user" name="username" class="form-control" required autocomplete="username">
                                </div>
                                <div class="form-group">
                                    <label for="doc-pass">Password *</label>
                                    <input type="password" id="doc-pass" name="password" class="form-control" required autocomplete="new-password">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">Register Doctor</button>
                        </form>
                    </div>

                    <!-- Doctors List -->
                    <div class="table-container" style="margin-bottom:0;">
                        <div class="table-header-actions">
                            <h3>Doctors Directory Registry</h3>
                        </div>
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Specialization</th>
                                    <th>Fee</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="admin-doctors-list">
                                <tr>
                                    <td colspan="6" style="text-align:center;">Loading registered doctors...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 4: MANAGE DEPARTMENTS (ADMIN ONLY) -->
            <!-- ========================================== -->
            <div id="departments-tab" class="dashboard-tab-content">
                <div class="dashboard-grid-split" style="grid-template-columns: 1fr 1.2fr; margin-top:0;">
                    <!-- Add Department Form -->
                    <div class="card-panel">
                        <h3>Add New Specialty Department</h3>
                        <form id="admin-add-department-form">
                            <div class="form-group">
                                <label for="dept-name">Department Name *</label>
                                <input type="text" id="dept-name" name="name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="dept-desc">Overview Description</label>
                                <textarea id="dept-desc" name="description" class="form-control" rows="4" placeholder="Briefly details clinical coverage..."></textarea>
                            </div>
                            <div class="form-group" style="margin-bottom:2rem;">
                                <label for="dept-icon">Icon Key</label>
                                <select id="dept-icon" name="icon" class="form-control">
                                    <option value="general-medicine">General Medicine (Stethoscope)</option>
                                    <option value="cardiology">Cardiology (Heart)</option>
                                    <option value="neurology">Neurology (Brain)</option>
                                    <option value="pediatrics">Pediatrics (Baby)</option>
                                    <option value="orthopedics">Orthopedics (Bone)</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">Create Department</button>
                        </form>
                    </div>

                    <!-- Departments list -->
                    <div class="table-container" style="margin-bottom:0;">
                        <div class="table-header-actions">
                            <h3>Hospital Specialty Units</h3>
                        </div>
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Specialty Name</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="admin-departments-list">
                                <tr>
                                    <td colspan="4" style="text-align:center;">Loading departments...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ========================================== -->
        <!-- TAB 5: MEDICAL HISTORY -->
        <!-- ========================================== -->
        <div id="records-tab" class="dashboard-tab-content">
            <div id="full-records-list">
                <div style="text-align:center; padding:3rem; color:#64748b;">Loading history records...</div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 6: BILLING & INVOICING -->
        <!-- ========================================== -->
        <div id="billing-tab" class="dashboard-tab-content">
            <div class="dashboard-grid-split" style="grid-template-columns: <?php echo ($role === 'admin' || $role === 'nurse') ? '1fr 1.5fr' : '1fr'; ?>; margin-top:0;">
                <!-- Generate custom bill form -->
                <?php if ($role === 'admin' || $role === 'nurse'): ?>
                    <div class="card-panel">
                        <h3>Generate Custom Invoice</h3>
                        <p style="font-size:0.85rem; color:#64748b; margin-bottom:1rem;">Invoices will be immediately logged into the patient's billing tab as outstanding.</p>
                        
                        <form id="admin-generate-bill-form">
                            <div class="form-group">
                                <label for="bill-patient">Select Patient *</label>
                                <select id="bill-patient" name="patient_id" class="form-control" required>
                                    <option value="">Select Patient...</option>
                                    <?php foreach ($patients as $pat): ?>
                                        <option value="<?php echo $pat['id']; ?>">
                                            Patient: <?php echo htmlspecialchars($pat['first_name'] . ' ' . $pat['last_name']); ?> (ID: <?php echo $pat['id']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="bill-amount">Invoice Amount ($) *</label>
                                <input type="number" step="0.01" id="bill-amount" name="amount" class="form-control" placeholder="100.00" required>
                            </div>
                            <div class="form-group" style="margin-bottom:2rem;">
                                <label for="bill-due">Payment Due Date *</label>
                                <input type="date" id="bill-due" name="due_date" class="form-control" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">Generate Invoice</button>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- List of all bills -->
                <div class="table-container" style="margin-bottom:0;">
                    <div class="table-header-actions">
                        <h3>Hospital Financial Ledger</h3>
                    </div>
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Patient</th>
                                <th>Amount</th>
                                <th>Service Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="full-billing-list">
                            <tr>
                                <td colspan="6" style="text-align:center;">Loading ledger...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- ========================================== -->
<!-- MEDICAL RECORD INSERTION MODAL (POPUP) -->
<!-- ========================================== -->
<div class="modal-overlay" id="add-record-modal">
    <div class="modal-card" style="max-width: 600px;">
        <div class="modal-header">
            <h3>Add Diagnostic Visit Record</h3>
            <button class="modal-close" id="record-modal-close">&times;</button>
        </div>
        <form id="record-form">
            <input type="hidden" name="patient_id" id="record-patient-id">
            <input type="hidden" name="doctor_id" id="record-doctor-id">
            <input type="hidden" name="appointment_id" id="record-appt-id" value="0">
            
            <div class="modal-body">
                <div class="form-group">
                    <label for="rec-diagnosis">Clinical Diagnosis / Medical Finding *</label>
                    <textarea id="rec-diagnosis" name="diagnosis" class="form-control" rows="3" placeholder="Identify disease, condition or checkup status..." required></textarea>
                </div>
                <div class="form-group">
                    <label for="rec-treatment">Treatment Plan / Therapy Advice *</label>
                    <textarea id="rec-treatment" name="treatment" class="form-control" rows="3" placeholder="Detail clinical recovery steps, procedures, resting orders..." required></textarea>
                </div>
                <div class="form-group">
                    <label for="rec-prescription">Prescriptions *</label>
                    <input type="text" id="rec-prescription" name="prescription" class="form-control" placeholder="e.g. Paracetamol 500mg (3x daily), Amoxicillin 250mg" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" id="btn-record-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Visit History & Complete</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // -------------------------------------------------------------
    // ADMIN ADD DOCTOR HANDLER
    // -------------------------------------------------------------
    const docForm = document.getElementById('admin-add-doctor-form');
    if (docForm) {
        docForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const payload = {
                first_name: docForm.first_name.value.trim(),
                last_name: docForm.last_name.value.trim(),
                email: docForm.email.value.trim(),
                department_id: parseInt(docForm.department_id.value),
                specialization: docForm.specialization.value.trim(),
                experience_years: parseInt(docForm.experience_years.value || 0),
                consultation_fee: parseFloat(docForm.consultation_fee.value || 0),
                bio: docForm.bio.value.trim(),
                username: docForm.username.value.trim(),
                password: docForm.password.value
            };

            try {
                const response = await fetch('../api/doctors.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await response.json();
                if (result.success) {
                    Toast.success('Physician registered successfully!');
                    docForm.reset();
                    loadDoctorsForAdmin();
                    loadDashboardStats();
                } else {
                    Toast.error(result.message);
                }
            } catch (err) {
                Toast.error('Failed to register doctor.');
            }
        });
    }

    // -------------------------------------------------------------
    // ADMIN ADD DEPARTMENT HANDLER
    // -------------------------------------------------------------
    const deptForm = document.getElementById('admin-add-department-form');
    if (deptForm) {
        deptForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const payload = {
                name: deptForm.name.value.trim(),
                description: deptForm.description.value.trim(),
                icon: deptForm.icon.value
            };

            try {
                const response = await fetch('../api/departments.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await response.json();
                if (result.success) {
                    Toast.success('Specialty department unit created.');
                    deptForm.reset();
                    loadDepartmentsForAdmin();
                    
                    // Reload doctor select dropdown
                    window.location.reload();
                } else {
                    Toast.error(result.message);
                }
            } catch (err) {
                Toast.error('Failed to create department.');
            }
        });
    }

    // -------------------------------------------------------------
    // ADMIN GENERATE BILL HANDLER
    // -------------------------------------------------------------
    const billForm = document.getElementById('admin-generate-bill-form');
    if (billForm) {
        // Set minimum due date is tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        billForm.due_date.min = tomorrow.toISOString().split('T')[0];

        billForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const payload = {
                patient_id: parseInt(billForm.patient_id.value),
                amount: parseFloat(billForm.amount.value),
                due_date: billForm.due_date.value
            };

            try {
                const response = await fetch('../api/billing.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await response.json();
                if (result.success) {
                    Toast.success('Patient invoice generated successfully!');
                    billForm.reset();
                    loadBilling();
                    loadDashboardStats();
                } else {
                    Toast.error(result.message);
                }
            } catch (err) {
                Toast.error('Failed to generate patient invoice.');
            }
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
