<?php
// hospital/pages/appointments.php
$page_title = 'Schedule Consultation';
require_once '../includes/header.php';
require_once '../includes/db.php';

// Redirect to login if user not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Redirect if logged-in user is not a patient
if ($_SESSION['role'] !== 'patient') {
    ?>
    <div class="fade-in" style="max-width: 600px; margin: 5rem auto; text-align: center;">
        <div class="card-panel" style="border-top: 4px solid var(--warning);">
            <span style="font-size:3rem; display:block; margin-bottom:1rem;">⚠️</span>
            <h2>Access Restricted</h2>
            <p style="color:#64748b; margin:1rem 0 2rem;">Only patient accounts can schedule new medical consultations. You are currently logged in as a <strong><?php echo htmlspecialchars($_SESSION['role']); ?></strong>.</p>
            <a href="patient_dashboard.php" class="btn btn-primary">Go to Dashboard</a>
        </div>
    </div>
    <?php
    require_once '../includes/footer.php';
    exit;
}

// Pre-selected doctor ID
$pre_selected_doctor = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;

// Fetch all doctors for select dropdown
$doctors = [];
try {
    $stmt = $pdo->query("
        SELECT d.id, d.consultation_fee, u.first_name, u.last_name, dept.name as department_name
        FROM doctors d
        JOIN users u ON d.user_id = u.id
        LEFT JOIN departments dept ON d.department_id = dept.id
        ORDER BY u.first_name ASC
    ");
    $doctors = $stmt->fetchAll();
} catch (PDOException $e) {
    // Database query failed
}
?>

<div class="fade-in">
    <div class="form-card" style="max-width: 650px; margin-top: 1.5rem; margin-bottom: 3.5rem;">
        <div class="form-title">
            <span style="font-size:2rem; display:block; margin-bottom:0.5rem;">🗓️</span>
            <h2>Book an Appointment</h2>
            <p>Select a physician, pick a convenient slot, and outline your symptoms.</p>
        </div>

        <form id="appointment-booking-form">
            <!-- Doctor Selector -->
            <div class="form-group">
                <label for="doctor_id">Select Doctor *</label>
                <select id="doctor_id" name="doctor_id" class="form-control" required>
                    <option value="" data-fee="0">Choose Doctor...</option>
                    <?php foreach ($doctors as $doc): ?>
                        <option value="<?php echo $doc['id']; ?>" data-fee="<?php echo $doc['consultation_fee']; ?>" <?php echo $pre_selected_doctor === intval($doc['id']) ? 'selected' : ''; ?>>
                            Dr. <?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name'] . ' (' . $doc['department_name'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Date and Time Inputs -->
            <div class="form-row">
                <div class="form-group">
                    <label for="appointment_date">Preferred Date *</label>
                    <input type="date" id="appointment_date" name="appointment_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="appointment_time">Preferred Time Slot *</label>
                    <select id="appointment_time" name="appointment_time" class="form-control" required>
                        <option value="">Select Time...</option>
                        <option value="09:00:00">09:00 AM</option>
                        <option value="09:30:00">09:30 AM</option>
                        <option value="10:00:00">10:00 AM</option>
                        <option value="10:30:00">10:30 AM</option>
                        <option value="11:00:00">11:00 AM</option>
                        <option value="11:30:00">11:30 AM</option>
                        <option value="13:00:00">01:00 PM</option>
                        <option value="13:30:00">01:30 PM</option>
                        <option value="14:00:00">02:00 PM</option>
                        <option value="14:30:00">02:30 PM</option>
                        <option value="15:00:00">03:00 PM</option>
                        <option value="15:30:00">03:30 PM</option>
                        <option value="16:00:00">04:00 PM</option>
                        <option value="16:30:00">04:30 PM</option>
                    </select>
                </div>
            </div>

            <!-- Reason/Symptoms Box -->
            <div class="form-group">
                <label for="reason">Reason for Appointment / Symptoms *</label>
                <textarea id="reason" name="reason" class="form-control" rows="3" placeholder="Please briefly explain your symptoms or purpose of checkup..." required></textarea>
            </div>

            <!-- Dynamic Fee Preview card -->
            <div id="fee-preview-box" style="background:var(--primary-soft); padding:1rem 1.25rem; border-radius:var(--radius-md); display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; border: 1px solid rgba(37,99,235,0.1);">
                <span style="font-weight:600; color:var(--dark);">Consultation Fee:</span>
                <span id="consultation-fee-amount" style="font-size:1.25rem; font-weight:800; color:var(--primary);">$0.00</span>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.9rem; font-size:1.05rem;">Confirm Booking Appointment</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('appointment-booking-form');
    const docSelect = document.getElementById('doctor_id');
    const feeAmount = document.getElementById('consultation-fee-amount');
    const dateInput = document.getElementById('appointment_date');

    // 1. Minimum date selection is tomorrow
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const minDateStr = tomorrow.toISOString().split('T')[0];
    dateInput.min = minDateStr;

    // 2. Update Fee Preview based on Selected Doctor
    const updateFee = () => {
        const selectedOption = docSelect.options[docSelect.selectedIndex];
        const fee = parseFloat(selectedOption.getAttribute('data-fee') || 0);
        feeAmount.innerText = `$${fee.toFixed(2)}`;
    };

    docSelect.addEventListener('change', updateFee);
    updateFee(); // Trigger initial state

    // 3. Form Submit via Fetch API
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const doctor_id = parseInt(docSelect.value);
        const appointment_date = dateInput.value;
        const appointment_time = form.appointment_time.value;
        const reason = form.reason.value.trim();

        if (!doctor_id || !appointment_date || !appointment_time || !reason) {
            Toast.error('Please fill in all booking fields.');
            return;
        }

        try {
            const response = await fetch('../api/appointments.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ doctor_id, appointment_date, appointment_time, reason })
            });

            const result = await response.json();

            if (result.success) {
                Toast.success(result.message);
                setTimeout(() => {
                    window.location.href = 'patient_dashboard.php';
                }, 1500);
            } else {
                Toast.error(result.message);
            }
        } catch (err) {
            console.error(err);
            Toast.error('Failed to submit appointment booking. Please try again.');
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
