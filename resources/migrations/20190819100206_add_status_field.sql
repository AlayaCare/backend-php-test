-- Task 2: A user is able to mark an item as done
ALTER TABLE `todos` ADD `status` enum('pending','done') NOT NULL DEFAULT 'pending';
