CREATE TABLE `banner_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `width` smallint(5) unsigned NOT NULL,
  `height` smallint(5) unsigned NOT NULL,
  `left` mediumint(8) unsigned NOT NULL,
  `right` mediumint(8) unsigned NOT NULL,
  `level` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `left` (`left`,`right`,`level`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
