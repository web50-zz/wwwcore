CREATE TABLE `structure_presets` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `created_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `creator_uid` mediumint(8) unsigned NOT NULL default '0',
  `changed_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `changer_uid` mediumint(8) unsigned NOT NULL default '0',
  `deleted_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `deleter_uid` mediumint(8) unsigned NOT NULL default '0',
  `title` varchar(150) default NULL,
  `preset_data` mediumtext,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8