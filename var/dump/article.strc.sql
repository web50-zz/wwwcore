CREATE TABLE `article` (
`id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
`release_date` date NOT NULL DEFAULT '0000-00-00',
`title` varchar(255) NOT NULL DEFAULT '',
`author` varchar(255) NOT NULL DEFAULT '',
`source` varchar(255) NOT NULL,
`content` text NOT NULL,
`category` int(10) unsigned NOT NULL DEFAULT '0',
`image` varchar(255) NOT NULL DEFAULT '',
`uri` varchar(255) NOT NULL DEFAULT '',
PRIMARY KEY (`id`,`image`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8
