<?php
// hospital/includes/footer.php
$path_prefix = (strpos($_SERVER['SCRIPT_NAME'], '/pages/') !== false) ? '../' : '';
?>
        </main> <!-- Close Main Content -->

        <!-- Global Footer component -->
        <footer class="app-footer">
            <div class="footer-container">
                <div class="footer-info">
                    <div class="logo" style="margin-bottom:1.5rem;">
                        <a href="<?php echo $path_prefix; ?>index.php" style="color:white;">
                            <div class="logo-icon">+</div>
                            Medicare
                        </a>
                    </div>
                    <p>Medicare provides state-of-the-art clinical services, preventative medicine, and specialized surgery options. Your health is our highest mission.</p>
                </div>
                
                <div class="footer-links">
                    <h4>Departments</h4>
                    <ul>
                        <li><a href="<?php echo $path_prefix; ?>pages/doctors.php?department_id=1">Cardiology</a></li>
                        <li><a href="<?php echo $path_prefix; ?>pages/doctors.php?department_id=2">Neurology</a></li>
                        <li><a href="<?php echo $path_prefix; ?>pages/doctors.php?department_id=3">Pediatrics</a></li>
                        <li><a href="<?php echo $path_prefix; ?>pages/doctors.php?department_id=4">Orthopedics</a></li>
                        <li><a href="<?php echo $path_prefix; ?>pages/doctors.php?department_id=5">General Medicine</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="<?php echo $path_prefix; ?>pages/doctors.php">Our Doctors</a></li>
                        <li><a href="<?php echo $path_prefix; ?>pages/departments.php">Specialties</a></li>
                        <li><a href="<?php echo $path_prefix; ?>pages/contact.php">Contact & Support</a></li>
                        <li><a href="<?php echo $path_prefix; ?>pages/login.php">Patient Login</a></li>
                        <li><a href="<?php echo $path_prefix; ?>pages/register.php">Patient Portal Signup</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h4>Contact Us</h4>
                    <ul style="opacity: 0.85;">
                        <li>📍 123 Health Ave, Metro City</li>
                        <li>📞 +1 555 100 2000</li>
                        <li>✉️ contact@medicare.com</li>
                        <li>⏰ 24 Hours Emergency Service</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Medicare Care System. All rights reserved.</p>
                <div style="display:flex; gap:1.5rem;">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Use</a>
                </div>
            </div>
        </footer>
    </div> <!-- Close Layout Container -->

    <!-- Load Global JavaScript Libraries -->
    <script src="<?php echo $path_prefix; ?>js/toast.js"></script>
    <script src="<?php echo $path_prefix; ?>js/app.js"></script>
    <script src="<?php echo $path_prefix; ?>js/auth.js"></script>
    
    <!-- Conditional page script injections -->
    <?php if (isset($include_dashboard_js) && $include_dashboard_js): ?>
        <script src="<?php echo $path_prefix; ?>js/dashboard.js"></script>
    <?php endif; ?>
</body>
</html>
