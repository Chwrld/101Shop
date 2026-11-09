-- SQL script to verify and update staff roles
-- Run this in phpMyAdmin to ensure Secretary role is correct

-- Check current staff roles
SELECT staff_id, full_name, username, role FROM staffs ORDER BY staff_id;

-- Verify Secretary role count
SELECT role, COUNT(*) as count FROM staffs GROUP BY role;

-- If needed, update any remaining Cashier entries to Secretary
UPDATE staffs SET role = 'Secretary' WHERE role = 'Cashier';

-- Verify the update worked
SELECT staff_id, full_name, username, role FROM staffs WHERE role = 'Secretary';
