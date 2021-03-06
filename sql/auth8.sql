
-- drop user  docsmd3;

-- create user 'auth9'   identified by 'auth9';

-- grant select, insert , delete, update  on auth9.* to auth9;

-- use auth9;

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
  banned boolean default false,
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
 values ('Администратор','','admin','6deb870833999b29935d28349cb54243','admin@mysite.com',true,3);
 
 
 ------------------------
 
 create table permission (
	permission_id tinyint not null primary key,
    permission_name varchar(20) not null unique,
    permission_description varchar(100),
    default_value boolean);
    
insert into permission values
 (1,'add_message',	'Добавлять сообщения',  true),
 (2,'add_replay',       'Отвечать на сообщения',true),
 (3,'add_attachment',   'Прикреплять файлы',    true) ,
 (4,'download',         'Скачивать файлы',      true),
 (5,'upload',           'Загружать файлы',      true) ;
 
 

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


create table user_payment(
    operation_id varchar(18),
	user_id integer not null,
    payment_date timestamp default current_timestamp(),
    amount decimal(10,2),
    withdraw_amount decimal(10,2),
    constraint fk_user_peyment_users foreign key (user_id) references users(user_id) 
);


create view v_permission as
select user_id,role_id,banned,
sum(case permission_id when 1 then permission_value else 0 end) as append,  -- добавлять сообщения
sum(case permission_id when 2 then permission_value else 0 end) as replay,  -- отвечать
sum(case permission_id when 3 then permission_value else 0 end) as attach,  -- прикреплять файлы
sum(case permission_id when 4 then permission_value else 0 end) as download,  -- скачивать
sum(case permission_id when 5 then permission_value else 0 end) as upload   -- загружать 
from users_permission inner join users using(user_id) 
group by user_id,role_id,banned;


create view v_users as
select user_id,concat(last_name,' ',first_name) as user_name,login,user_role.role_name, email,email_confirmed,users.banned,users.role_id,
  date_format(reg_date,'%d %m %Y') as reg_date,
  date_format((select max(visit_time) from visits where user_id=users.user_id),'%d %m %Y') as last_visit,
  (select count(*) from visits where user_id=users.user_id) as visit_count,
  append,replay,attach,download,upload
  from users inner join user_role using(role_id)
  inner join v_permission using(user_id);

