use auth8;

--   Права пользователя 14 апр 2016

drop trigger if exists users_after_insert;

drop table if exists users_permission;
drop table if exists permission;

create table permission (
	permission_id tinyint not null primary key,
    permission_name varchar(20) not null unique,
    permission_description varchar(100),
    default_value boolean);
    
insert into permission values
 (1,'add_message',	'Добавлять сообщения',true),
 (2,'replay_message','Отвечать на сообщения',true),
 (3,'add_attachment','Прикреплять файлы',false) ,
 (4,'download','Скачивать файлы',false),
 (5,'upload','Загружать файлы',false) ;
 
 

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

-- посмотреть что получилось
select * from permission;   
select * from users_permission;