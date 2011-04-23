CREATE TABLE `ui_view_point` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `page_id` smallint(5) unsigned NOT NULL,
  `order` tinyint(3) unsigned NOT NULL,
  `deep_hide` tinyint(1) unsigned NOT NULL,
  `has_structure` tinyint(1) unsigned NOT NULL,
  `view_point` tinyint(3) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `ui_name` varchar(255) NOT NULL,
  `ui_call` varchar(255) NOT NULL,
  `ui_configure` text NOT NULL,
  `cache_enabled` tinyint(1) unsigned NOT NULL,
  `cache_timeout` varchar(6) default NULL,
  PRIMARY KEY  (`id`),
  KEY `page_id` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=409 DEFAULT CHARSET=utf8