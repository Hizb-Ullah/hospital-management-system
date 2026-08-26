<?php
// hospital/pages/register.php
$page_title = 'Create Patient Account';
require_once '../includes/header.php';

if (isset($_SESSION['user_id'])) {
    header('Location: patient_dashboard.php');
    exit;
}
?>

<div class="fade-in">
    <div class="form-card" style="max-width: 800px; margin-top: 1.5rem; margin-bottom: 3.5rem;">
        <div class="form-title">
            <span style="font-size:2rem; display:block; margin-bottom:0.5rem;">🩺</span>
            <h2>Patient Registration</h2>
            <p>Sign up to schedule doctor appointments and access medical files.</p>
        </div>

        <form id="register-form">
            <h3 style="font-size:1.1rem; border-bottom:1px solid var(--border-color); padding-bottom:0.5rem; margin-bottom:1.5rem;">1. Personal Details</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" placeholder="e.g. John" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" placeholder="e.g. Doe" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" class="form-control">
                </div>
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" class="form-control">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="e.g. +1 555-0199">
                </div>
                <div class="form-group">
                    <label for="blood_group">Blood Group</label>
                    <select id="blood_group" name="blood_group" class="form-control">
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
                <label for="address">Residential Address</label>
                <textarea id="address" name="address" class="form-control" rows="2" placeholder="Street, City, Zip Code"></textarea>
            </div>

            <div class="form-group">
                <label for="emergency_contact">Emergency Contact (Name & Phone)</label>
                <input type="text" id="emergency_contact" name="emergency_contact" class="form-control" placeholder="e.g. Jane Doe (Wife): +1 555-0188">
            </div>

            <h3 style="font-size:1.1rem; border-bottom:1px solid var(--border-color); padding-bottom:0.5rem; margin-top:2.5rem; margin-bottom:1.5rem;">2. Account Credentials</h3>

            <div class="form-group">
                <label for="username">Username *</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Create username" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="e.g. john@example.com" required autocomplete="email">
            </div>

            <div class="form-row" style="margin-bottom: 2.5rem;">
                <div class="form-group">
                    <label for="password">Password * (min. 6 chars)</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="••••••••" required autocomplete="new-password">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.9rem; font-size:1.05rem;">Complete Account Creation</button>
        </form>

        <div style="text-align:center; margin-top:2rem; font-size:0.9rem;">
            <span>Already have an account? </span>
            <a href="login.php" style="color:var(--primary); font-weight:700;">Sign In Instead</a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
