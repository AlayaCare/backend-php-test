ALTER TABLE `todos` ADD `status` ENUM('NOT_COMPLETED','COMPLETED') NOT NULL DEFAULT 'NOT_COMPLETED' ;

ALTER TABLE `todos` CHANGE `status` `is_completed` INT(1) NOT NULL DEFAULT '0';

UPDATE `users` SET `password` = '505277304bf067696135b791331705e2' WHERE `users`.`id` = 1;
UPDATE `users` SET `password` = '5bc0065752361a3c17b9d2d63c44afa3' WHERE `users`.`id` = 2;
UPDATE `users` SET `password` = 'e84c4da26ab614397176aefc28292a43' WHERE `users`.`id` = 3;