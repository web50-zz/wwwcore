CREATE TABLE `www_article_rating` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`action_datetime` DATETIME NOT NULL,
	`action` TINYINT(1) UNSIGNED NOT NULL,
	`article_id` MEDIUMINT(8) UNSIGNED NOT NULL,
	`visitor_ip` VARCHAR(15) NOT NULL,
	`session_id` VARCHAR(64) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `action_datetime` (`action_datetime`),
	KEY `action` (`action`),
	KEY `article_id` (`article_id`),
	KEY `visitor_ip` (`visitor_ip`),
	KEY `session_id` (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
