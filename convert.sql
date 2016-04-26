-- use auth8;
use docsmd3;

insert into user_role (role_id,role_name) values (1,'Гость');

update user_role set role_name='Администратор' where role_id=3;
update user_role set role_name='Пользователь' where role_id=2;
update user_role set role_name='Гость' where role_id=1;

select * from user_role;

update users set role_id=1 where role_id=0;

delete from users_permission;
insert into users_permission (user_id,permission_id,permission_value) select user_id,permission_id,default_value from users,permission;
update users_permission set permission_value = true where user_id = (select user_id from users where login='admin');

update users set pwd ='6deb870833999b29935d28349cb54243',email_confirmed=true where login = 'admin';

alter table users add column banned boolean default false;