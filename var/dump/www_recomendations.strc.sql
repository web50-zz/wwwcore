--
-- Table structure for table `www_recomendations`
--

DROP TABLE IF EXISTS `www_recomendations`;
CREATE TABLE `www_recomendations` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `order` smallint(5) unsigned NOT NULL,
  `client_name` varchar(128) NOT NULL,
  `real_name` varchar(64) NOT NULL,
  `description` text NOT NULL,
  `client_id` int(11) unsigned NOT NULL DEFAULT '0',
  `person` varchar(255) NOT NULL,
  `position` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order` (`order`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
