CREATE TABLE users (
  id INT(11) NOT NULL AUTO_INCREMENT,
  username VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  PRIMARY KEY (id)
) Engine=InnoDB CHARSET=utf8;

CREATE TABLE todos (
  id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  description VARCHAR(255),
  status tinyint(4) NOT NULL DEFAULT '0' COMMENT '0 - Open Tasks\n1 - Completed Tasks',
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users(id)
) Engine=InnoDB CHARSET=utf8;
