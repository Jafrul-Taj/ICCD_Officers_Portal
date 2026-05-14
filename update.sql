-- ============================================================
-- update.sql
-- Run this INSTEAD of install.php when you already have data
-- in the employees table and do not want to drop it.
--
-- Usage (XAMPP):
--   C:\xampp\mysql\bin\mysql.exe -u root employee_management < update.sql
-- ============================================================

USE `employee_management`;

-- Step 1: Replace NULL with '' so NOT NULL conversion is safe
UPDATE `employees` SET `designation`  = '' WHERE `designation`  IS NULL;
UPDATE `employees` SET `division`     = '' WHERE `division`     IS NULL;
UPDATE `employees` SET `sub_division` = '' WHERE `sub_division` IS NULL;
UPDATE `employees` SET `email`        = '' WHERE `email`        IS NULL;
UPDATE `employees` SET `cell_number`  = '' WHERE `cell_number`  IS NULL;

-- Step 2: Add role column if it does not exist yet
--         (MySQL 5.7 will error if it already exists – safe to ignore that error)
ALTER TABLE `employees`
    ADD COLUMN `role` VARCHAR(100) NOT NULL DEFAULT '' AFTER `sub_division`;

-- Step 3: Strengthen NOT NULL constraints on all required fields
ALTER TABLE `employees`
    MODIFY COLUMN `eid`          VARCHAR(20)  NOT NULL,
    MODIFY COLUMN `name`         VARCHAR(100) NOT NULL,
    MODIFY COLUMN `designation`  VARCHAR(100) NOT NULL DEFAULT '',
    MODIFY COLUMN `division`     VARCHAR(100) NOT NULL DEFAULT '',
    MODIFY COLUMN `sub_division` VARCHAR(100) NOT NULL DEFAULT '',
    MODIFY COLUMN `email`        VARCHAR(100) NOT NULL DEFAULT '',
    MODIFY COLUMN `cell_number`  VARCHAR(20)  NOT NULL DEFAULT '';

-- Step 4: Ensure UNIQUE constraint on eid (add only if missing)
-- Check first: SELECT * FROM information_schema.TABLE_CONSTRAINTS
--              WHERE TABLE_NAME='employees' AND CONSTRAINT_NAME='uq_eid';
-- If it does not exist, uncomment the next line:
-- ALTER TABLE `employees` ADD UNIQUE KEY `uq_eid` (`eid`);

SELECT 'update.sql applied successfully.' AS result;
