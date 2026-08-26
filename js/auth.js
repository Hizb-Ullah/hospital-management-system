/* hospital/js/auth.js */

document.addEventListener('DOMContentLoaded', () => {
    // 1. LOGIN FORM HANDLER
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const username = loginForm.username.value.trim();
            const password = loginForm.password.value;
            const role = loginForm.role.value;

            if (!username || !password || !role) {
                Toast.error('Please enter role, username, and password.');
                return;
            }

            try {
                const response = await fetch('../api/auth.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password, role })
                });

                const result = await response.json();

                if (result.success) {
                    Toast.success(result.message || 'Login successful!');
                    
                    // Redirect based on role
                    setTimeout(() => {
                        const userRole = result.data.role;
                        if (userRole === 'admin') {
                            window.location.href = 'admin_dashboard.php';
                        } else if (userRole === 'doctor') {
                            window.location.href = 'doctor_dashboard.php';
                        } else if (userRole === 'nurse') {
                            window.location.href = 'nurse_dashboard.php';
                        } else {
                            window.location.href = 'patient_dashboard.php';
                        }
                    }, 1000);
                } else {
                    Toast.error(result.message || 'Invalid username or password.');
                }
            } catch (error) {
                console.error('Login error:', error);
                Toast.error('An error occurred during login. Please try again.');
            }
        });
    }

    // 2. REGISTRATION FORM HANDLER
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const username = registerForm.username.value.trim();
            const email = registerForm.email.value.trim();
            const password = registerForm.password.value;
            const confirmPassword = registerForm.confirm_password.value;
            const first_name = registerForm.first_name.value.trim();
            const last_name = registerForm.last_name.value.trim();
            const phone = registerForm.phone.value.trim();
            const dob = registerForm.dob.value;
            const gender = registerForm.gender.value;
            const blood_group = registerForm.blood_group.value;
            const address = registerForm.address.value.trim();
            const emergency_contact = registerForm.emergency_contact.value.trim();

            // Client-side validations
            if (!username || !email || !password || !first_name || !last_name) {
                Toast.error('Please fill in all required fields marked with *');
                return;
            }

            if (password.length < 6) {
                Toast.error('Password must be at least 6 characters long.');
                return;
            }

            if (password !== confirmPassword) {
                Toast.error('Passwords do not match.');
                return;
            }

            // Simple email validation regex
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                Toast.error('Please enter a valid email address.');
                return;
            }

            const payload = {
                username, password, email, first_name, last_name,
                phone, dob, gender, blood_group, address, emergency_contact
            };

            try {
                const response = await fetch('../api/auth.php?action=register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (result.success) {
                    Toast.success('Registration completed! Redirecting to login page...');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1500);
                } else {
                    Toast.error(result.message || 'Registration failed. Try again.');
                }
            } catch (error) {
                console.error('Registration error:', error);
                Toast.error('A system error occurred. Please try again.');
            }
        });
    }
});

// Logout utility
async function logoutUser() {
    try {
        const response = await fetch('../api/auth.php?action=logout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        });
        const result = await response.json();
        if (result.success) {
            Toast.success('Successfully logged out.');
            setTimeout(() => {
                // If we are in pages/ directory, go to login. If we are in root directory, go to login.
                const pathPrefix = window.location.pathname.includes('/pages/') ? '' : 'pages/';
                window.location.href = pathPrefix + 'login.php';
            }, 1000);
        } else {
            Toast.error('Logout failed.');
        }
    } catch (err) {
        console.error('Logout error:', err);
        window.location.reload();
    }
}
window.logoutUser = logoutUser;
