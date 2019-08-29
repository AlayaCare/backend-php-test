CREATE TABLE completed (
  id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  todo_id INT(11) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT todo_id FOREIGN KEY (todo_id) REFERENCES todos(id)
  ON DELETE CASCADE
) Engine=InnoDB CHARSET=utf8;