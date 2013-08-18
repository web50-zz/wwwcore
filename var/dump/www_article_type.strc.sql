CREATE TABLE `www_article_type` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `hidden` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'скрыть',
  `title` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(16) NOT NULL DEFAULT '',
  `uri` varchar(255) NOT NULL DEFAULT '',
  `left` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `right` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `level` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `link_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `site_part_id` (`left`,`right`,`level`),
  KEY `uri` (`uri`)
) ENGINE=MyISAM AUTO_INCREMENT=1527 DEFAULT CHARSET=utf8