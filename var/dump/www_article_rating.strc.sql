CREATE TABLE `www_article_rating` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `action_datetime` datetime NOT NULL,
  `action` tinyint(1) unsigned NOT NULL,
  `article_id` mediumint(8) unsigned NOT NULL,
  `visitor_ip` varchar(15) NOT NULL,
  `session_id` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `action_datetime` (`action_datetime`),
  KEY `action` (`action`),
  KEY `article_id` (`article_id`),
  KEY `visitor_ip` (`visitor_ip`),
  KEY `session_id` (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8