DROP TABLE IF EXISTS `www_faq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `www_faq` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `created_datetime` datetime NOT NULL COMMENT 'Дата создания',
  `name` varchar(255) NOT NULL COMMENT 'Имя',
  `email` varchar(255) NOT NULL COMMENT 'e-mail',
  `comment` text NOT NULL COMMENT 'Комментарий',
  `left` smallint(5) unsigned NOT NULL,
  `right` smallint(5) unsigned NOT NULL,
  `level` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `email` (`email`),
  KEY `node` (`left`,`right`,`level`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;

