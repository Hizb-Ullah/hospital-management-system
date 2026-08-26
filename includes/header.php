<?php
// hospital/includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Calculate the path prefix dynamically to support both root index.php and files under pages/
$path_prefix = (strpos($_SERVER['SCRIPT_NAME'], '/pages/') !== false) ? '../' : '';
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $_SESSION['role'] ?? '';
$user_name = $_SESSION['first_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Optimization -->
    <title><?php echo isset($page_title) ? $page_title . ' - Medicare' : 'Medicare Hospital Management System'; ?></title>
    <meta name="description" content="Medicare offers cutting-edge medical treatments, premium inpatient wards, expert physician consultations, and digital healthcare management.">
    
    <!-- Premium Stylesheet Links -->
    <link rel="stylesheet" href="<?php echo $path_prefix; ?>css/style.css">
    <link rel="stylesheet" href="<?php echo $path_prefix; ?>css/toast.css">
    <?php if (isset($include_dashboard_css) && $include_dashboard_css): ?>
        <link rel="stylesheet" href="<?php echo $path_prefix; ?>css/dashboard.css">
    <?php endif; ?>
</head>
<body data-user-role="<?php echo htmlspecialchars($user_role); ?>">
    
    <div class="layout-container">
        <!-- App Navigation Header -->
        <header class="app-header">
            <div class="header-container">
                <div class="logo">
                    <a href="<?php echo $path_prefix; ?>index.php">
                        <div class="logo-icon">+</div>
                        Medicare<span>Care</span>
                    </a>
                </div>
                
                <!-- Primary Navigation -->
                <ul class="nav-links">
                    <li><a href="<?php echo $path_prefix; ?>index.php" class="nav-item">Home</a></li>
                    <li><a href="<?php echo $path_prefix; ?>pages/departments.php" class="nav-item">Departments</a></li>
                    <li><a href="<?php echo $path_prefix; ?>pages/doctors.php" class="nav-item">Doctors</a></li>
                    <li><a href="<?php echo $path_prefix; ?>pages/contact.php" class="nav-item">Contact Us</a></li>
                    
                    <!-- Dynamic Logged-in Links (Mobile View) -->
                    <?php if ($is_logged_in): ?>
                        <li>
                            <?php 
                            $dashboard_url = 'patient_dashboard.php';
                            if ($user_role === 'doctor') $dashboard_url = 'doctor_dashboard.php';
                            elseif ($user_role === 'nurse') $dashboard_url = 'nurse_dashboard.php';
                            elseif ($user_role === 'admin') $dashboard_url = 'admin_dashboard.php';
                            ?>
                            <a href="<?php echo $path_prefix; ?>pages/<?php echo $dashboard_url; ?>" class="nav-item">My Dashboard</a>
                        </li>
                        <div class="nav-actions-mobile" style="display: none;">
                            <button class="btn btn-outline" onclick="logoutUser()">Log Out</button>
                        </div>
                    <?php else: ?>
                        <div class="nav-actions-mobile" style="display: none;">
                            <a href="<?php echo $path_prefix; ?>pages/login.php" class="btn btn-outline">Log In</a>
                            <a href="<?php echo $path_prefix; ?>pages/register.php" class="btn btn-primary">Register</a>
                        </div>
                    <?php endif; ?>
                </ul>

                <!-- Desktop Action Panel -->
                <div class="nav-actions">
                    <?php if ($is_logged_in): ?>
                        <span style="font-size:0.9rem; font-weight:600; color:var(--dark);">Hello, <?php echo htmlspecialchars($user_name); ?></span>
                        <?php 
                        $portal_name = 'My Portal';
                        $portal_url = 'patient_dashboard.php';
                        if ($user_role === 'admin') {
                            $portal_name = 'Admin Portal';
                            $portal_url = 'admin_dashboard.php';
                        } elseif ($user_role === 'doctor') {
                            $portal_name = 'Doctor Portal';
                            $portal_url = 'doctor_dashboard.php';
                        } elseif ($user_role === 'nurse') {
                            $portal_name = 'Nurse Portal';
                            $portal_url = 'nurse_dashboard.php';
                        }
                        ?>
                        <a href="<?php echo $path_prefix; ?>pages/<?php echo $portal_url; ?>" class="btn btn-secondary"><?php echo $portal_name; ?></a>
                        <button class="btn btn-outline" onclick="logoutUser()">Log Out</button>
                    <?php else: ?>
                        <a href="<?php echo $path_prefix; ?>pages/login.php" class="btn btn-outline">Log In</a>
                        <a href="<?php echo $path_prefix; ?>pages/register.php" class="btn btn-primary">Book Now</a>
                    <?php endif; ?>
                </div>

                <!-- Responsive Menu Hamburger -->
                <button class="menu-toggle" aria-label="Toggle Navigation Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </header>
        
        <!-- Main Wrapper -->
        <main class="main-content <?php echo isset($main_class) ? $main_class : ''; ?>">
