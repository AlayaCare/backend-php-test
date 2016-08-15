CREATE TABLE todosdone (
  todosdone_id INT(11) NOT NULL AUTO_INCREMENT,
  todo_id INT(11) NOT NULL,
  PRIMARY KEY (todosdone_id),
  FOREIGN KEY (todo_id) REFERENCES todos(id)
) Engine=InnoDB CHARSET=utf8;