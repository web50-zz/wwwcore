CREATE TABLE `faq` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `faq_created_datetime` datetime default NULL,
  `faq_changed_datetime` datetime default NULL,
  `faq_deleted_datetime` datetime default NULL,
  `faq_deleter_uid` int(10) NOT NULL default '0',
  `faq_creator_uid` int(10) NOT NULL default '0',
  `faq_changer_uid` int(10) NOT NULL default '0',
  `faq_question` text NOT NULL,
  `faq_answer` text NOT NULL,
  `faq_question_author_name` varchar(60) NOT NULL,
  `faq_question_author_email` varchar(60) default NULL,
  `faq_part_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=utf8