-- use docsmd;
create table download (
	download_id integer not null primary key auto_increment,
	user_id integer not null,
	download_date timestamp default current_timestamp,
	constraint fk_download_users foreign key (user_id) references users(user_id) on delete cascade
 );