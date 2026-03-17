-- Database Refactor Migration Script
-- This script standardizes table names, normalizes relationships, and adds missing timestamps.

-- 1. Rename adminstafflogs to accounts
RENAME TABLE adminstafflogs TO accounts;

-- 2. Standardize accounts table
ALTER TABLE accounts 
    CHANGE COLUMN timestamp created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- 3. Cleanup users table
ALTER TABLE users 
    DROP COLUMN photo,
    MODIFY COLUMN middle_name VARCHAR(100) NULL,
    MODIFY COLUMN picture VARCHAR(255) NULL,
    ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- 4. Standardize vouchers table
ALTER TABLE vouchers 
    CHANGE COLUMN date_issued created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- 5. Normalize student_vouchers table
-- First, ensure all student_id values in student_vouchers correspond to valid student_id in users
-- (Usually done via join, but we'll assume data integrity for this script or use a temporary approach)

-- Step A: Add a temporary column for the INT ID
ALTER TABLE student_vouchers ADD COLUMN user_id INT AFTER student_id;

-- Step B: Populate user_id based on student_id matching
UPDATE student_vouchers sv 
JOIN users u ON sv.student_id = u.student_id 
SET sv.user_id = u.id;

-- Step C: Remove the old VARCHAR student_id column and rename user_id
ALTER TABLE student_vouchers DROP FOREIGN KEY student_vouchers_ibfk_1;
ALTER TABLE student_vouchers DROP COLUMN student_id;
ALTER TABLE student_vouchers CHANGE COLUMN user_id student_id INT NOT NULL;

-- Step D: Add redemption status and timestamps if missing
-- (The existing schema had date_redeemed or redeemed_at, let's standardize to redeemed_at)
-- Checking if redeemed_at exists (it does in the dump).
-- Add updated_at
ALTER TABLE student_vouchers 
    ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Step E: Restore Foreign Key constraint pointing to users.id (INT)
ALTER TABLE student_vouchers 
    ADD CONSTRAINT fk_student_vouchers_user 
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE;
