CREATE TABLE `www_client` (
	`id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
	`order` smallint(5) unsigned NOT NULL,
	`client_name` varchar(128) NOT NULL,
	`real_name` varchar(64) NOT NULL,
	`link` varchar(255) NOT NULL,
	`description` text NOT NULL,
	PRIMARY KEY (`id`),
	KEY `order` (`order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
