-- SQL Commands to Add Payment Method and Reference Number to Transactions Table
-- Run these in phpMyAdmin SQL tab if the migration script didn't work

-- Add payment_method column (Cash or GCash)
ALTER TABLE transactions 
ADD COLUMN payment_method VARCHAR(50) DEFAULT 'Cash' AFTER payment_status;

-- Add reference_number column (for GCash transactions)
ALTER TABLE transactions 
ADD COLUMN reference_number VARCHAR(100) DEFAULT NULL AFTER payment_method;
