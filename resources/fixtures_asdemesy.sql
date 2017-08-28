INSERT INTO users (username, password, salt, role) VALUES
('asdemesy', 'Q8U5bW+ruNRFvXdNd5xmiLpcSmZ9JS1FWPTPm6K/KbYNrwwk6xhvSL0AKkMBeg9HfDjBM2qNeBPTYk5cY/QoLg==', '4f66099d55095fa2c5ef3b3736cbd32749f233e0', 'ROLE_USER'),
('alayacare', 'kEa36t7dY049btoyIpnXS4cHToS1ai/VtPBHpkxF+pZfvFDUf9cOmlT9NkDKsI6JVURhdrzELO89feDABjAQXA==', '4f66099d55095fa2c5ef3b3736cbd32749f233e0', 'ROLE_USER');

INSERT INTO todos (user_id, description, completed) VALUES
(1, 'TASK 1: As a user I can''t add a todo without a description', 1),
(1, 'TASK 2: As a user I can mark a todo as completed', 1),
(1, 'TASK 3: As a user I can view a todo in a JSON format', 1),
(1, 'TASK 4: As a user I can see a confirmation message when I add/delete a todo', 1),
(1, 'TASK 5: As a user I can see my list of todos paginated', 1),
(1, 'TASK 6: Implement an ORM database access layer so we don’t have SQL in the controller code', 1),
(1, 'Drink coffee', 1),
(1, 'All the todos are private, users can''t see other user''s todos.', 1),
(1, 'Users must be logged in order to add/delete/see their todos.', 1),
(1, 'Security: Secure user session', 1),
(1, 'Meet Alayacare team', 0),
(2, 'Meet Anne-Sophie :)', 0);
