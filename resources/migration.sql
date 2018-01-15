UPDATE users set password=md5(CONCAT('salt',password));


ALTER TABLE `todos` ADD `complete` TINYINT(1) NOT NULL DEFAULT '0' AFTER `description`;