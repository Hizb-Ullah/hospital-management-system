<?php
// hospital/index.php
$page_title = 'Welcome to Medicare Care';
$main_class = 'home-page';
require_once 'includes/header.php';
?>

<!-- Hero Banner Section -->
<section class="hero-section">
    <div class="hero-content fade-in">
        <span style="font-weight:700; color:var(--primary); text-transform:uppercase; letter-spacing:1px; display:inline-block; margin-bottom:1rem;">★ Next Generation Healthcare</span>
        <h1>Advanced Medicine, <br><span>Compassionate Care</span></h1>
        <p>Medicare connects you with top-rated medical specialists, 24/7 critical emergency response units, and instant digitized healthcare records management. Book your consultations online in minutes.</p>
        <div style="display:flex; gap:1.25rem;">
            <a href="pages/register.php" class="btn btn-primary">Get Started Now</a>
            <a href="pages/doctors.php" class="btn btn-outline">Meet Our Doctors</a>
        </div>
    </div>
    
    <div class="hero-image">
        <!-- Modern Healthcare SVG Illustration -->
        <svg width="500" height="420" viewBox="0 0 500 420" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="20" y="60" width="460" height="320" rx="24" fill="url(#hero-grad)" />
            <circle cx="410" cy="120" r="40" fill="rgba(255,255,255,0.08)" />
            <circle cx="100" cy="300" r="60" fill="rgba(255,255,255,0.04)" />
            
            <!-- Graphic Hospital Building Outline -->
            <path d="M120 380 V180 H380 V380 Z" fill="white" opacity="0.95" />
            <path d="M120 180 L250 100 L380 180 Z" fill="var(--primary)" />
            <rect x="230" y="310" width="40" height="70" rx="4" fill="var(--dark)" />
            
            <!-- Grid Windows -->
            <rect x="150" y="210" width="30" height="30" rx="4" fill="var(--primary-soft)" />
            <rect x="200" y="210" width="30" height="30" rx="4" fill="var(--primary-soft)" />
            <rect x="270" y="210" width="30" height="30" rx="4" fill="var(--primary-soft)" />
            <rect x="320" y="210" width="30" height="30" rx="4" fill="var(--primary-soft)" />
            
            <rect x="150" y="260" width="30" height="30" rx="4" fill="var(--primary-soft)" />
            <rect x="320" y="260" width="30" height="30" rx="4" fill="var(--primary-soft)" />
            
            <!-- Floating Pulse Wave line -->
            <path d="M50 320 Q80 320 90 280 T110 360 T130 310 T145 320 H450" stroke="var(--accent)" stroke-width="4" stroke-linecap="round" />
            
            <defs>
                <linearGradient id="hero-grad" x1="0" y1="0" x2="500" y2="420" gradientUnits="userSpaceOnUse">
                    <stop stop-color="var(--primary)" />
                    <stop offset="1" stop-color="var(--secondary)" />
                </linearGradient>
            </defs>
        </svg>
    </div>
</section>

<!-- Counters Section -->
<section class="stats-section">
    <div class="stat-item">
        <div class="stat-num">50+</div>
        <div class="stat-label">Medical Specialists</div>
    </div>
    <div class="stat-item">
        <div class="stat-num">12k+</div>
        <div class="stat-label">Happy Patients Served</div>
    </div>
    <div class="stat-item">
        <div class="stat-num">15+</div>
        <div class="stat-label">Specialized Departments</div>
    </div>
    <div class="stat-item">
        <div class="stat-num">24/7</div>
        <div class="stat-label">Emergency Active Wards</div>
    </div>
</section>

<!-- Departments Section Preview -->
<section style="margin-bottom:6rem;">
    <div class="section-title">
        <h2>Our Core Specialties</h2>
        <p>We provide a comprehensive range of clinical services tailored to your family's needs, backed by cutting-edge medical labs.</p>
    </div>
    
    <div class="departments-grid">
        <div class="dept-card">
            <div class="dept-icon-box">❤️</div>
            <h3>Cardiology</h3>
            <p>Advanced diagnostic cardiology, electrocardiograms, and post-operative cardiovascular support plans.</p>
            <a href="pages/doctors.php?department_id=1" class="btn-link">Meet Specialists &rarr;</a>
        </div>
        <div class="dept-card">
            <div class="dept-icon-box">🧠</div>
            <h3>Neurology</h3>
            <p>Consultations for headaches, complex brain-nerves disorders, clinical studies, and rehabilitations.</p>
            <a href="pages/doctors.php?department_id=2" class="btn-link">Meet Specialists &rarr;</a>
        </div>
        <div class="dept-card">
            <div class="dept-icon-box">👶</div>
            <h3>Pediatrics</h3>
            <p>Friendly healthcare services for infants and children, standard vaccinations, and growth monitoring.</p>
            <a href="pages/doctors.php?department_id=3" class="btn-link">Meet Specialists &rarr;</a>
        </div>
        <div class="dept-card">
            <div class="dept-icon-box">🦴</div>
            <h3>Orthopedics</h3>
            <p>Complete joint reconstruction options, trauma therapies, and active sports injuries rehabilitations.</p>
            <a href="pages/doctors.php?department_id=4" class="btn-link">Meet Specialists &rarr;</a>
        </div>
    </div>
    
    <div style="text-align:center;">
        <a href="pages/departments.php" class="btn btn-secondary">Explore All Departments</a>
    </div>
</section>

<!-- Call-to-action Section -->
<section style="background:linear-gradient(135deg, rgba(37, 99, 235, 0.03), rgba(20, 184, 166, 0.03)); border: 1px solid var(--border-color); border-radius:var(--radius-xl); padding:4rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:2rem; margin-bottom: 3rem;">
    <div>
        <h2 style="font-size:2rem; margin-bottom:0.5rem; color:var(--dark);">Need Immediate Consultation?</h2>
        <p style="color:var(--dark-soft); font-size:1.1rem; max-width:650px;">Sign up to our secure online Patient Portal to book appointments, review billing invoices, and check your diagnostic history records instantly.</p>
    </div>
    <a href="pages/register.php" class="btn btn-primary" style="padding:1rem 2rem; font-size:1.05rem;">Book Appointment Now</a>
</section>

<?php require_once 'includes/footer.php'; ?>
