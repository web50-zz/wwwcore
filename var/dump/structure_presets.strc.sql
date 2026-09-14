CREATE TABLE `structure_presets` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `created_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `creator_uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `changed_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `changer_uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `deleted_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleter_uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `title` varchar(150) DEFAULT NULL,
  `preset_data` mediumtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
