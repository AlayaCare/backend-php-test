INSERT INTO users (username, password) VALUES
('user1', '$2y$10$RNh71xuKpWHuMfXD49bmqu6JCKc/.WvccxrRyF2Jv9gpS2XVtw51u'),
('user2', '$2y$10$PQOacN.zUPOiqOJvGULXQeb9UJu30bfkUqyaa0WkoxUuR./LjmxtS'),
('user3', '$2y$10$2OV3wAjeYfim0EfGsE8J7uasolCH/UswKbWhanodNjAkZSaL.rHpy');

INSERT INTO todos (user_id, description) VALUES
(1, 'Vivamus tempus'),
(1, 'lorem ac odio'),
(1, 'Ut congue odio'),
(1, 'Sodales finibus'),
(1, 'Accumsan nunc vitae'),
(2, 'Lorem ipsum'),
(2, 'In lacinia est'),
(2, 'Odio varius gravida');