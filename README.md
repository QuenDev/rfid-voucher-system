# 🎫 RFID-Based Student Voucher System

[![GitHub License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4.svg?style=flat&logo=php)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1.svg?style=flat&logo=mysql)](https://www.mysql.com/)

A modern, high-efficiency web application designed to automate the distribution of internet vouchers in university environments using RFID technology. This system eliminates manual tracking and ensures fair, secure access to network resources for students.

---

## 🚀 Key Features

- **⚡ Real-Time RFID Integration**: Instant student identification and voucher issuance via RFID card simulation (Keyboard Emulation).
- **📊 Comprehensive Admin Dashboard**: Live monitoring of system statistics, including total students, voucher availability, and redemption progress bars.
- **👤 Student Management System**: Complete records management with automated photo uploads and CSV import capabilities.
- **🎟️ Voucher Lifecycle Management**: Secure importation and tracking of internet vouchers with automated status updates (Available/Used).
- **📝 Automated Reporting**: Detailed logs of daily redemptions and system-wide audits, exportable for administrative review.
- **🛡️ Security & Authentication**: Secure admin login system with session management and encrypted activity logging.

---

## 🛠️ Technical Stack

- **Backend**: PHP (Procedural/OOP)
- **Database**: MySQL (Relational Schema with Foreign Key Constraints)
- **Frontend**: HTML5, CSS3 (Modern Flexbox/Grid layouts), JavaScript (Vanilla, Fetch API)
- **Icons**: Font Awesome 6
- **Hardware Support**: Compatible with standard USB RFID Readers

---

## 📸 Core Modules

### 📍 Student Scanning Interface
The primary interface for students. Upon tapping an RFID card, the system instantly displays the student's photo, name, and their uniquely assigned voucher code.

### 📈 Analytics Dashboard
Provides administrators with a bird's-eye view of connectivity metrics, allowing for efficient resource planning and usage monitoring.

---

## 🔧 Installation & Setup

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/QuenDev/rfid-voucher-system.git
   ```

2. **Database Configuration**:
   - Create a database named `university_voucher_system`.
   - Import the `university_voucher_schema.sql` file.
   - Update `config.php` with your database credentials.

3. **Web Server**:
   - Deploy to a PHP-enabled server (XAMPP, WAMP, or Linux Apache).
   - Ensure the `uploads/` directory has write permissions.

---

## 🤝 Contributing
Contributions are welcome! Please feel free to submit a Pull Request.

---

## 📄 License
This project is licensed under the MIT License.
