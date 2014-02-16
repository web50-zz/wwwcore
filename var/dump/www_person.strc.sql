CREATE TABLE `www_person` (
	`id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
	`order` smallint(5) unsigned NOT NULL,
	`person_title` varchar(255) NOT NULL,
	`person_name` varchar(128) NOT NULL,
	`category` tinyint(1) unsigned NOT NULL ,
	`real_name` varchar(64) NOT NULL,
	`comment` varchar(255) NOT NULL,
	`description` text NOT NULL,
	PRIMARY KEY (`id`),
	KEY `order` (`order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
