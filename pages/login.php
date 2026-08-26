<?php
// hospital/pages/login.php
$page_title = 'Patient & Staff Portal Login';
require_once '../includes/header.php';

// Redirect to dashboard if session exists
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: patient_dashboard.php');
    }
    exit;
}
?>

<div class="fade-in">
    <div class="form-card" style="margin-top: 1.5rem; margin-bottom: 3.5rem;">
        <div class="form-title">
            <span class="icon-wrapper" style="display: inline-block; background: var(--primary-soft); padding: 0.75rem; border-radius: var(--radius-md); margin-bottom: 0.5rem; color: var(--primary);">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon-svg"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <h2>Sign In to Medicare</h2>
            <p>Access your health records, manage bills, and book doctors.</p>
        </div>

        <form id="login-form">
            <div class="form-group">
                <label for="role">Sign In As</label>
                <select id="role" name="role" class="form-control" required style="cursor:pointer; height:auto; padding:0.75rem 1rem;">
                    <option value="" disabled selected>Select your role...</option>
                    <option value="patient">Patient</option>
                    <option value="doctor">Doctor</option>
                    <option value="nurse">Nurse</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>

            <div class="form-group">
                <label for="username">Username or Registration ID</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="e.g. johndoe" required autocomplete="username">
            </div>

            <div class="form-group" style="margin-bottom: 2rem;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <label for="password">Password</label>
                    <a href="#" style="font-size:0.8rem; color:var(--primary); font-weight:600;">Forgot Password?</a>
                </div>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.9rem;">Sign In</button>
        </form>

        <div style="text-align:center; margin-top:2rem; font-size:0.9rem;">
            <span>New patient? </span>
            <a href="register.php" style="color:var(--primary); font-weight:700;">Create Patient Account</a>
        </div>
        
        <!-- Quick login help for testing -->
        <div style="margin-top: 2.5rem; padding: 1rem; background: var(--bg-main); border-radius: var(--radius-md); font-size: 0.85rem; border: 1px dashed var(--border-color);">
            <strong style="color:var(--dark); display:block; margin-bottom:0.5rem;">ℹ️ Quick Test Accounts (Password: password123)</strong>
            <ul style="list-style:none; display:flex; flex-direction:column; gap:0.25rem; opacity:0.85;">
                <li>• Admin Username: <strong style="font-family:monospace;">admin</strong> (Role: admin)</li>
                <li>• Doctor Username: <strong style="font-family:monospace;">doctor1</strong> (Role: doctor)</li>
                <li>• Nurse Username: <strong style="font-family:monospace;">nurse1</strong> (Role: nurse)</li>
                <li>• Patient Username: <strong style="font-family:monospace;">patient1</strong> (Role: patient)</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
