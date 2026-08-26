<?php
// hospital/pages/departments.php
$page_title = 'Our Medical Departments';
require_once '../includes/header.php';
require_once '../includes/db.php';

// Fetch departments from database
$departments = [];
try {
    $departments = $pdo->query("SELECT * FROM departments ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) {
    // Show error page
}
?>

<div class="fade-in">
    <div class="section-title">
        <h2>Clinical Specialties & Departments</h2>
        <p>Explore our specialized medical fields, advanced diagnostics equipment, and customized care programs.</p>
    </div>

    <!-- Departments Cards Grid -->
    <div class="departments-grid">
        <?php if (count($departments) === 0): ?>
            <div style="grid-column: 1 / -1; text-align:center; padding:3rem; background:white; border-radius:var(--radius-lg);">
                <h3>No Clinical Departments Registered</h3>
            </div>
        <?php else: ?>
            <?php foreach ($departments as $dept): ?>
                <?php
                // Choose emoji icon based on department name
                $icon = '🏥';
                $name_lower = strtolower($dept['name']);
                if (strpos($name_lower, 'cardio') !== false) $icon = '❤️';
                elseif (strpos($name_lower, 'neuro') !== false) $icon = '🧠';
                elseif (strpos($name_lower, 'pediatr') !== false) $icon = '👶';
                elseif (strpos($name_lower, 'ortho') !== false) $icon = '🦴';
                elseif (strpos($name_lower, 'general') !== false || strpos($name_lower, 'med') !== false) $icon = '🩺';
                ?>
                <div class="dept-card">
                    <div class="dept-icon-box"><?php echo $icon; ?></div>
                    <h3><?php echo htmlspecialchars($dept['name']); ?></h3>
                    <p><?php echo htmlspecialchars($dept['description'] ?? 'Comprehensive care options using state-of-the-art diagnostic and nursing systems.'); ?></p>
                    <a href="doctors.php?department_id=<?php echo $dept['id']; ?>" class="btn-link">View Physicians &rarr;</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
