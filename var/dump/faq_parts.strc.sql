CREATE TABLE `faq_parts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `faqp_created_datetime` datetime default NULL,
  `faqp_changed_datetime` datetime default NULL,
  `faqp_deleted_datetime` datetime default NULL,
  `faqp_deleter_uid` int(10) NOT NULL default '0',
  `faqp_creator_uid` int(10) NOT NULL default '0',
  `faqp_changer_uid` int(10) NOT NULL default '0',
  `faqp_title` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8