CREATE TABLE `search` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `exists` tinyint(1) unsigned NOT NULL,
  `created_datetime` datetime NOT NULL,
  `uri` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `update` (`exists`,`uri`),
  FULLTEXT KEY `content` (`content`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8