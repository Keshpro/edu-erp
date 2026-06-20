# 🎓 EduCore ERP - University Management System

A complete university **ERP web application** built with **HTML, CSS, PHP, and MySQL**.  
Includes role-based portals for **Admins** and **Students**, with full CRUD functionality.

---

## ✨ Features

### 🛠️ Admin Portal
- Institutional Dashboard (live stats from DB)
- Student Directory (search, paginate, add, edit, delete)
- Course Catalog (full CRUD)
- Examination Management (schedule + publish results)
- Attendance Management (mark per course + date, view records)

### 🎓 Student Portal
- Personal Dashboard (GPA, credits, attendance, alerts)
- Course Registration (search, filter, register & drop)
- Profile (academic standing + grades + attendance)
- Notifications panel

---

## 🧰 Tech Stack
- **Frontend:** HTML5, CSS3 (custom modern design), Font Awesome 6, Inter font
- **Backend:** PHP 7.4+ (PDO + prepared statements)
- **Database:** MySQL / MariaDB
- **Auth:** bcrypt password hashing (`password_hash` / `password_verify`)

---

## 🚀 Setup Instructions

### 1. Install XAMPP
Download from https://www.apachefriends.org and install. Make sure both **Apache** and **MySQL** modules can start.

### 2. Place the project
Copy the `edu-erp` folder into `C:/xampp/htdocs/` (Windows) or `/Applications/XAMPP/htdocs/` (macOS).

### 3. Start services
Open XAMPP Control Panel → Start **Apache** and **MySQL**.

### 4. Import the database
- Open http://localhost/phpmyadmin
- Click **Import** → choose `edu-erp/database.sql` → Go
- This creates the `edu_erp` database with seed data.

### 5. Verify DB credentials
Default `config/db.php` uses `root` with empty password.  
Edit if your MySQL credentials differ.

### 6. Open the app
Visit: **http://localhost/edu-erp/**

---

## 🔑 Demo Accounts

| Role     | Email              | Password    |
|----------|--------------------|-------------|
| Admin    | admin@edu.com      | admin123    |
| Student  | student@edu.com    | student123  |
| Faculty  | faculty@edu.com    | faculty123  |

---

## 📁 Project Structure

```
edu-erp/
├── index.php                 # Entry point (redirects by role)
├── README.md
├── database.sql              # Full schema + seed data
│
├── config/
│   ├── db.php                # PDO connection
│   └── auth.php              # Session + role guards
│
├── assets/
│   ├── css/style.css         # Complete modern design system
│   └── images/logo.svg       # Graduation cap logo
│
├── partials/
│   ├── header.php            # <head> + opening layout
│   ├── sidebar.php           # Role-aware sidebar nav
│   ├── navbar.php            # Top bar (search, bell, user)
│   └── footer.php            # Closing tags
│
├── auth/
│   ├── login.php             # Sign-in screen
│   └── logout.php
│
├── admin/
│   ├── dashboard.php
│   ├── students.php
│   ├── add_student.php
│   ├── edit_student.php
│   ├── delete_student.php
│   ├── courses.php
│   ├── exams.php
│   └── attendance.php
│
└── student/
    ├── dashboard.php
    ├── registration.php
    ├── profile.php
    └── notifications.php
```

---

## 🎨 Design System

```css
--primary:   #1A365D   /* Deep navy */
--secondary: #FFFFFF
--tertiary:  #4A5568
--neutral:   #77777A
```

Font: **Inter** (Google Fonts).  
Icons: **Font Awesome 6** (CDN).

---

## 🖼️ About the Logo
The logo was unable to be cleanly extracted from the source PDF (low-res UI screenshots), so a clean **SVG graduation cap** in the brand color has been generated at:
```
assets/images/logo.svg
```
You can replace it anytime by overwriting the file.

---

## 🔒 Security Notes
- All DB queries use **PDO prepared statements**.
- All output is escaped with `htmlspecialchars`.
- Passwords are hashed with **bcrypt**.
- Role-based access enforced via `config/auth.php`.

---

## 📜 License
For educational / academic use.
