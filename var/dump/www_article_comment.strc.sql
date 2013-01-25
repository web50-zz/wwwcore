CREATE TABLE `www_article_comment` (
	`id` MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	`created_datetime` DATETIME NOT NULL,
	`public` TINYINT(1) UNSIGNED NOT NULL,
	`article_id` MEDIUMINT(8) UNSIGNED NOT NULL,
	`name` VARCHAR(255) NOT NULL,
	`comment` TEXT NOT NULL,
	PRIMARY KEY (`id`),
	KEY `public` (`public`),
	KEY `article_id` (`article_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
