-- Task 1: Make the description field not nullable -
ALTER TABLE `todos` CHANGE `description` `description` varchar(255) NOT NULL;
