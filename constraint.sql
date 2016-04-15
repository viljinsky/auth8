use auth8;

alter table visits drop foreign key fk_users;

alter table visits add constraint fk_users foreign key (user_id) references users(user_id) on delete cascade;