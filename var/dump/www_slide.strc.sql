CREATE TABLE `www_slide` (
	`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
	`slide_group_id` mediumint(8) unsigned NOT NULL,
	`order` mediumint(8) unsigned NOT NULL,
	`type` tinyint(1) unsigned NOT NULL,
	`link` varchar(255) NOT NULL,
	`target` varchar(10) NOT NULL,
	`title` varchar(255) NOT NULL,
	`comment` text NOT NULL,
	`real_name` varchar(64) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `slide_group_id` (`slide_group_id`),
	KEY `order` (`order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
