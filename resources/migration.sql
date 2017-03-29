ALTER TABLE `ac_todos`.`todos` 
ADD COLUMN `completed` TINYINT NOT NULL DEFAULT 0 AFTER `description`;
