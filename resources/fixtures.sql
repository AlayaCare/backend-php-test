INSERT INTO users (username, password) VALUES
('user1', 'user1'),
('user2', 'user2'),
('user3', 'user3');

INSERT INTO todos (user_id, description) VALUES
(1, 'Vivamus tempus'),
(1, 'lorem ac odio'),
(1, 'Ut congue odio'),
(1, 'Sodales finibus'),
(1, 'Accumsan nunc vitae'),
(2, 'Lorem ipsum'),
(2, 'In lacinia est'),
(2, 'Odio varius gravida');


/* To be able to mark ToDos as completed */ ALTER TABLE `todos` ADD `completed_at` TIMESTAMP  NULL  AFTER `description`;