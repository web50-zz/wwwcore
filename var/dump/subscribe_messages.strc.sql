CREATE TABLE `subscribe_messages` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `subscr_created_datetime` datetime default NULL,
  `subscr_changed_datetime` datetime default NULL,
  `subscr_deleted_datetime` datetime default NULL,
  `subscr_deleter_uid` int(10) NOT NULL default '0',
  `subscr_creator_uid` int(10) NOT NULL default '0',
  `subscr_changer_uid` int(10) NOT NULL default '0',
  `subscr_title` varchar(255) NOT NULL,
  `subscr_message_body` text,
  `subscr_id` int(10) NOT NULL default '0',
  `subscr_sended_flag` tinyint(1) unsigned default '0',
  `subscr_sended_datetime` datetime default NULL,
  `subscr_sheduled_to_send` tinyint(1) unsigned default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=52 DEFAULT CHARSET=utf8