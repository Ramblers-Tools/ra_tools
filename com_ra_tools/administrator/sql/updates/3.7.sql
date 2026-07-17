CREATE TABLE IF NOT EXISTS `#__ra_control` (
    `record_type` INT NOT NULL,
    `key_value` VARCHAR(255) NOT NULL,
PRIMARY KEY (`record_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;