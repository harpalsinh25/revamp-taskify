-- Cleanup Script for Leave Management System
-- Run this in your database to remove tables/columns created by the leave system

-- Drop user_leave_balances table
DROP TABLE IF EXISTS user_leave_balances;

-- Remove columns from leave_requests table (if they exist)
SET @dbname = DATABASE();
SET @tablename = 'leave_requests';

-- Drop total_days if exists
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname
     AND TABLE_NAME = @tablename
     AND COLUMN_NAME = 'total_days') > 0,
  'ALTER TABLE leave_requests DROP COLUMN total_days;',
  'SELECT 1;'
));
PREPARE alterStatement FROM @preparedStatement;
EXECUTE alterStatement;
DEALLOCATE PREPARE alterStatement;

-- Drop paid_days if exists
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname
     AND TABLE_NAME = @tablename
     AND COLUMN_NAME = 'paid_days') > 0,
  'ALTER TABLE leave_requests DROP COLUMN paid_days;',
  'SELECT 1;'
));
PREPARE alterStatement FROM @preparedStatement;
EXECUTE alterStatement;
DEALLOCATE PREPARE alterStatement;

-- Drop unpaid_days if exists
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname
     AND TABLE_NAME = @tablename
     AND COLUMN_NAME = 'unpaid_days') > 0,
  'ALTER TABLE leave_requests DROP COLUMN unpaid_days;',
  'SELECT 1;'
));
PREPARE alterStatement FROM @preparedStatement;
EXECUTE alterStatement;
DEALLOCATE PREPARE alterStatement;

-- Drop is_paid if exists
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname
     AND TABLE_NAME = @tablename
     AND COLUMN_NAME = 'is_paid') > 0,
  'ALTER TABLE leave_requests DROP COLUMN is_paid;',
  'SELECT 1;'
));
PREPARE alterStatement FROM @preparedStatement;
EXECUTE alterStatement;
DEALLOCATE PREPARE alterStatement;

-- Drop balance_deducted if exists
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname
     AND TABLE_NAME = @tablename
     AND COLUMN_NAME = 'balance_deducted') > 0,
  'ALTER TABLE leave_requests DROP COLUMN balance_deducted;',
  'SELECT 1;'
));
PREPARE alterStatement FROM @preparedStatement;
EXECUTE alterStatement;
DEALLOCATE PREPARE alterStatement;

-- Drop manually_marked_as if exists
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname
     AND TABLE_NAME = @tablename
     AND COLUMN_NAME = 'manually_marked_as') > 0,
  'ALTER TABLE leave_requests DROP COLUMN manually_marked_as;',
  'SELECT 1;'
));
PREPARE alterStatement FROM @preparedStatement;
EXECUTE alterStatement;
DEALLOCATE PREPARE alterStatement;

-- Done!
SELECT 'Leave management system tables/columns cleaned successfully!' AS Status;

