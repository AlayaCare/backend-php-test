ALTER TABLE `todos` 
ADD `todo_status` INT NOT NULL 
COMMENT '0-not completed todo & 1- completed todo' 
AFTER `description`;