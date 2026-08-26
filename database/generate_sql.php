<?php
// hospital/database/generate_sql.php

$sql = "";

// 1. Table dropping and creation schema
$sql .= "
-- Create Database
CREATE DATABASE IF NOT EXISTS medicare_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE medicare_database;

-- Disable foreign key checks to allow clean drop of tables
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS billing;
DROP TABLE IF EXISTS medical_records;
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS nurses;
DROP TABLE IF EXISTS doctors;
DROP TABLE IF EXISTS patients;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS wards;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Departments Table
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Wards Table
CREATE TABLE wards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    department_id INT,
    capacity INT NOT NULL DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ward_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('patient', 'doctor', 'nurse', 'admin') NOT NULL,
    phone VARCHAR(20),
    gender ENUM('male', 'female', 'other'),
    dob DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Doctors Table
CREATE TABLE doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    department_id INT,
    specialization VARCHAR(100),
    experience_years INT,
    consultation_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_doctor_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_doctor_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Nurses Table
CREATE TABLE nurses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    department_id INT,
    doctor_id INT NULL,
    ward_id INT NULL,
    shift ENUM('Morning', 'Evening', 'Night') NOT NULL DEFAULT 'Morning',
    duty_time VARCHAR(100) NOT NULL DEFAULT '08:00 AM - 04:00 PM',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_nurse_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_nurse_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    CONSTRAINT fk_nurse_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE SET NULL,
    CONSTRAINT fk_nurse_ward FOREIGN KEY (ward_id) REFERENCES wards(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Patients Table
CREATE TABLE patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    nurse_id INT NULL,
    ward_id INT NULL,
    blood_group VARCHAR(5),
    address TEXT,
    emergency_contact VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_patient_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_patient_nurse FOREIGN KEY (nurse_id) REFERENCES nurses(id) ON DELETE SET NULL,
    CONSTRAINT fk_patient_ward FOREIGN KEY (ward_id) REFERENCES wards(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Admins Table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_admin_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Appointments Table
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_appointment_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    CONSTRAINT fk_appointment_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Medical Records Table
CREATE TABLE medical_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    diagnosis TEXT NOT NULL,
    treatment TEXT NOT NULL,
    prescription TEXT NOT NULL,
    visit_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_record_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    CONSTRAINT fk_record_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Billing Table
CREATE TABLE billing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    appointment_id INT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('unpaid', 'paid', 'partially_paid') NOT NULL DEFAULT 'unpaid',
    due_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_billing_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    CONSTRAINT fk_billing_appointment FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Payments Table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    billing_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'card', 'bank_transfer') NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    transaction_id VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payment_billing FOREIGN KEY (billing_id) REFERENCES billing(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

// 2. Insert Departments
$sql .= "
-- Seed Departments
INSERT INTO departments (id, name, description, icon) VALUES
(1, 'Cardiology', 'Specialized care for heart conditions, including advanced diagnostics, coronary care, and vascular rehabilitation.', 'cardiology'),
(2, 'Neurology', 'Diagnosis and treatment of complex disorders of the brain, spinal cord, nerves, and muscles.', 'neurology'),
(3, 'Pediatrics', 'Comprehensive healthcare for infants, children, and adolescents up to age 18.', 'pediatrics'),
(4, 'Orthopedics', 'Surgical and non-surgical treatment of bones, joints, ligaments, tendons, and muscles.', 'orthopedics'),
(5, 'General Medicine', 'Primary healthcare services, preventative medicine, chronic condition management, and diagnostics.', 'general-medicine');
";

// 3. Insert Wards
$sql .= "
-- Seed Wards
INSERT INTO wards (id, name, department_id, capacity) VALUES
(1, 'Cardiology Care Ward', 1, 15),
(2, 'Neurology Recovery Ward', 2, 10),
(3, 'Pediatric Friendly Ward', 3, 20),
(4, 'Orthopedic Rehab Ward', 4, 15),
(5, 'General Medicine Ward', 5, 25);
";

// Hash password
$hashedPassword = '$2y$10$8UgJIh.KAnDc1/b.4gb33eaBtrDRgXb2kQt8oNO0GKRe6sIFKR8IC'; // password123

// Lists of names
$firstNames = ['John', 'Emily', 'Raj', 'Lisa', 'David', 'Robert', 'Mary', 'Patricia', 'Michael', 'Linda', 'William', 'Elizabeth', 'Richard', 'Barbara', 'Joseph', 'Susan', 'Thomas', 'Jessica', 'Charles', 'Sarah', 'Karen', 'Nancy', 'Lisa', 'Betty', 'Margaret', 'Sandra', 'Ashley', 'Kimberly', 'Donna', 'Carol', 'Steven', 'Paul', 'Andrew', 'Joshua', 'Kevin', 'Brian', 'Timothy', 'Ronald', 'George', 'Jason', 'Edward', 'Jeffrey', 'Ruth', 'Sharon', 'Michelle', 'Laura', 'Sarah', 'Kimberly', 'Deborah', 'Jessica'];
$lastNames = ['Smith', 'Jones', 'Patel', 'Williams', 'Brown', 'Johnson', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson', 'Walker', 'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores', 'Green', 'Adams', 'Nelson', 'Baker', 'Hall', 'Rivera', 'Campbell', 'Mitchell', 'Carter', 'Roberts'];

// Generate users, doctors, nurses, patients
$usersSql = "
-- Seed Users (Admins, Doctors, Nurses, Patients)
INSERT INTO users (id, username, password, email, first_name, last_name, role, phone, gender, dob) VALUES
(1, 'admin', '$hashedPassword', 'admin@medicare.com', 'Sarah', 'Connor', 'admin', '+1555100200', 'female', '1985-04-12'),
(2, 'admin2', '$hashedPassword', 'admin2@medicare.com', 'Alex', 'Mercer', 'admin', '+1555100201', 'male', '1990-08-25')
";

$adminsSql = "
-- Seed Admins
INSERT INTO admins (id, user_id) VALUES
(1, 1),
(2, 2);
";

$doctorsDetailsSql = "-- Seed Doctors
INSERT INTO doctors (id, user_id, department_id, specialization, experience_years, consultation_fee, bio) VALUES
";

$nursesDetailsSql = "-- Seed Nurses
INSERT INTO nurses (id, user_id, department_id, doctor_id, ward_id, shift, duty_time) VALUES
";

$patientsDetailsSql = "-- Seed Patients
INSERT INTO patients (id, user_id, nurse_id, ward_id, blood_group, address, emergency_contact) VALUES
";

$currentUserId = 3;
$doctorId = 1;
$nurseId = 1;
$patientId = 1;

// 1. Doctors generation: 10 per department
$deptNames = [1 => 'Cardiology', 2 => 'Neurology', 3 => 'Pediatrics', 4 => 'Orthopedics', 5 => 'General Medicine'];
$specializations = [
    1 => ['Interventional Cardiology', 'Cardiothoracic Surgery', 'Heart Failure Specialist', 'Cardiac Arrhythmia Specialist', 'Vascular Medicine', 'Echocardiography Expert', 'Pediatric Cardiology', 'Preventative Cardiology', 'Cardiovascular Imaging', 'Electrophysiology'],
    2 => ['Clinical Neurology', 'Stroke Medicine', 'Epilepsy Specialist', 'Neuromuscular Medicine', 'Neuro-oncology', 'Cognitive Neurology', 'Pediatric Neurology', 'Sleep Medicine', 'Movement Disorders Specialist', 'Neuro-critical Care'],
    3 => ['General Pediatrics', 'Neonatology', 'Pediatric Allergy & Immunology', 'Pediatric Pulmonology', 'Pediatric Endocrinology', 'Pediatric Emergency Medicine', 'Pediatric Hematology', 'Child Psychiatry', 'Pediatric Infectious Diseases', 'Pediatric Gastroenterology'],
    4 => ['Joint Reconstruction', 'Spine Surgery', 'Sports Orthopedics', 'Orthopedic Oncology', 'Pediatric Orthopedics', 'Trauma and Fracture Care', 'Hand Surgery', 'Foot and Ankle Specialist', 'Arthroscopy Expert', 'Physical Rehabilitation'],
    5 => ['Internal Medicine', 'Family Medicine', 'Geriatrics Care', 'Preventative Care', 'Chronic Disease Management', 'Clinical Immunology', 'Diagnostic Medicine', 'Primary Care', 'Travel Medicine', 'Endocrine & Metabolic Care']
];

for ($dept = 1; $dept <= 5; $dept++) {
    for ($d = 0; $d < 10; $d++) {
        $first = $firstNames[($doctorId + $dept * 10) % count($firstNames)];
        $last = $lastNames[($doctorId + $dept * 17) % count($lastNames)];
        $username = "doctor" . $doctorId;
        $email = strtolower($first . "." . $last . $doctorId . "@medicare.com");
        $phone = "+1555" . (200000 + $doctorId);
        $gender = ($d % 2 === 0) ? 'male' : 'female';
        $dobYear = 1970 + ($doctorId % 20);
        $dob = "$dobYear-05-15";
        
        // Add to users
        $usersSql .= ",\n($currentUserId, '$username', '$hashedPassword', '$email', '$first', '$last', 'doctor', '$phone', '$gender', '$dob')";
        
        // Add to doctors
        $spec = $specializations[$dept][$d];
        $exp = 8 + ($doctorId % 20);
        $fee = 70.00 + ($doctorId % 5) * 20;
        $bio = "Dr. $first $last is a highly skilled specialist in " . strtolower($spec) . " with over $exp years of clinical experience in $deptNames[$dept].";
        
        $doctorsDetailsSql .= ($doctorId > 1 ? ",\n" : "") . "($doctorId, $currentUserId, $dept, '$spec', $exp, $fee, '$bio')";
        
        $doctorId++;
        $currentUserId++;
    }
}
$doctorsDetailsSql .= ";\n\n";

// 2. Nurses generation: 30 per department
$shifts = ['Morning', 'Evening', 'Night'];
$dutyTimes = [
    'Morning' => '08:00 AM - 04:00 PM',
    'Evening' => '04:00 PM - 12:00 AM',
    'Night' => '12:00 AM - 08:00 AM'
];

for ($dept = 1; $dept <= 5; $dept++) {
    // 30 nurses in this department
    for ($n = 0; $n < 30; $n++) {
        $first = $firstNames[($nurseId + $dept * 7) % count($firstNames)];
        $last = $lastNames[($nurseId + $dept * 11) % count($lastNames)];
        $username = "nurse" . $nurseId;
        $email = strtolower($first . "." . $last . $nurseId . "@medicare.com");
        $phone = "+1555" . (300000 + $nurseId);
        $gender = ($n % 4 === 0) ? 'male' : 'female'; // mostly female, some male
        $dobYear = 1985 + ($nurseId % 15);
        $dob = "$dobYear-10-20";
        
        // Add to users
        $usersSql .= ",\n($currentUserId, '$username', '$hashedPassword', '$email', '$first', '$last', 'nurse', '$phone', '$gender', '$dob')";
        
        // Doctor association: each nurse works with one of the 10 doctors in their department
        $relatedDoctorOffset = ($nurseId - 1) % 10;
        $relatedDoctorId = ($dept - 1) * 10 + 1 + $relatedDoctorOffset;
        
        // Ward association: ward of their department
        $wardId = $dept;
        
        // Shift distribution
        $shiftIndex = ($nurseId - 1) % 3;
        $shift = $shifts[$shiftIndex];
        $time = $dutyTimes[$shift];
        
        $nursesDetailsSql .= ($nurseId > 1 ? ",\n" : "") . "($nurseId, $currentUserId, $dept, $relatedDoctorId, $wardId, '$shift', '$time')";
        
        $nurseId++;
        $currentUserId++;
    }
}
$nursesDetailsSql .= ";\n\n";

// 3. Patients generation: 15 patients
$bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
for ($p = 0; $p < 15; $p++) {
    $first = $firstNames[($patientId + 23) % count($firstNames)];
    $last = $lastNames[($patientId + 37) % count($lastNames)];
    $username = "patient" . $patientId;
    $email = strtolower($first . "." . $last . $patientId . "@gmail.com");
    $phone = "+1555" . (400000 + $patientId);
    $gender = ($p % 2 === 0) ? 'male' : 'female';
    
    // Ages distribution to have boy/girl, young, and old
    // patient 1: age 8 (Boy/Girl)
    // patient 2: age 72 (Old)
    // patient 3: age 30 (Young)
    // patient 4: age 15 (Boy/Girl)
    // patient 5: age 62 (Old)
    // and so on...
    if ($p % 3 === 0) {
        $dobYear = 2015 + ($patientId % 5); // Children (8-11 yrs)
    } elseif ($p % 3 === 1) {
        $dobYear = 1950 + ($patientId % 15); // Seniors (60-75 yrs)
    } else {
        $dobYear = 1980 + ($patientId % 20); // Young Adults (25-45 yrs)
    }
    
    $dob = "$dobYear-08-12";
    
    // Add to users
    $usersSql .= ",\n($currentUserId, '$username', '$hashedPassword', '$email', '$first', '$last', 'patient', '$phone', '$gender', '$dob')";
    
    // Ward admission: Patient 1-5 admitted to Cardiology, Neurology, Pediatrics wards respectively
    $admittedWard = ($patientId <= 5) ? $patientId : "NULL";
    
    // Assigned Nurse: Patient 1-5 assigned to Nurses 1, 31, 61, 91, 121
    $assignedNurse = ($patientId <= 5) ? (($patientId - 1) * 30 + 1) : "NULL";
    
    $bg = $bloodGroups[$patientId % count($bloodGroups)];
    $address = (100 + $patientId * 15) . " Health St, Metro City";
    $emergency = "Guardian " . $last . ": +1555" . (900000 + $patientId);
    
    $patientsDetailsSql .= ($patientId > 1 ? ",\n" : "") . "($patientId, $currentUserId, $assignedNurse, $admittedWard, '$bg', '$address', '$emergency')";
    
    $patientId++;
    $currentUserId++;
}
$usersSql .= ";\n\n";
$patientsDetailsSql .= ";\n\n";

$sql .= $usersSql;
$sql .= $adminsSql;
$sql .= $doctorsDetailsSql;
$sql .= $nursesDetailsSql;
$sql .= $patientsDetailsSql;

// 4. Seed appointments, billing, payments
$sql .= "
-- Seed Appointments
-- Note: Doctor 1 is Cardiology, Doctor 11 is Neurology, Doctor 21 is Pediatrics, Doctor 31 is Orthopedics, Doctor 41 is General Medicine.
INSERT INTO appointments (id, patient_id, doctor_id, appointment_date, appointment_time, reason, status) VALUES
(1, 1, 1, '2026-06-01', '09:00:00', 'Routine checkup for heart pressure monitoring.', 'approved'),
(2, 2, 11, '2026-06-02', '10:00:00', 'Persistent migraine attacks for the last 2 weeks.', 'pending'),
(3, 3, 21, '2026-06-03', '11:00:00', 'Childhood asthma and breathing checks.', 'approved'),
(4, 4, 31, '2026-06-04', '14:00:00', 'Post-operative knee recovery assessment.', 'pending'),
(5, 5, 41, '2026-06-05', '15:00:00', 'General physical checkup and blood panel.', 'approved'),
(6, 6, 1, '2026-06-01', '10:30:00', 'Chest tight sensation and heart rate monitoring.', 'approved'),
(7, 7, 1, '2026-06-01', '11:30:00', 'Cardiology follow-up scan.', 'approved'),
(8, 8, 1, '2026-06-01', '14:00:00', 'Palpitation evaluation.', 'approved'),
(9, 1, 1, '2026-05-15', '09:00:00', 'Previous checkup visit.', 'completed');

-- Seed Billing (matches consultation fees of doctors)
INSERT INTO billing (id, patient_id, appointment_id, amount, status, due_date) VALUES
(1, 1, 1, 110.00, 'unpaid', '2026-06-01'),
(2, 2, 2, 110.00, 'unpaid', '2026-06-02'),
(3, 3, 3, 110.00, 'paid', '2026-06-03'),
(4, 4, 4, 110.00, 'unpaid', '2026-06-04'),
(5, 5, 5, 110.00, 'unpaid', '2026-06-05'),
(6, 1, 9, 110.00, 'paid', '2026-05-15');

-- Seed Payments
INSERT INTO payments (id, billing_id, amount, payment_method, payment_date, transaction_id) VALUES
(1, 3, 110.00, 'card', '2026-05-20 10:30:00', 'TXN9988776655'),
(2, 6, 110.00, 'cash', '2026-05-15 11:15:00', 'TXN1122334455');

-- Seed Medical Records
INSERT INTO medical_records (id, patient_id, doctor_id, diagnosis, treatment, prescription, visit_date) VALUES
(1, 1, 1, 'Mild chest pressure with normal ECG findings. Swelling under control.', 'Advised low sodium diet and regular mild cardiovascular exercise.', 'Aspirin 81mg once daily, CoQ10 supplements.', '2026-05-15');
";

file_put_contents('medicare_database.sql', $sql);
echo "SQL file generated successfully!\n";
?>
