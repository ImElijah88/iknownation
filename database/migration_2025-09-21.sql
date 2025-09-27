ALTER TABLE `users`
ADD COLUMN `remember_token` VARCHAR(255) NULL DEFAULT NULL AFTER `progress_data`,
ADD COLUMN `remember_token_expiry` DATETIME NULL DEFAULT NULL AFTER `remember_token`,
ADD UNIQUE INDEX `remember_token_unique` (`remember_token`);
