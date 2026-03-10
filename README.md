# NARA Laboratory Management System (PHTD-App)

Welcome to the internal **Laboratory Management System** web application built for the **National Aquatic Resources Research & Development Agency (NARA)**. This system is designed to streamline laboratory workflows, handle sample submissions, manage testing parameters and results, and generate secure, professional test reports for internal and client use.

---

## 🚀 Key Features

### 1. Advanced Authentication & RBAC (Role-Based Access Control)

- **Multi-Role System**: Includes distinct access levels for Administrators, Lab Technicians, Analysts, and Viewers.
- **Secure Password Hashing**: Utilizes modern PHP `password_hash()` and `password_verify()` techniques.
- **Session Management**: Automatic session expiration, session fixation prevention (`session_regenerate_id`), and robust logout handling.

### 2. Sample Management (SAF Workflow)

- **Sample Submission**: A comprehensive wizard for entering incoming sample details, including client information, origin, and required analyses.
- **Tracking & Status**: Monitor the status of samples moving from submission through testing and reporting.
- **Sample Records**: Deep view of historical and current sample transactions.

### 3. Laboratory Configuration Management

- **Test Parameters & Variants**: Full CRUD management of lab testing parameters (e.g., pH, heavy metals, microbial counts) and parameter variants.
- **Test Methods**: Store and manage analytical methods used in the lab.
- **Pricing Configuration**: Maintain an up-to-date pricing structure for all tests and extra items.
- **Swab Parameters**: Specialized data entry for environmental swab samples.
- **Entities Management**: Interfaces to manage Clients and Cities/Regions.

### 4. Advanced Reporting System

- **Result Entry**: Dynamic forms for lab technicians to input analytical results against submitted parameters.
- **Report Generation**: Combine sample data, results, authorized signatories, and lab accreditations into single, unified views.
- **Report Printing & Export**: Print-ready, pixel-perfect report views optimized for A4 paper. Integrated tracking of print history.

### 5. Responsive UI/UX

- **Dynamic Aesthetic**: Modern, premium look with deep blues and clean typography, conforming to professional government UI standards.
- **Asynchronous Interactions**: Most data tables, forms, and modals are heavily driven by JavaScript (AJAX) to provide a single-page application (SPA) feel.

---

## 🛠 Technology Stack

### Frontend Architecture

- **HTML5 & CSS3**: Custom grid and flexbox-based layouts.
- **Vanilla JavaScript**: Lightweight, dependency-free DOM manipulation and AJAX form handling.
- **Bootstrap 5 (CSS Only)**: Used primarily for rapid grid structuring, utilities, and generic form component styling.
- **CSS Variables**: A well-structured `style.css` driving a unified design system (e.g., `--primary-blue`, `--primary-blue-dark`).
- **Icons**: FontAwesome & Bootstrap Icons integration.

### Backend Architecture

- **Language**: PHP 8.x
- **Pattern**: Custom MVC-like (Model-View-Controller) structure ensuring high maintainability and segregation of concerns.
- **Routing**: Query-parameter based simple routing managed by `index.php` incorporating an `Includes/` loader.
- **Database**: MySQL over `mysqli` object-oriented connections.

---

## 📂 Deep Dive into Project Structure

```text
├── Config/                        # Global Environment logic
│   ├── Database.php               # MySQLi connection wrapper
│   └── roles-permissions.php      # RBAC middleware definitions
├── db/                            # Database initialization
│   └── lab.sql                    # Skeleton database structure (imports schema)
├── public/                        # Publicly accessible assets
│   ├── assets/
│   │   ├── css/                   # Modular CSS per view (login, dashboard, forms)
│   │   ├── js/                    # Client-side validation, charts, AJAX fetch API
│   │   └── libs/                  # Client-side 3rd party scripts (e.g., html2pdf)
│   └── images/                    # NARA logos and UI imagery
├── src/                           # Backend Application Code
│   ├── Controllers/               # Business logic, request/response cycle
│   │   ├── auth-controller.php    # Manages login/logout loops
│   │   ├── sample-controller.php  # Handles the core sample submission workflow
│   │   └── (14+ specific controllers for granular data entities)
│   ├── Helpers/                   # Reusable functions & abstract utility classes
│   │   └── FormDataValidator.php  # Server-side deep validation rules
│   ├── Includes/                  # View Partial Templates & UI Shell
│   │   ├── access-control.php     # Intercepts requests unauthenticated users
│   │   ├── sidebar.php            # Dynamic navigation menu module
│   │   └── (Dynamic Dashboard page views)
│   ├── Models/                    # Data Access Layer (DAL) interacting with MySQL
│   │   ├── ParameterModel.php     # Complex JOIN queries for tests
│   │   ├── SAFModel.php           # Relational logic for Form processing
│   │   └── (15+ model classes providing clear parameterized queries protecting against SQLi)
│   └── Views/                     # Full-Page Renderings lacking Layout wrappers
│       └── login.php              # Secure standalone authentication portal
├── README.md                      # This documentation reference
├── index.php                      # Web Front-Controller
```

---

## 🗄️ Database Schema & Entities Overview

The application relies on a normalized relational MySQL schema with foreign keys strictly enforcing data integrity:

1. **`users`**: Maintains `user_id`, `username`, `password_hash`, `role` (Admin, Technician), and `status`.
2. **`clients` & `cities`**: Relational tracking of customer geographic nodes.
3. **`samples` & `saf_records`**: Complex bridging table relating incoming physical samples to required test arrays.
4. **`parameters`, `variants`, `test_methods`, `pricing`**: High-cardinality configuration tables dictating laboratory capabilities.
5. **`print_history`**: Audit trail of when reports were generated/printed for clients.

_(Note: During development, refer to `db/lab(XX).sql` for exact column declarations and constraints.)_

---

## 🔒 Security Best Practices Implemented

1. **SQL Injection**: Complete usage of `$stmt->bind_param(...)` across all MySQLi endpoints.
2. **XSS Protection**: Consistent output escaping using `htmlspecialchars()` in PHP views.
3. **CSRF Minimization**: AJAX form actions bounded to session validations.
4. **Hidden Pages**: The `access-control.php` file prevents direct URL navigation unauthenticated. Unlinked pages throw immediate permission traps.

---

## ⚙️ Installation & Setup

1. **Prerequisites**
   - PHP versions 8.0 or highly recommended 8.2+
   - MySQL / MariaDB Server 10.4+
   - PDO & MySQLi PHP Extensions enabled.

2. **Repository Clone**
   Pull the repository securely into your document root (`htdocs` or `/var/www/html/`).

3. **Database Import**
   Create a local MySQL Database named `lab`. Run the latest supplied `.sql` dump inside `db/` against this database using phpMyAdmin or CLI:

   ```bash
   mysql -u root -p lab < db/lab.sql
   ```

4. **Environment Config**
   Modify `Config/Database.php` setting the `$host`, `$username`, and `$password` appropriate for your environment.

5. **Start Application**
   For rapid testing, boot up PHP's built-in webserver from the project root:
   ```bash
   php -S localhost:3000
   ```
   Navigate to `http://localhost:3000` in your browser.

---

## 🔑 Default Administrator Credentials

For initial deployment and auditing purposes, use the following credentials. **It is highly recommended to change this password via the dashboard after first launch.**

- **Username**: `rootAdmin`
- **Password**: `Rootuser@19`

---

_Developed for the National Aquatic Resources Research & Development Agency (NARA)._  
_v1.0.0 Internal Release._
