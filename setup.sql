/*
	TODO
    - indexing
    - foreign key constraints
    - unique constraints
*/

/*
CREATE SCHEMA propay;
CREATE USER propay@localhost IDENTIFIED BY 'pr0p@y';
GRANT ALL PRIVILEGES ON propay.* TO propay@localhost;
FLUSH PRIVILEGES;
*/

DROP SCHEMA propay;
CREATE SCHEMA propay;

USE `propay`;

CREATE TABLE `users` (
    `user_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(512) NOT NULL,
    PRIMARY KEY (`user_id`)
);

CREATE TABLE `persons` (
    `person_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `first_name` VARCHAR(255) NOT NULL,
    `last_name` VARCHAR(255) NOT NULL,
    `id_number` CHAR(13) NOT NULL UNIQUE,
    `cell_number` CHAR(10) NOT NULL UNIQUE,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    PRIMARY KEY (`person_id`),
    INDEX (`last_name`)
);

CREATE TABLE `languages` (
    `language_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `language` VARCHAR(255) NOT NULL UNIQUE,
    PRIMARY KEY (`language_id`)
);

CREATE TABLE `language_person` (
    `language_id` BIGINT UNSIGNED NOT NULL,
    `person_id` BIGINT UNSIGNED NOT NULL UNIQUE -- one to one
);

CREATE TABLE `interests` (
    `interest_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `interest` VARCHAR(255) NOT NULL UNIQUE,
    PRIMARY KEY (`interest_id`)
);

CREATE TABLE `interest_person` (
    `interest_id` BIGINT UNSIGNED NOT NULL,
    `person_id` BIGINT UNSIGNED NOT NULL,
    UNIQUE `unique_interest_person` (`interest_id`, `person_id`)
);

insert into `users`
(`username`, `password`)
values
('user', md5('pwd'));

insert into `languages`
(`language`)
values
('Afrikaans');
insert into `languages`
(`language`)
values
('English');
insert into `languages`
(`language`)
values
('Swahili');
insert into `languages`
(`language`)
values
('isiZulu');
insert into `languages`
(`language`)
values
('Manderin');
insert into `languages`
(`language`)
values
('Inuit');

insert into `interests`
(`interest`)
values
('Reading');
insert into `interests`
(`interest`)
values
('Fishing');
insert into `interests`
(`interest`)
values
('Cycling');
insert into `interests`
(`interest`)
values
('Jogging');
insert into `interests`
(`interest`)
values
('Fox Hunting');
insert into `interests`
(`interest`)
values
('Bird Watching');
insert into `interests`
(`interest`)
values
('Comic Conventions');