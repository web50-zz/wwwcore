CREATE TABLE `www_visitor_indexer` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`visiting_datetime` datetime NOT NULL,
	`main_uri` text NOT NULL,
	`page_uri` text NOT NULL,
	`srch_uri` text NOT NULL,
	`referer` text NOT NULL,
	`user_agent` varchar(255) NOT NULL,
	`visitor_ip` varchar(15) NOT NULL,
	`session_id` varchar(64) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `visiting_datetime` (`visiting_datetime`),
	KEY `visitor_ip` (`visitor_ip`),
	KEY `session_id` (`session_id`),
	KEY `main_uri` (`main_uri`(1024)),
	KEY `page_uri` (`page_uri`(1024)),
	KEY `srch_uri` (`srch_uri`(1024))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
