# RFID-Based Student Voucher System

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4.svg?style=flat&logo=php)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1.svg?style=flat&logo=mysql)](https://www.mysql.com/)

Feature-focused RFID voucher redemption system built for academic use and portfolio showcase.

## Project Status

- Completed as a local/internal system (XAMPP environment).
- Not deployed publicly.
- Primary goal: demonstrate end-to-end features and workflow.

## Scope (Important)

This project is currently intended for:

- classroom/demo use
- local/internal testing
- portfolio presentation

It is **not yet production-hardened** for internet exposure.

## Core Features

- Public student RFID scanning page
- Admin login and dashboard
- Student photo display after successful RFID scan
- Voucher redemption with anti-spam limits
  - max 2 vouchers per 60 minutes
  - max 2 vouchers per day
- Student management
  - add, edit, view, delete (soft delete)
  - import students
- Voucher management
  - add, edit, delete (soft delete)
  - import vouchers
- Reports module
  - filtered redemption records
  - CSV export

## Tech Stack

- PHP (procedural + service-class OOP style)
- MySQL / MariaDB
- HTML/CSS/JavaScript (vanilla)
- Font Awesome
- XAMPP (Apache + MySQL)

## Entry Points

- Home/Landing: `index.html`
- Public RFID Scanner: `client/rfid.php`
- Admin Login: `client/login.php`

## Local Setup

1. Clone repository:

```bash
git clone <your-repo-url>
```

2. Put project inside XAMPP `htdocs`:

`D:\xampp\htdocs\rfid-student-voucher-system`

3. Create database:

- DB name: `university_voucher_system`
- Import: `Database Files/university_voucher_system.sql`

4. Configure `.env`:

- Copy `.env.example` to `.env`
- Set:
  - `DB_HOST`
  - `DB_USER`
  - `DB_PASS`
  - `DB_NAME`

5. Start Apache + MySQL in XAMPP.

## Import / Export Notes

- Student and voucher imports accept `.xlsx` and `.csv`.
- Reports export is `.csv` (Excel-compatible).
- If `.xlsx` import fails due to ZIP support:
  - open `D:\xampp\php\php.ini`
  - ensure `extension=zip` is enabled
  - restart Apache

## Portfolio Demo Flow (Suggested)

1. Login as admin
2. Import students
3. Import vouchers
4. Open public RFID page and scan student RFID
5. Show student photo + issued voucher
6. Open reports and export CSV

## Current Limitations

- Built as a local/internal app first, not cloud-deployed.
- Security hardening and production deployment pipeline are future improvements.
- Spreadsheet support depends on local PHP extension/runtime configuration.

## License

MIT
