CREATE TABLE `guestbook` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `gb_created_datetime` datetime default NULL,
  `gb_changed_datetime` datetime default NULL,
  `gb_deleted_datetime` datetime default NULL,
  `gb_deleter_uid` int(10) NOT NULL default '0',
  `gb_creator_uid` int(10) NOT NULL default '0',
  `gb_changer_uid` int(10) NOT NULL default '0',
  `gb_record` text NOT NULL,
  `gb_author_name` varchar(60) NOT NULL,
  `gb_author_email` varchar(60) default NULL,
  `gb_answer` text,
  `gb_author_location` varchar(80) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=99 DEFAULT CHARSET=utf8