CREATE TABLE `www_article_comment` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `created_datetime` datetime NOT NULL,
  `published` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `article_id` mediumint(8) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `public` (`published`),
  KEY `article_id` (`article_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8