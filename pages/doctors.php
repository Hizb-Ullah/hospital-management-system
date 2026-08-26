<?php
// hospital/pages/doctors.php
$page_title = 'Our Medical Specialists';
require_once '../includes/header.php';
require_once '../includes/db.php';

// Fetch departments for the filter bar
$depts = [];
try {
    $depts = $pdo->query("SELECT * FROM departments ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) {
    // Suppress or handle
}

// Get filter parameters from GET request
$filter_dept = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Retrieve Doctors matching filter criteria
$doctors = [];
try {
    $sql = "
        SELECT d.*, u.first_name, u.last_name, u.email, u.phone, u.gender,
               dept.name as department_name, dept.icon as department_icon
        FROM doctors d
        JOIN users u ON d.user_id = u.id
        LEFT JOIN departments dept ON d.department_id = dept.id
        WHERE 1=1
    ";
    $params = [];

    if ($filter_dept > 0) {
        $sql .= " AND d.department_id = ?";
        $params[] = $filter_dept;
    }

    if (!empty($search_query)) {
        $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR d.specialization LIKE ?)";
        $term = "%" . $search_query . "%";
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
    }

    $sql .= " ORDER BY u.first_name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $doctors = $stmt->fetchAll();
} catch (PDOException $e) {
    // Show database error
}
?>

<div class="fade-in">
    <div class="section-title">
        <h2>Meet Our Physicians</h2>
        <p>Our world-renowned medical consultants bring decades of clinical expertise in multiple specialties.</p>
    </div>

    <!-- Search and Filter Panel -->
    <div class="search-filter-bar">
        <form method="GET" action="doctors.php" style="display:flex; width:100%; gap:1.5rem; flex-wrap:wrap; align-items:center;">
            <div class="search-input-wrapper">
                <input type="text" name="search" class="form-control" placeholder="Search doctor by name or specialization..." value="<?php echo htmlspecialchars($search_query); ?>">
            </div>
            
            <div style="min-width:200px;">
                <select name="department_id" class="form-control" onchange="this.form.submit()">
                    <option value="0">All Departments</option>
                    <?php foreach ($depts as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>" <?php echo $filter_dept === intval($dept['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <?php if ($filter_dept > 0 || !empty($search_query)): ?>
                <a href="doctors.php" class="btn btn-outline" style="padding:0.75rem 1rem;">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Doctor Grid Cards -->
    <div class="doctors-grid">
        <?php if (count($doctors) === 0): ?>
            <div style="grid-column: 1 / -1; text-align:center; padding:3rem; background:white; border-radius:var(--radius-lg); box-shadow:var(--shadow-md);">
                <h3>No Medical Specialists Found</h3>
                <p style="color:#64748b; margin-top:0.5rem;">Try adjusting your search queries or department dropdown selections.</p>
            </div>
        <?php else: ?>
            <?php foreach ($doctors as $doc): ?>
                <div class="doc-card">
                    <div class="doc-img-container">
                        <div class="doc-avatar-svg">
                            <!-- Stethoscope SVG representing Doctor Avatar -->
                            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4.5 16.5c-1.5 1.26-2.5 3.19-2.5 5.5"/>
                                <path d="M22 22c0-2.31-1-4.24-2.5-5.5"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                    </div>
                    <div class="doc-info">
                        <span class="doc-dept-tag"><?php echo htmlspecialchars($doc['department_name'] ?? 'General Medicine'); ?></span>
                        <h3>Dr. <?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?></h3>
                        <div class="doc-specialty"><?php echo htmlspecialchars($doc['specialization']); ?></div>
                        
                        <p style="font-size:0.85rem; color:#64748b; margin-bottom:1.5rem; line-height:1.5;">
                            <?php echo htmlspecialchars($doc['bio'] ?? 'Medical practitioner dedicated to premium patient recovery plans.'); ?>
                        </p>
                        
                        <div class="doc-meta">
                            <div>
                                <span style="display:block; font-size:0.75rem; color:#94a3b8; text-transform:uppercase;">Experience</span>
                                <strong style="color:var(--dark);"><?php echo intval($doc['experience_years']); ?> Years</strong>
                            </div>
                            <div style="text-align:right;">
                                <span style="display:block; font-size:0.75rem; color:#94a3b8; text-transform:uppercase;">Consultation Fee</span>
                                <span class="doc-fee">$<?php echo number_format($doc['consultation_fee'], 2); ?></span>
                            </div>
                        </div>
                        
                        <div style="margin-top: 1.5rem; width: 100%;">
                            <a href="appointments.php?doctor_id=<?php echo $doc['id']; ?>" class="btn btn-primary" style="width:100%; justify-content:center; font-size:0.85rem; padding:0.6rem 1rem;">Book Consultation</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
