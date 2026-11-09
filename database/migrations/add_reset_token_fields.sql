-- Add reset token fields to staff table
ALTER TABLE `staff`
    ADD COLUMN `reset_token` VARCHAR(64) DEFAULT NULL,
    ADD COLUMN `reset_token_expiry` TIMESTAMP DEFAULT NULL;

-- Add index to improve lookup performance
CREATE INDEX `idx_reset_token` ON `staff` (`reset_token`);

-- Ensure reset token is unique when not null
ALTER TABLE `staff` 
    ADD UNIQUE KEY `unq_reset_token` (`reset_token`);