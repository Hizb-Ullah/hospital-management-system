/* hospital/js/dashboard.js */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Sidebar tab switching
    setupTabs();

    // 2. Load dashboard stats
    loadDashboardStats();

    // 3. Setup modal operations
    setupModals();
});

// Sidebar Tab Controller
function setupTabs() {
    const links = document.querySelectorAll('.sidebar-link');
    const tabs = document.querySelectorAll('.dashboard-tab-content');

    if (links.length === 0) return;

    links.forEach(link => {
        link.addEventListener('click', (e) => {
            const targetId = link.getAttribute('data-tab');
            if (!targetId) return;

            // Remove active classes
            links.forEach(l => l.classList.remove('active'));
            tabs.forEach(t => t.classList.remove('active'));

            // Set active
            link.classList.add('active');
            const targetTab = document.getElementById(targetId);
            if (targetTab) {
                targetTab.classList.add('active');
                
                // Fetch data specific to this tab if needed
                onTabLoad(targetId);
            }
        });
    });
}

// Trigger API requests on tab loads
function onTabLoad(tabId) {
    if (tabId === 'appointments-tab') {
        loadAppointments();
    } else if (tabId === 'records-tab') {
        loadMedicalRecords();
    } else if (tabId === 'billing-tab') {
        loadBilling();
    } else if (tabId === 'doctors-tab') {
        loadDoctorsForAdmin();
    } else if (tabId === 'departments-tab') {
        loadDepartmentsForAdmin();
    }
}

// Fetch dashboard stats cards and recent tables
async function loadDashboardStats() {
    try {
        const response = await fetch('../api/dashboard.php');
        const result = await response.json();

        if (result.success) {
            const stats = result.data.stats;
            
            // Populating stat values dynamically
            const elementsMapping = {
                'stat-unpaid-bills': `$${parseFloat(stats.unpaid_bills || 0).toFixed(2)}`,
                'stat-total-appts': stats.total_appointments,
                'stat-record-count': stats.medical_records,
                
                'stat-doc-today': stats.appointments_today,
                'stat-doc-pending': stats.pending_appointments,
                'stat-doc-treated': stats.treated_patients,
                
                'stat-admin-patients': stats.total_patients,
                'stat-admin-doctors': stats.total_doctors,
                'stat-admin-appts': stats.total_appointments,
                'stat-admin-revenue': `$${parseFloat(stats.total_revenue || 0).toFixed(2)}`
            };

            for (const [id, value] of Object.entries(elementsMapping)) {
                const el = document.getElementById(id);
                if (el) el.innerText = value;
            }

            // Populate recent appointments list
            const recentApptsContainer = document.getElementById('recent-appointments-list');
            if (recentApptsContainer && result.data.recent_appointments) {
                renderRecentAppointments(recentApptsContainer, result.data.recent_appointments);
            }

            // Populate recent records list
            const recentRecordsContainer = document.getElementById('recent-records-list');
            if (recentRecordsContainer && result.data.recent_medical_records) {
                renderRecentRecords(recentRecordsContainer, result.data.recent_medical_records);
            }

            // Populate recent payments list (admin only)
            const recentPaymentsContainer = document.getElementById('recent-payments-list');
            if (recentPaymentsContainer && result.data.recent_payments) {
                renderRecentPayments(recentPaymentsContainer, result.data.recent_payments);
            }
        }
    } catch (error) {
        console.error('Error loading dashboard stats:', error);
    }
}

// Renderers for recent items on overview tab
function renderRecentAppointments(container, appointments) {
    if (appointments.length === 0) {
        container.innerHTML = '<tr><td colspan="5" style="text-align:center;">No recent appointments found.</td></tr>';
        return;
    }

    container.innerHTML = appointments.map(appt => {
        const isDoc = appt.patient_first_name !== undefined;
        const displayName = isDoc 
            ? `Patient: ${appt.patient_first_name} ${appt.patient_last_name}`
            : `Dr. ${appt.doctor_first_name} ${appt.doctor_last_name}`;
        
        return `
            <tr>
                <td>#${appt.id}</td>
                <td>${displayName}</td>
                <td>${formatDate(appt.appointment_date)}</td>
                <td>${formatTime(appt.appointment_time)}</td>
                <td><span class="badge badge-${appt.status}">${appt.status}</span></td>
            </tr>
        `;
    }).join('');
}

function renderRecentRecords(container, records) {
    if (records.length === 0) {
        container.innerHTML = '<div style="text-align:center; padding:1.5rem; color:#64748b;">No medical history records.</div>';
        return;
    }

    container.innerHTML = records.map(record => `
        <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1rem;">
            <div style="display:flex; justify-content:space-between; margin-bottom: 0.5rem;">
                <strong style="color:var(--dark);">Dr. ${record.doctor_first_name} ${record.doctor_last_name} (${record.specialization})</strong>
                <span style="font-size:0.85rem; color:#64748b;">${formatDate(record.visit_date)}</span>
            </div>
            <p style="margin-bottom:0.25rem;"><strong>Diagnosis:</strong> ${record.diagnosis}</p>
            <p style="margin-bottom:0.25rem;"><strong>Treatment:</strong> ${record.treatment}</p>
            <p><strong>Prescription:</strong> <span style="font-family: monospace;">${record.prescription}</span></p>
        </div>
    `).join('');
}

function renderRecentPayments(container, payments) {
    if (payments.length === 0) {
        container.innerHTML = '<tr><td colspan="5" style="text-align:center;">No payments received yet.</td></tr>';
        return;
    }

    container.innerHTML = payments.map(pay => `
        <tr>
            <td>${pay.transaction_id}</td>
            <td>${pay.first_name} ${pay.last_name}</td>
            <td>$${parseFloat(pay.amount).toFixed(2)}</td>
            <td><span style="text-transform:uppercase; font-weight:600;">${pay.payment_method}</span></td>
            <td>${formatDate(pay.payment_date)}</td>
        </tr>
    `).join('');
}

// -------------------------------------------------------------
// LOAD FULL APPOINTMENTS TAB
// -------------------------------------------------------------
async function loadAppointments() {
    const list = document.getElementById('full-appointments-list');
    if (!list) return;

    try {
        const response = await fetch('../api/appointments.php');
        const result = await response.json();

        if (result.success) {
            const appts = result.data;
            if (appts.length === 0) {
                list.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:2rem;">No appointments scheduled.</td></tr>';
                return;
            }

            list.innerHTML = appts.map(appt => {
                // Determine layout actions based on user role
                const userRole = document.body.getAttribute('data-user-role');
                let actionsHTML = '';

                if (appt.status === 'pending') {
                    if (userRole === 'patient') {
                        actionsHTML = `<button class="btn-action btn-action-cancel" onclick="updateAppointmentStatus(${appt.id}, 'cancelled')">Cancel</button>`;
                    } else if (userRole === 'doctor' || userRole === 'admin' || userRole === 'nurse') {
                        actionsHTML = `
                            <div class="btn-action-group">
                                <button class="btn-action btn-action-approve" onclick="updateAppointmentStatus(${appt.id}, 'approved')">Approve</button>
                                <button class="btn-action btn-action-cancel" onclick="updateAppointmentStatus(${appt.id}, 'cancelled')">Cancel</button>
                            </div>
                        `;
                    }
                } else if (appt.status === 'approved') {
                    if (userRole === 'doctor' || userRole === 'admin') {
                        actionsHTML = `<button class="btn-action btn-action-complete" onclick="openAddRecordModal(${appt.patient_id}, ${appt.doctor_id}, ${appt.id})">Complete Visit</button>`;
                    } else if (userRole === 'patient') {
                        actionsHTML = `<button class="btn-action btn-action-cancel" onclick="updateAppointmentStatus(${appt.id}, 'cancelled')">Cancel</button>`;
                    }
                }

                return `
                    <tr>
                        <td>#${appt.id}</td>
                        <td>Patient: ${appt.patient_first_name} ${appt.patient_last_name}</td>
                        <td>Dr. ${appt.doctor_first_name} ${appt.doctor_last_name}</td>
                        <td>${formatDate(appt.appointment_date)}</td>
                        <td>${formatTime(appt.appointment_time)}</td>
                        <td><span class="badge badge-${appt.status}">${appt.status}</span></td>
                        <td>${actionsHTML}</td>
                    </tr>
                `;
            }).join('');
        }
    } catch (err) {
        console.error('Error fetching appointments:', err);
    }
}

async function updateAppointmentStatus(id, status) {
    try {
        const response = await fetch('../api/appointments.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, status })
        });
        const result = await response.json();
        if (result.success) {
            Toast.success(`Appointment status set to ${status}.`);
            loadAppointments();
            loadDashboardStats();
        } else {
            Toast.error(result.message);
        }
    } catch (err) {
        Toast.error('An error occurred during updating status.');
    }
}
window.updateAppointmentStatus = updateAppointmentStatus;

// -------------------------------------------------------------
// LOAD FULL MEDICAL RECORDS TAB
// -------------------------------------------------------------
async function loadMedicalRecords() {
    const container = document.getElementById('full-records-list');
    if (!container) return;

    try {
        const response = await fetch('../api/medical_records.php');
        const result = await response.json();

        if (result.success) {
            const records = result.data;
            if (records.length === 0) {
                container.innerHTML = '<div style="text-align:center; padding:3rem; color:#64748b;">No medical record entries found.</div>';
                return;
            }

            container.innerHTML = records.map(record => `
                <div class="card-panel" style="margin-bottom: 1.5rem;">
                    <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--border-color); padding-bottom:0.75rem; margin-bottom:1rem;">
                        <div>
                            <h4 style="color:var(--primary); font-size:1.15rem;">Patient: ${record.patient_first_name} ${record.patient_last_name}</h4>
                            <span style="font-size:0.8rem; color:#64748b;">Blood Group: ${record.blood_group || 'Not set'}</span>
                        </div>
                        <div style="text-align:right;">
                            <strong>Dr. ${record.doctor_first_name} ${record.doctor_last_name}</strong><br>
                            <span style="font-size:0.85rem; color:#64748b;">Specialization: ${record.specialization} | Date: ${formatDate(record.visit_date)}</span>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div>
                            <p style="margin-bottom:0.5rem;"><strong>Diagnosis:</strong></p>
                            <p style="background:#f8fafc; padding:0.75rem; border-radius:var(--radius-sm); font-size:0.95rem;">${record.diagnosis}</p>
                        </div>
                        <div>
                            <p style="margin-bottom:0.5rem;"><strong>Treatment Plan:</strong></p>
                            <p style="background:#f8fafc; padding:0.75rem; border-radius:var(--radius-sm); font-size:0.95rem;">${record.treatment}</p>
                        </div>
                    </div>
                    <div style="margin-top: 1rem; border-top: 1px dashed var(--border-color); padding-top:0.75rem;">
                        <p><strong>Prescription:</strong> <span style="font-family:monospace; background:#f1f5f9; padding:0.25rem 0.5rem; border-radius:4px; font-size:0.9rem;">${record.prescription}</span></p>
                    </div>
                </div>
            `).join('');
        }
    } catch (err) {
        console.error('Error fetching records:', err);
    }
}

// -------------------------------------------------------------
// LOAD BILLING TAB & PAYMENTS
// -------------------------------------------------------------
async function loadBilling() {
    const list = document.getElementById('full-billing-list');
    if (!list) return;

    try {
        const response = await fetch('../api/billing.php');
        const result = await response.json();

        if (result.success) {
            const bills = result.data;
            if (bills.length === 0) {
                list.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:2rem;">No billing records.</td></tr>';
                return;
            }

            list.innerHTML = bills.map(bill => {
                const userRole = document.body.getAttribute('data-user-role');
                let actionHTML = '';

                if (bill.status !== 'paid' && userRole === 'patient') {
                    actionHTML = `<button class="btn-action btn-action-approve" onclick="openPaymentModal(${bill.id}, ${bill.amount})">Pay Now</button>`;
                }

                return `
                    <tr>
                        <td>#${bill.id}</td>
                        <td>Patient: ${bill.patient_first_name} ${bill.patient_last_name}</td>
                        <td>$${parseFloat(bill.amount).toFixed(2)}</td>
                        <td>${bill.appointment_date ? 'Appt: ' + formatDate(bill.appointment_date) : 'General Service'}</td>
                        <td>${formatDate(bill.due_date)}</td>
                        <td><span class="badge badge-${bill.status}">${bill.status.replace('_', ' ')}</span></td>
                        <td>${actionHTML}</td>
                    </tr>
                `;
            }).join('');
        }
    } catch (err) {
        console.error('Error loading billing:', err);
    }
}

// -------------------------------------------------------------
// POPUP MODALS LOGIC
// -------------------------------------------------------------
let activePaymentId = null;

function setupModals() {
    // Payment Modal Controls
    const payClose = document.getElementById('pay-modal-close');
    const payCancel = document.getElementById('btn-pay-cancel');
    const payForm = document.getElementById('payment-form');

    const closePayment = () => {
        const modal = document.getElementById('payment-modal');
        if (modal) modal.classList.remove('active');
        activePaymentId = null;
    };

    if (payClose) payClose.addEventListener('click', closePayment);
    if (payCancel) payCancel.addEventListener('click', closePayment);
    
    if (payForm) {
        payForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const method = payForm.payment_method.value;
            const amount = parseFloat(payForm.payment_amount.value);

            if (!activePaymentId || amount <= 0) return;

            try {
                const response = await fetch('../api/payments.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        billing_id: activePaymentId,
                        amount: amount,
                        payment_method: method
                    })
                });

                const result = await response.json();
                if (result.success) {
                    Toast.success(`Payment processed! Transaction ID: ${result.data.transaction_id}`);
                    closePayment();
                    loadBilling();
                    loadDashboardStats();
                } else {
                    Toast.error(result.message);
                }
            } catch (err) {
                Toast.error('Failed to log payment transaction.');
            }
        });
    }

    // Medical Record addition modal controls (Doctor/Admin)
    const recClose = document.getElementById('record-modal-close');
    const recCancel = document.getElementById('btn-record-cancel');
    const recForm = document.getElementById('record-form');

    const closeRecord = () => {
        const modal = document.getElementById('add-record-modal');
        if (modal) modal.classList.remove('active');
    };

    if (recClose) recClose.addEventListener('click', closeRecord);
    if (recCancel) recCancel.addEventListener('click', closeRecord);

    if (recForm) {
        recForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const patientId = parseInt(recForm.patient_id.value);
            const doctorId = parseInt(recForm.doctor_id.value);
            const apptId = parseInt(recForm.appointment_id.value);
            const diagnosis = recForm.diagnosis.value.trim();
            const treatment = recForm.treatment.value.trim();
            const prescription = recForm.prescription.value.trim();

            if (!patientId || !doctorId || !diagnosis || !treatment || !prescription) {
                Toast.error('Please enter all diagnosis information.');
                return;
            }

            try {
                // Add medical record
                const response = await fetch('../api/medical_records.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ patient_id: patientId, doctor_id: doctorId, diagnosis, treatment, prescription })
                });

                const result = await response.json();
                if (result.success) {
                    Toast.success('Medical record added!');
                    
                    // Mark appointment as completed
                    if (apptId > 0) {
                        await fetch('../api/appointments.php', {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id: apptId, status: 'completed' })
                        });
                    }

                    closeRecord();
                    loadAppointments();
                    loadDashboardStats();
                } else {
                    Toast.error(result.message);
                }
            } catch (err) {
                Toast.error('Failed to write medical history.');
            }
        });
    }
}

// Payment modal triggers
function openPaymentModal(billingId, amount) {
    activePaymentId = billingId;
    const modal = document.getElementById('payment-modal');
    const amountInput = document.getElementById('payment-amount');
    
    if (amountInput) amountInput.value = amount;
    if (modal) modal.classList.add('active');
}
window.openPaymentModal = openPaymentModal;

// Medical Record addition triggers
function openAddRecordModal(patientId, doctorId, apptId = 0) {
    const modal = document.getElementById('add-record-modal');
    const form = document.getElementById('record-form');

    if (form) {
        form.patient_id.value = patientId;
        form.doctor_id.value = doctorId;
        form.appointment_id.value = apptId;
        
        // Reset text
        form.diagnosis.value = '';
        form.treatment.value = '';
        form.prescription.value = '';
    }

    if (modal) modal.classList.add('active');
}
window.openAddRecordModal = openAddRecordModal;

// -------------------------------------------------------------
// ADMIN DOCTORS AND DEPARTMENTS CRUD LOGIC
// -------------------------------------------------------------
async function loadDoctorsForAdmin() {
    const list = document.getElementById('admin-doctors-list');
    if (!list) return;

    try {
        const response = await fetch('../api/doctors.php');
        const result = await response.json();
        if (result.success) {
            const doctors = result.data;
            if (doctors.length === 0) {
                list.innerHTML = '<tr><td colspan="6" style="text-align:center;">No doctors registered.</td></tr>';
                return;
            }

            list.innerHTML = doctors.map(doc => `
                <tr>
                    <td>#${doc.id}</td>
                    <td>Dr. ${doc.first_name} ${doc.last_name}</td>
                    <td>${doc.department_name || 'Unassigned'}</td>
                    <td>${doc.specialization}</td>
                    <td>$${parseFloat(doc.consultation_fee).toFixed(2)}</td>
                    <td>
                        <button class="btn-action btn-action-cancel" onclick="deleteDoctor(${doc.user_id})">Delete</button>
                    </td>
                </tr>
            `).join('');
        }
    } catch (err) {
        console.error(err);
    }
}

async function deleteDoctor(userId) {
    if (!confirm('Are you sure you want to permanently delete this doctor account?')) return;

    try {
        const response = await fetch(`../api/doctors.php?user_id=${userId}`, { method: 'DELETE' });
        const result = await response.json();
        if (result.success) {
            Toast.success('Doctor account deleted.');
            loadDoctorsForAdmin();
            loadDashboardStats();
        } else {
            Toast.error(result.message);
        }
    } catch (err) {
        Toast.error('Failed to connect to delete endpoint.');
    }
}
window.deleteDoctor = deleteDoctor;

async function loadDepartmentsForAdmin() {
    const list = document.getElementById('admin-departments-list');
    if (!list) return;

    try {
        const response = await fetch('../api/departments.php');
        const result = await response.json();
        if (result.success) {
            const departments = result.data;
            if (departments.length === 0) {
                list.innerHTML = '<tr><td colspan="4" style="text-align:center;">No departments found.</td></tr>';
                return;
            }

            list.innerHTML = departments.map(dept => `
                <tr>
                    <td>#${dept.id}</td>
                    <td><strong>${dept.name}</strong></td>
                    <td>${dept.description ? dept.description.substring(0, 80) + '...' : ''}</td>
                    <td>
                        <button class="btn-action btn-action-cancel" onclick="deleteDepartment(${dept.id})">Delete</button>
                    </td>
                </tr>
            `).join('');
        }
    } catch (err) {
        console.error(err);
    }
}

async function deleteDepartment(id) {
    if (!confirm('Are you sure you want to delete this department?')) return;

    try {
        const response = await fetch(`../api/departments.php?id=${id}`, { method: 'DELETE' });
        const result = await response.json();
        if (result.success) {
            Toast.success('Department deleted.');
            loadDepartmentsForAdmin();
        } else {
            Toast.error(result.message);
        }
    } catch (err) {
        Toast.error('Failed to delete department.');
    }
}
window.deleteDepartment = deleteDepartment;

// -------------------------------------------------------------
// HELPER FORMATTING FUNCTIONS
// -------------------------------------------------------------
function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatTime(timeString) {
    if (!timeString) return '';
    // Format "14:30:00" to "2:30 PM"
    const [hours, minutes] = timeString.split(':');
    const h = parseInt(hours);
    const ampm = h >= 12 ? 'PM' : 'AM';
    const formattedHours = h % 12 || 12;
    return `${formattedHours}:${minutes} ${ampm}`;
}
