DROP TABLE IF EXISTS `www_offices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `www_offices` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `city_id` int(11) unsigned NOT NULL default 0,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `site` varchar(255) NOT NULL,
  `addr` text NOT NULL,
  `postaddr` text NOT NULL,
  `phones` text NOT NULL,
  `work_time` text NOT NULL,
  `map` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

