CREATE DATABASE university_voucher_system;
USE university_voucher_system;

-- Users Table 
	CREATE TABLE users (
		id INT AUTO_INCREMENT PRIMARY KEY,
		rfid VARCHAR(50) UNIQUE NULL, 
		student_id VARCHAR(50) UNIQUE NOT NULL,
		last_name VARCHAR(100) NOT NULL,
		first_name VARCHAR(100) NOT NULL,
		middle_name VARCHAR(100) NULL,
		sex ENUM('M', 'F') NOT NULL,
		role ENUM('student') NOT NULL,
		course VARCHAR(100) NULL, 
		year INT NULL, 
		section VARCHAR(50) NULL,	
		picture TEXT NULL,
		created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Vouchers Table (Tracks issued vouchers)
CREATE TABLE vouchers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voucher_code VARCHAR(50) UNIQUE NOT NULL,
    office_department VARCHAR(100) NOT NULL,
    date_issued TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    minutes_valid INT NOT NULL,
    status ENUM('available', 'used') DEFAULT 'available'
);

-- Student Vouchers Table (Links vouchers to students)
CREATE TABLE student_vouchers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    voucher_id INT NOT NULL,
    status ENUM('available', 'used') DEFAULT 'available',
    date_redeemed TIMESTAMP NULL,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (voucher_id) REFERENCES vouchers(id) ON DELETE CASCADE
);

-- Admin Actions Log (Tracks admin activities)
CREATE TABLE admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);
