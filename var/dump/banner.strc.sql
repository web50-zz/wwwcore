CREATE TABLE `banner` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `banner_group_id` mediumint(8) unsigned NOT NULL,
  `type` tinyint(1) unsigned NOT NULL,
  `link` varchar(255) NOT NULL,
  `target` varchar(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `real_name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `banner_group_id` (`banner_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
