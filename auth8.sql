drop database if exists auth8;

create database auth8;

drop user  auth8;

create user 'auth8'   identified by 'auth8';

grant all on auth8.* to auth8;

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
 
 
 ------------------------
 
 create table permission (
	permission_id tinyint not null primary key,
    permission_name varchar(20) not null unique,
    permission_description varchar(100),
    default_value boolean);
    
insert into permission values
 (1,'add_message',	'Добавлять сообщения',  true),
 (2,'add_replay',       'Отвечать на сообщения',true),
 (3,'add_attachment',   'Прикреплять файлы',    false) ,
 (4,'download',         'Скачивать файлы',      false),
 (5,'upload',           'Загружать файлы',      false) ;
 
 

create table users_permission(
user_id integer not null, 
permission_id tinyint not null,
permission_value boolean default false,
constraint fk_users_permission foreign key (user_id) references users(user_id) on delete cascade,
constraint fk_users_premission_permission foreign key (permission_id) references permission(permission_id),
constraint uq_users_permission unique(user_id,permission_id)
);
-- После доавления пользователя - нужно назначить права !
create trigger users_after_insert after insert on users for each  row
 insert into users_permission (user_id,permission_id,permission_value) select new.user_id,permission_id,default_value from permission; 

-- есть прописать права
delete from users_permission;
insert into users_permission (user_id,permission_id,permission_value) select user_id,permission_id,default_value from users,permission;
update users_permission set permission_value = true where user_id = (select user_id from users where login='admin');

-- посмотреть что получилось
select * from permission;   
select * from users_permission;


create table download (
	download_id integer not null primary key auto_increment,
	user_id integer not null,
	download_date timestamp default current_timestamp,
	constraint fk_download_users foreign key (user_id) references users(user_id) on delete cascade
 );

create view v_users as
select user_id,concat(last_name,' ',first_name) as user_name,login,user_role.role_name, email,email_confirmed,
  date_format(reg_date,'%d %m %Y') as reg_date,
  date_format((select max(visit_time) from visits where user_id=users.user_id),'%d %m %Y') as last_visit,
  (select count(*) from visits where user_id=users.user_id) as visit_count
  from users inner join user_role on users.role_id=user_role.role_id;
  
  select * from v_users;