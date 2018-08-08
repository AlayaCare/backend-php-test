INSERT INTO users (username, password) VALUES
('user1', Sha2('user1',256)),
('user2', Sha2('user2',256)),
('user3', Sha2('user3',256));

INSERT INTO todos (user_id, description) VALUES
(1, 'Vivamus tempus'),
(1, 'lorem ac odio'),
(1, 'Ut congue odio'),
(1, 'Sodales finibus'),
(1, 'Accumsan nunc vitae'),
(2, 'Lorem ipsum'),
(2, 'In lacinia est'),
(2, 'Odio varius gravida');