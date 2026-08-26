# Medicare Care - Hospital Management System

Medicare is a premium, full-stack Hospital Management System built from scratch using **PHP** (backend), **MySQL** (database), and **Vanilla HTML5, CSS3, and JavaScript** (frontend). It is designed to run directly in a local XAMPP server environment.

## 🚀 Setup & Installation Instructions

To run this application locally, please follow these simple steps:

### 1. Place the files in XAMPP
Copy the entire `hospital` directory and place it into your XAMPP's local server root folder:
- **Windows**: `C:\xampp\htdocs\hospital\`
- **macOS (XAMPP-VM)**: `/Applications/XAMPP/htdocs/hospital/`

### 2. Start Local Servers
1. Open the **XAMPP Control Panel**.
2. Click **Start** next to the **Apache** module.
3. Click **Start** next to the **MySQL** module.
*(Ensure both services are running and their status indicators turn green.)*

### 3. Setup and Import MySQL Database
1. Open your browser and navigate to the phpMyAdmin panel:
   [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Click on the **Databases** tab in the top navigation menu.
3. In the "Create database" field, enter the name exactly as:
   `medicare_database`
4. Choose `utf8mb4_unicode_ci` as the collation, and click **Create**.
5. Select the newly created `medicare_database` from the sidebar list.
6. Click the **Import** tab in the top navigation.
7. Click the **Choose File** button and select the database seed file located at:
   `hospital/database/medicare_database.sql`
8. Scroll to the bottom of the page and click **Import** (or **Go**).
*(You should see a message confirming the queries imported successfully, creating all 10 tables and seeding them with default values).*

### 4. Open the Web Application
Access the system by opening your browser and entering the following URL:
👉 [http://localhost/hospital/](http://localhost/hospital/)

---

## 🔑 Quick Testing Credentials

All accounts in the dummy seed data use the same password:
👉 **`password123`**

Here are the usernames for testing each role:

1. **System Administrator**
   - **Username**: `admin`
   - **Access**: Manage doctors, specialties departments, review clinic appointments, generate patient invoices, inspect transaction records.

2. **Medical Practitioner (Doctor)**
   - **Username**: `drsmith`
   - **Access**: Approve pending appointment slots, complete consultations, log patient diagnosis, write prescriptions.

3. **General Nurse**
   - **Username**: `nursejoy`
   - **Access**: Approve appointments, review patient registries, view diagnosis charts, invoice patients.

4. **Patient Account**
   - **Username**: `johndoe`
   - **Access**: Schedule new consultations, review personal visit history, check prescription logs, pay outstanding invoices using mock payment cards.

---

## 📂 Project Architecture

```
hospital/
├── index.php                   # Clinic landing page (SVG graphics, quick links)
├── README.md                   # Setup guides and configurations overview
├── css/
│   ├── style.css               # Core styling (color variables, design system tokens)
│   ├── dashboard.css           # Portal layout system (sidebar grids, custom badges)
│   └── toast.css               # Animated feedback alert boxes
├── js/
│   ├── app.js                  # Global scripts (header sticky behavior, menu toggles)
│   ├── auth.js                 # Authentication client-side validations and AJAX requests
│   ├── dashboard.js            # Dashboard operations (populating tables, modals, payments)
│   └── toast.js                # Custom toast alerts library
├── includes/
│   ├── db.php                  # Secure PDO prepared database connection helper
│   ├── header.php              # Global navigation header template
│   └── footer.php              # Global template footer (loads scripts)
├── api/
│   ├── auth.php                # Auth operations (registers patients, logins)
│   ├── dashboard.php           # Aggregated statistics based on session roles
│   ├── doctors.php             # Doctor registries CRUD (admin permissions)
│   ├── departments.php         # Department specialties CRUD
│   ├── appointments.php        # Appointment slots booking and updates
│   ├── medical_records.php     # Diagnostic entries (written by doctors)
│   ├── billing.php             # Invoices retrieval and creation
│   └── payments.php            # Log transactions and clear outstanding bills
└── database/
    └── medicare_database.sql   # SQL structure definitions and seed values
```

## 🛡️ Security Best Practices Implemented

- **Prepared Statements**: All database operations throughout the backend PHP APIs utilize PDO prepared statement arrays to fully guard against SQL injection.
- **Bcrypt Password Salting**: Passwords are securely hashed with standard Bcrypt algorithms using PHP's native `password_hash()` and verified with `password_verify()`.
- **Session Protections**: User sessions are checked in the API layer to block unauthenticated edits or modifications.
- **Client & Server-Side Valids**: Field length checks, email regex checks, and sanitizations are handled on both the client (JS) and backend (PHP).
