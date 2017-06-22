drop table if exists t_comment;
drop table if exists t_user;
drop table if exists t_article;

create table t_article (
    art_id integer not null primary key auto_increment,
    art_title varchar(100) not null,
    art_content varchar(2000) not null
) engine=innodb character set utf8 collate utf8_unicode_ci;

create table t_user (
    usr_id integer not null primary key auto_increment,
    usr_name varchar(50) not null,
    usr_password varchar(88) not null,
    usr_salt varchar(23) not null,
    usr_role varchar(50) not null 
) engine=innodb character set utf8 collate utf8_unicode_ci;

create table t_comment (
    com_id integer not null primary key auto_increment,
    com_content varchar(500) not null,
    art_id integer not null,
    usr_id integer not null,
	`com_level` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
	`com_is_deleted` tinyint(1) NOT NULL DEFAULT '0',
	parent_id mediumint(8) UNSIGNED DEFAULT NULL,
    constraint fk_com_art foreign key(art_id) references t_article(art_id),
    constraint fk_com_usr foreign key(usr_id) references t_user(usr_id)
) engine=innodb character set utf8 collate utf8_unicode_ci;

create table t_flags (
	flag_id mediumint(8) unsigned not null,
	flag_date datetime not null,
	flag_com_id mediumint(8) unsigned not null,
	flag_usr_id smallint(5) default null,
	flag_art_id smallint(5) unsigned not null,
	flag_ip binary(16) default null
) engine=InnoDB default charset=utf8;
