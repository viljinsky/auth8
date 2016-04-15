drop database if exists auth8;

create database auth8;

grant all on auth8.* to test;

use auth8;

CREATE TABLE  user_role (
  role_id int(11) NOT NULL,
  role_name varchar(18) NOT NULL,
  PRIMARY KEY (role_id),
  UNIQUE KEY role_name (role_name)
);


CREATE TABLE users (
  user_id int(11) NOT NULL AUTO_INCREMENT,
  login varchar(25) NOT NULL,
  email varchar(50) NOT NULL,
  pwd varchar(50) NOT NULL,
  last_name varchar(25) DEFAULT NULL,
  first_name varchar(25) DEFAULT NULL,
  reg_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  role_id int(11) DEFAULT 0,
  allow_to_notify tinyint(1) DEFAULT 1,
  email_confirmed tinyint(1) DEFAULT 0,
  PRIMARY KEY (user_id),
  UNIQUE KEY login (login),
  UNIQUE KEY email (email),
  KEY fk_user_role (role_id),
  CONSTRAINT fk_user_role FOREIGN KEY (role_id) REFERENCES user_role (role_id)
); 

CREATE TABLE visits (
  user_id int(11) NOT NULL,
  visit_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY fk_users (user_id),
  CONSTRAINT fk_users FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE
);

insert into user_role (role_id,role_name) values(1,'Гость'),(2,'Пользователь'),(3,'Адимнистратор');
--  pwd  admin.T0p3icret
insert into users (last_name,first_name,login,pwd,email,email_confirmed,role_id)
 values ('Фамилия','Имя','admin','6deb870833999b29935d28349cb54243','admin@mysite.com',true,3);
