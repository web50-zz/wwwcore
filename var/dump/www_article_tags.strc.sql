CREATE TABLE `www_article_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) DEFAULT NULL,
  `item_id` int(10) DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=230 DEFAULT CHARSET=utf8