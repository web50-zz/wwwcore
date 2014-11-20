-- MySQL dump 10.13  Distrib 5.1.63, for debian-linux-gnu (i486)
--
-- Host: localhost    Database: ulcb_u9_ru
-- ------------------------------------------------------
-- Server version	5.1.63-0+squeeze1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `structure_presets`
--

DROP TABLE IF EXISTS `structure_presets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `structure_presets`
--

LOCK TABLES `structure_presets` WRITE;
/*!40000 ALTER TABLE `structure_presets` DISABLE KEYS */;
INSERT INTO `structure_presets` VALUES (24,'2014-11-20 15:40:00',1,'2014-11-20 15:40:00',1,'0000-00-00 00:00:00',0,'reg','a:6:{i:0;a:13:{s:2:\"id\";s:0:\"\";s:10:\"view_point\";s:1:\"0\";s:5:\"title\";s:12:\"cabinet_auth\";s:7:\"ui_name\";s:7:\"cb_auth\";s:7:\"page_id\";s:0:\"\";s:7:\"ui_call\";s:7:\"checker\";s:12:\"ui_configure\";s:0:\"\";s:5:\"order\";s:1:\"1\";s:13:\"has_structure\";s:1:\"0\";s:9:\"deep_hide\";s:1:\"0\";s:13:\"cache_enabled\";s:1:\"0\";s:13:\"cache_timeout\";s:1:\"0\";s:10:\"human_name\";s:30:\"Cabinet A:  front auth cb_auth\";}i:1;a:13:{s:2:\"id\";s:0:\"\";s:10:\"view_point\";s:1:\"0\";s:5:\"title\";s:9:\"js jQuery\";s:7:\"ui_name\";s:12:\"jquery_1_7_1\";s:7:\"page_id\";s:0:\"\";s:7:\"ui_call\";s:7:\"content\";s:12:\"ui_configure\";s:0:\"\";s:5:\"order\";s:1:\"1\";s:13:\"has_structure\";s:1:\"0\";s:9:\"deep_hide\";s:1:\"0\";s:13:\"cache_enabled\";s:1:\"0\";s:13:\"cache_timeout\";s:1:\"0\";s:10:\"human_name\";s:19:\"JS LIB jquery_1_7_1\";}i:2;a:13:{s:2:\"id\";s:0:\"\";s:10:\"view_point\";s:1:\"0\";s:5:\"title\";s:12:\"js Bootstrap\";s:7:\"ui_name\";s:9:\"bootstrap\";s:7:\"page_id\";s:0:\"\";s:7:\"ui_call\";s:7:\"content\";s:12:\"ui_configure\";s:0:\"\";s:5:\"order\";s:2:\"10\";s:13:\"has_structure\";s:1:\"0\";s:9:\"deep_hide\";s:1:\"0\";s:13:\"cache_enabled\";s:1:\"0\";s:13:\"cache_timeout\";s:1:\"0\";s:10:\"human_name\";s:38:\"JS widget Bootstrap latest metapackage\";}i:3;a:13:{s:2:\"id\";s:0:\"\";s:10:\"view_point\";s:1:\"0\";s:5:\"title\";s:20:\"bootstrap responsive\";s:7:\"ui_name\";s:20:\"bootstrap_responsive\";s:7:\"page_id\";s:0:\"\";s:7:\"ui_call\";s:7:\"content\";s:12:\"ui_configure\";s:0:\"\";s:5:\"order\";s:2:\"20\";s:13:\"has_structure\";s:1:\"0\";s:9:\"deep_hide\";s:1:\"0\";s:13:\"cache_enabled\";s:1:\"0\";s:13:\"cache_timeout\";s:1:\"0\";s:10:\"human_name\";s:54:\"CSS widget Bootstrap Responsive CSS latest metapackage\";}i:4;a:13:{s:2:\"id\";s:0:\"\";s:10:\"view_point\";s:1:\"0\";s:5:\"title\";s:10:\"js pnotify\";s:7:\"ui_name\";s:7:\"pnotify\";s:7:\"page_id\";s:0:\"\";s:7:\"ui_call\";s:7:\"content\";s:12:\"ui_configure\";s:0:\"\";s:5:\"order\";s:2:\"20\";s:13:\"has_structure\";s:1:\"0\";s:9:\"deep_hide\";s:1:\"0\";s:13:\"cache_enabled\";s:1:\"0\";s:13:\"cache_timeout\";s:1:\"0\";s:10:\"human_name\";s:30:\"JS widget Pines Notift pnotify\";}i:5;a:13:{s:2:\"id\";s:0:\"\";s:10:\"view_point\";s:1:\"1\";s:5:\"title\";s:26:\"Cabinet навигация\";s:7:\"ui_name\";s:6:\"cb_nav\";s:7:\"page_id\";s:0:\"\";s:7:\"ui_call\";s:7:\"content\";s:12:\"ui_configure\";s:15:\"{\"parent\":\"20\"}\";s:5:\"order\";s:2:\"10\";s:13:\"has_structure\";s:1:\"0\";s:9:\"deep_hide\";s:1:\"0\";s:13:\"cache_enabled\";s:1:\"0\";s:13:\"cache_timeout\";s:1:\"0\";s:10:\"human_name\";s:36:\"Cabinet A: Топ навигация\";}}');
/*!40000 ALTER TABLE `structure_presets` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-11-20 16:04:58
