<?php
// hospital/pages/patient_dashboard.php
$page_title = 'Patient Portal Dashboard';
$include_dashboard_css = true;
$include_dashboard_js = true;
require_once '../includes/header.php';

// Auth checks
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['role'] !== 'patient') {
    // Redirect admin to admin dashboard
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin_dashboard.php');
    } else {
        // Redirect doctor/nurse to the unified admin dashboard where they manage patients
        header('Location: admin_dashboard.php');
    }
    exit;
}

$user_initials = strtoupper(substr($_SESSION['first_name'], 0, 1) . substr($_SESSION['last_name'], 0, 1));
?>

<div class="dashboard-container fade-in">
    <!-- Sidebar Navigation -->
    <aside class="dashboard-sidebar">
        <div class="user-profile-summary">
            <div class="user-avatar-placeholder"><?php echo $user_initials; ?></div>
            <div class="user-profile-info">
                <h3><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></h3>
                <span>Patient Account</span>
            </div>
        </div>
        
        <ul class="sidebar-menu">
            <li><a class="sidebar-link active" data-tab="overview-tab">📊 Overview</a></li>
            <li><a class="sidebar-link" data-tab="appointments-tab">📅 Appointments</a></li>
            <li><a class="sidebar-link" data-tab="records-tab">🧬 Medical History</a></li>
            <li><a class="sidebar-link" data-tab="billing-tab">💳 Billing & Invoices</a></li>
            <li><a class="sidebar-link" data-tab="profile-tab">⚙️ My Profile</a></li>
        </ul>
    </aside>

    <!-- Main Content Area -->
    <main class="dashboard-main">
        <div class="dashboard-header">
            <div>
                <h1>Patient Portal</h1>
                <p>Manage your appointments, medical files, and bills online.</p>
            </div>
            <a href="appointments.php" class="btn btn-primary">+ Book Appointment</a>
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: OVERVIEW -->
        <!-- ========================================== -->
        <div id="overview-tab" class="dashboard-tab-content active">
            <!-- Stats Counters -->
            <div class="stats-cards-grid">
                <div class="stat-card">
                    <div class="stat-card-info">
                        <h4>Total Appointments</h4>
                        <div class="stat-value" id="stat-total-appts">0</div>
                    </div>
                    <div class="stat-card-icon">📅</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-info">
                        <h4>Unpaid Balance</h4>
                        <div class="stat-value" id="stat-unpaid-bills">$0.00</div>
                    </div>
                    <div class="stat-card-icon">💳</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-info">
                        <h4>Medical Records</h4>
                        <div class="stat-value" id="stat-record-count">0</div>
                    </div>
                    <div class="stat-card-icon">🧬</div>
                </div>
            </div>

            <!-- Double split panel -->
            <div class="dashboard-grid-split">
                <!-- Recent Appointments table -->
                <div class="table-container" style="margin-bottom: 0;">
                    <div class="table-header-actions">
                        <h3>Recent Consultations</h3>
                        <a href="#" class="sidebar-link" data-tab="appointments-tab" style="padding:0; font-size:0.85rem; color:var(--primary);">View All</a>
                    </div>
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Doctor</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="recent-appointments-list">
                            <tr>
                                <td colspan="5" style="text-align:center;">Loading recent appointments...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Recent Medical Records -->
                <div class="card-panel">
                    <h3>Recent Diagnoses</h3>
                    <div id="recent-records-list">
                        <div style="text-align:center; padding:1rem; color:#64748b;">Loading medical history...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: APPOINTMENTS -->
        <!-- ========================================== -->
        <div id="appointments-tab" class="dashboard-tab-content">
            <div class="table-container">
                <div class="table-header-actions">
                    <h3>Scheduled Consultations</h3>
                    <a href="appointments.php" class="btn btn-secondary" style="font-size:0.85rem; padding:0.5rem 1rem;">Schedule Appointment</a>
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
                            <td colspan="7" style="text-align:center;">Loading scheduled visits...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 3: MEDICAL HISTORY -->
        <!-- ========================================== -->
        <div id="records-tab" class="dashboard-tab-content">
            <div id="full-records-list">
                <div style="text-align:center; padding:3rem; color:#64748b;">Loading records...</div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 4: BILLING -->
        <!-- ========================================== -->
        <div id="billing-tab" class="dashboard-tab-content">
            <div class="table-container">
                <div class="table-header-actions">
                    <h3>Outstanding Invoices & Bills</h3>
                </div>
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Invoice ID</th>
                            <th>Details</th>
                            <th>Amount</th>
                            <th>Service Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="full-billing-list">
                        <tr>
                            <td colspan="7" style="text-align:center;">Loading invoice lists...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 5: PROFILE EDIT -->
        <!-- ========================================== -->
        <div id="profile-tab" class="dashboard-tab-content">
            <div class="card-panel" style="max-width:700px; margin:0 auto;">
                <h3>Edit Profile Details</h3>
                <form id="patient-profile-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="prof-first">First Name *</label>
                            <input type="text" id="prof-first" name="first_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="prof-last">Last Name *</label>
                            <input type="text" id="prof-last" name="last_name" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="prof-phone">Phone Number</label>
                            <input type="tel" id="prof-phone" name="phone" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="prof-dob">Date of Birth</label>
                            <input type="date" id="prof-dob" name="dob" class="form-control">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="prof-gender">Gender</label>
                            <select id="prof-gender" name="gender" class="form-control">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="prof-blood">Blood Group</label>
                            <select id="prof-blood" name="blood_group" class="form-control">
                                <option value="">Select Blood Group</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="prof-address">Residential Address</label>
                        <textarea id="prof-address" name="address" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom:2rem;">
                        <label for="prof-emergency">Emergency Contact Details</label>
                        <input type="text" id="prof-emergency" name="emergency_contact" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary">Save Profile Settings</button>
                </form>
            </div>
        </div>
    </main>
</div>

<!-- ========================================== -->
<!-- PAYMENT MODAL (POPUP) -->
<!-- ========================================== -->
<div class="modal-overlay" id="payment-modal">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Invoice Settlement Portal</h3>
            <button class="modal-close" id="pay-modal-close">&times;</button>
        </div>
        <form id="payment-form">
            <div class="modal-body">
                <p style="margin-bottom:1rem; font-size:0.9rem; color:#64748b;">Please verify billing amounts and choose a payment method. Card processing is running in demo environment mode.</p>
                
                <div class="form-group">
                    <label for="payment-amount">Paying Amount ($)</label>
                    <input type="number" step="0.01" id="payment-amount" name="payment_amount" class="form-control" readonly>
                </div>
                
                <div class="form-group">
                    <label for="payment-method">Payment Method</label>
                    <select id="payment-method" name="payment_method" class="form-control">
                        <option value="card">Credit/Debit Card</option>
                        <option value="bank_transfer">Bank Wire Transfer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="card-num">Card Number (Mock)</label>
                    <input type="text" id="card-num" class="form-control" placeholder="4111 2222 3333 4444" pattern="[0-9\s]{13,19}">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="card-expiry">Expiry Date</label>
                        <input type="text" id="card-expiry" class="form-control" placeholder="MM/YY">
                    </div>
                    <div class="form-group">
                        <label for="card-cvc">CVC</label>
                        <input type="text" id="card-cvc" class="form-control" placeholder="123">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" id="btn-pay-cancel">Close</button>
                <button type="submit" class="btn btn-primary">Process Payment</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Populate profile form values on tab load
    const profileForm = document.getElementById('patient-profile-form');
    
    document.querySelector('.sidebar-link[data-tab="profile-tab"]').addEventListener('click', async () => {
        try {
            const response = await fetch('../api/patients.php');
            const result = await response.json();
            if (result.success) {
                const profile = result.data;
                profileForm.first_name.value = profile.first_name || '';
                profileForm.last_name.value = profile.last_name || '';
                profileForm.phone.value = profile.phone || '';
                profileForm.dob.value = profile.dob || '';
                profileForm.gender.value = profile.gender || 'male';
                profileForm.blood_group.value = profile.blood_group || '';
                profileForm.address.value = profile.address || '';
                profileForm.emergency_contact.value = profile.emergency_contact || '';
            }
        } catch (err) {
            console.error('Error fetching patient profile details:', err);
        }
    });

    profileForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const payload = {
            first_name: profileForm.first_name.value,
            last_name: profileForm.last_name.value,
            phone: profileForm.phone.value,
            dob: profileForm.dob.value,
            gender: profileForm.gender.value,
            blood_group: profileForm.blood_group.value,
            address: profileForm.address.value,
            emergency_contact: profileForm.emergency_contact.value
        };

        try {
            const response = await fetch('../api/patients.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            if (result.success) {
                Toast.success('Profile configurations updated successfully.');
                loadDashboardStats();
            } else {
                Toast.error(result.message);
            }
        } catch (err) {
            Toast.error('An error occurred during updating settings.');
        }
    });
});
</script>

<?php require_once '../includes/header.php'; // Close tag is handled by footer ?>
<?php require_once '../includes/footer.php'; ?>
