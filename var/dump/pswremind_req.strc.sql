CREATE TABLE `pswremind_req` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `hash` varchar(255) default NULL,
  `req` text,
  `req_datetime` datetime default NULL,
  `req_ip` varchar(15) default NULL,
  `done` tinyint(1) unsigned NOT NULL default '0',
  `done_datetime` datetime default NULL,
  `done_ip` varchar(15) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8