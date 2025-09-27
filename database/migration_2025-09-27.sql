CREATE TABLE `login_attempts` (
  `ip_address` VARCHAR(45) NOT NULL,
  `attempts` INT NOT NULL DEFAULT 1,
  `last_attempt_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
