DROP TABLE IF EXISTS `#__ra_emails`;
CREATE TABLE  `#__ra_emails` (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `sub_system` VARCHAR(10)  NULL  DEFAULT "",
    `record_type` VARCHAR(2)  NULL  DEFAULT "",
    `ref` INT  DEFAULT "0", 
    `date_sent` VARCHAR(20)  NULL  DEFAULT "",
    `sender_name` VARCHAR(100)  NULL  DEFAULT "",
    `sender_email` VARCHAR(100)  NULL  DEFAULT "",
    `addressee_name` VARCHAR(100)  NULL  DEFAULT "",
    `addressee_email` TEXT,
    `title` VARCHAR(100)  NOT NULL ,
    `body` TEXT NOT NULL ,
    `attachments` TEXT NULL ,
    `state` TINYINT(1)  NULL  DEFAULT 1,
    `created` DATETIME NULL  DEFAULT NULL ,
    `created_by` INT(11)  NULL  DEFAULT 0,
    `modified` DATETIME NULL  DEFAULT NULL ,
    `modified_by` INT(11)  NULL  DEFAULT 0,
    `checked_out` INT(11)  UNSIGNED,
    `checked_out_time` DATETIME NULL  DEFAULT NULL ,
PRIMARY KEY (`id`)
    ,KEY `idx_ref` (`ref`)
    ,KEY `idx_state` (`state`)
    ,KEY `idx_checked_out` (`checked_out`)
    ,KEY `idx_created_by` (`created_by`)
    ,KEY `idx_modified_by` (`modified_by`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;
