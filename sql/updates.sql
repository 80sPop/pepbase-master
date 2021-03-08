/* Use when converting database from Pepbase 3 to Pepbase 4

/* remove deprecated tables */
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `shared_portions`;
DROP TABLE IF EXISTS `sort_pantry_activity`;
DROP TABLE IF EXISTS `sort_products`;
DROP TABLE IF EXISTS `work_bench`;
DROP TABLE IF EXISTS `sort_018`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `access_levels`;

/* rename tables */
ALTER TABLE `signin_accounts` RENAME TO `users`; 
ALTER TABLE jtproducts RENAME TO products; 
ALTER TABLE jtproducts_nameinfo RENAME TO products_nameinfo; 
ALTER TABLE jtproducts_pantryinfo RENAME TO products_pantryinfo; 
ALTER TABLE jtproducts_typeinfo RENAME TO products_typeinfo;

/* drop sa_ prefix and unused columns, add phone */
ALTER TABLE `users` CHANGE `sa_id` `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `users` CHANGE `sa_pantry_id` `pantry_id` SMALLINT NOT NULL;
ALTER TABLE `users` CHANGE `sa_access_level_id` `access_level` SMALLINT NOT NULL;
ALTER TABLE `users` CHANGE `sa_first_name` `firstname` varchar(100) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `users` CHANGE `sa_last_name` `lastname` varchar(100) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `users` CHANGE `sa_signin_name` `username` varchar(100) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `users` CHANGE `sa_email` `email` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'email, unique';
ALTER TABLE `users` CHANGE `sa_last_signin` `last_signin` datetime NOT NULL;
ALTER TABLE `users` CHANGE `sa_is_active` `is_active` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `users` CHANGE `sa_password` `password` varchar(255) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'password in salted and hashed format';
ALTER TABLE `users` ADD `phone` VARCHAR(25) NOT NULL  AFTER `email`;
ALTER TABLE `users` DROP `sa_pantry`;
ALTER TABLE `users` DROP `sa_access_level`;

ALTER TABLE `consumption` ADD `quantity_approved` TINYINT(3) NOT NULL AFTER `quantity_oked`;
UPDATE `consumption` SET `quantity_approved`=`quantity_oked`;

/* reset user_log */
DROP TABLE IF EXISTS `user_log`;
CREATE TABLE `user_log` (
	`id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`date_time` datetime NOT NULL,
	`user_id` int(11) DEFAULT NULL,
	`pantry_id` smallint(6) DEFAULT NULL,
	`household_id` int(10) DEFAULT NULL,  
	`db_table` varchar(50) DEFAULT NULL, 
	`table_id` int(11) DEFAULT NULL,  
	`action` varchar(50) DEFAULT NULL,    
	`shopping_date` date DEFAULT NULL,
	`shopping_time` time DEFAULT NULL,
	`ip_address` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/* update access_levels  */
ALTER TABLE `access_levels` CHANGE `al_id` `id` INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `access_levels` CHANGE `al_name` `name` VARCHAR(100) CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_hh_profile_update` hh_profile_update varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_hh_profile_delete` hh_profile_delete varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_hh_profile_browse` hh_profile_browse varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_hh_members_update` hh_members_update varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_hh_members_delete` hh_members_delete varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_hh_members_browse` hh_members_browse varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_hh_eligible_update` hh_eligible_update varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_hh_eligible_delete` hh_eligible_delete varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_hh_eligible_browse` hh_eligible_browse varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_hh_history_update` hh_history_update varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_hh_history_delete` hh_history_delete varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_hh_history_browse` hh_history_browse varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_prod_def_update` prod_def_update varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_prod_def_delete` prod_def_delete varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_prod_def_browse` prod_def_browse varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_instock_update` instock_update varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_instock_delete` instock_delete varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_instock_browse` instock_browse varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_prod_setup_update` prod_setup_update varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_prod_setup_delete` prod_setup_delete varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_prod_setup_browse` prod_setup_browse varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_access_level_update` access_level_update varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_access_level_delete` access_level_delete varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_access_level_browse` access_level_browse varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_signin_accounts_update` signin_accounts_update varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_signin_accounts_delete` signin_accounts_delete varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_signin_accounts_browse` signin_accounts_browse varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_languages_update` languages_update varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_languages_delete` languages_delete varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_languages_browse` languages_browse varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_shared_portions_update` shared_portions_update varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_shared_portions_delete` shared_portions_delete varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_shared_portions_browse` shared_portions_browse varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_shelters_update` shelters_update varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_shelters_delete` shelters_delete varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_shelters_browse` shelters_browse varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_pantries_update` pantries_update varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_pantries_delete` pantries_delete varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_pantries_browse` pantries_browse varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_themes_update` themes_update varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_themes_delete` themes_delete varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_themes_browse` themes_browse varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_advanced_update` advanced_update varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_advanced_delete` advanced_delete varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_advanced_browse` advanced_browse varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_reports_con_browse` reports_con_browse varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_reports_demo_browse` reports_demo_browse varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_reports_charts_browse` reports_charts_browse varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `al_reports_admin_browse` reports_admin_browse varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` ADD `level` SMALLINT NOT NULL  AFTER `id`;
ALTER TABLE `access_levels` CHANGE `signin_accounts_update` users_update varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `signin_accounts_delete` users_delete varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` CHANGE `signin_accounts_browse` users_browse varchar(10) COLLATE latin1_general_ci DEFAULT NULL;
ALTER TABLE `access_levels` DROP `shared_portions_update`;
ALTER TABLE `access_levels` DROP `shared_portions_delete`;
ALTER TABLE `access_levels` DROP `shared_portions_browse`;
ALTER TABLE `access_levels` ADD `measures_update` varchar(10) DEFAULT NULL AFTER `languages_browse`;
ALTER TABLE `access_levels` ADD `measures_delete` varchar(10) DEFAULT NULL AFTER `measures_update`;
ALTER TABLE `access_levels` ADD `measures_browse` varchar(10) DEFAULT NULL AFTER `measures_delete`;
ALTER TABLE `access_levels` ADD `containers_update` varchar(10) DEFAULT NULL AFTER `measures_browse`;
ALTER TABLE `access_levels` ADD `containers_delete` varchar(10) DEFAULT NULL AFTER `containers_update`;
ALTER TABLE `access_levels` ADD `containers_browse` varchar(10) DEFAULT NULL AFTER `containers_delete`;
ALTER TABLE `access_levels` ADD `changepantry_browse` varchar(10) DEFAULT NULL AFTER `shelters_browse`;
ALTER TABLE `access_levels` ADD `userlog_browse` varchar(10) DEFAULT NULL AFTER `changepantry_browse`;
ALTER TABLE `access_levels` ADD `convert_browse` varchar(10) DEFAULT NULL AFTER `userlog_browse`;

/* pantries */
ALTER TABLE pantries CHANGE pantryID id SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT;

/* matched display */
ALTER TABLE matched_display ADD COLUMN is_active tinyint NOT NULL AFTER email;
ALTER TABLE matched_display ADD COLUMN initial varchar(10) NOT NULL AFTER matchedlast;
ALTER TABLE matched_display 
ADD COLUMN nick1 varchar(30) NOT NULL,
ADD COLUMN nick2 varchar(30) NOT NULL,
ADD COLUMN nick3 varchar(30) NOT NULL,
ADD COLUMN nick4 varchar(30) NOT NULL,
ADD COLUMN nick5 varchar(30) NOT NULL,
ADD COLUMN nick6 varchar(30) NOT NULL,
ADD COLUMN nick7 varchar(30) NOT NULL,
ADD COLUMN nick8 varchar(30) NOT NULL,
ADD COLUMN nick9 varchar(30) NOT NULL,
ADD COLUMN nick10 varchar(30) NOT NULL,
ADD COLUMN nick11 varchar(30) NOT NULL,
ADD COLUMN nick12 varchar(30) NOT NULL,
ADD COLUMN nick13 varchar(30) NOT NULL,
ADD COLUMN nick14 varchar(30) NOT NULL,
ADD COLUMN nick15 varchar(30) NOT NULL;

/* members */
UPDATE members SET initial="" WHERE initial IS NULL;
ALTER TABLE members 
ADD COLUMN nick1 varchar(30) NOT NULL,
ADD COLUMN nick2 varchar(30) NOT NULL,
ADD COLUMN nick3 varchar(30) NOT NULL,
ADD COLUMN nick4 varchar(30) NOT NULL,
ADD COLUMN nick5 varchar(30) NOT NULL,
ADD COLUMN nick6 varchar(30) NOT NULL,
ADD COLUMN nick7 varchar(30) NOT NULL,
ADD COLUMN nick8 varchar(30) NOT NULL,
ADD COLUMN nick9 varchar(30) NOT NULL,
ADD COLUMN nick10 varchar(30) NOT NULL,
ADD COLUMN nick11 varchar(30) NOT NULL,
ADD COLUMN nick12 varchar(30) NOT NULL,
ADD COLUMN nick13 varchar(30) NOT NULL,
ADD COLUMN nick14 varchar(30) NOT NULL,
ADD COLUMN nick15 varchar(30) NOT NULL;

/* products */
ALTER TABLE products CHANGE COLUMN eligible_for age_group enum('all','infant','youth','teen','adult','inf_youth','youth_teen_adult','teen_adult') NOT NULL DEFAULT 'all';
ALTER TABLE products CHANGE COLUMN qty container varchar(30) DEFAULT NULL;
ALTER TABLE products ADD COLUMN measure varchar(30) NOT NULL AFTER amount;

/* products_typeinfo */
ALTER TABLE products_typeinfo DROP id;

/* products_pantryinfo */
ALTER TABLE products_pantryinfo ADD typenum TINYINT(3) UNSIGNED NOT NULL AFTER productID;

/* add containers */
DROP TABLE IF EXISTS `containers`;
CREATE TABLE `containers` ( 
`id` SMALLINT NOT NULL AUTO_INCREMENT,  
`name` VARCHAR(40) NOT NULL,
PRIMARY KEY (`id`)) ENGINE = InnoDB;
INSERT INTO `containers` (`name`) VALUES ('bar');
INSERT INTO `containers` (`name`) VALUES ('bottle');
INSERT INTO `containers` (`name`) VALUES ('box');
INSERT INTO `containers` (`name`) VALUES ('can');
INSERT INTO `containers` (`name`) VALUES ('pkg');
INSERT INTO `containers` (`name`) VALUES ('roll');
INSERT INTO `containers` (`name`) VALUES ('tube');

/* add measures */
DROP TABLE IF EXISTS `measures`;
CREATE TABLE `measures` ( 
`id` SMALLINT NOT NULL AUTO_INCREMENT,  
`name` VARCHAR(40) NOT NULL,
`abbrev` VARCHAR(40) NOT NULL,
PRIMARY KEY (`id`)) ENGINE = InnoDB;
INSERT INTO `measures` (`name`,`abbrev`) VALUES ('count','ct');
INSERT INTO `measures` (`name`,`abbrev`) VALUES ('pounds','lbs');
INSERT INTO `measures` (`name`,`abbrev`) VALUES ('ounces','oz');
INSERT INTO `measures` (`name`,`abbrev`) VALUES ('sheets','');
INSERT INTO `measures` (`name`,`abbrev`) VALUES ('square feet', 'sq ft');
INSERT INTO `measures` (`name`,`abbrev`) VALUES ('yards','yd');

/* password reset */
ALTER TABLE `password_reset` CHANGE `sa_id` `user_id` INT(11) NOT NULL;