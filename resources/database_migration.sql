ALTER TABLE `alayacare`.`todos`   
  ADD COLUMN `status` SMALLINT(2) DEFAULT 0  NOT NULL AFTER `description`;
