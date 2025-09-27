ALTER TABLE `users`
ADD COLUMN `password_reset_token` VARCHAR(255) NULL DEFAULT NULL AFTER `remember_token_expiry`,
ADD COLUMN `password_reset_expiry` DATETIME NULL DEFAULT NULL AFTER `password_reset_token`;
