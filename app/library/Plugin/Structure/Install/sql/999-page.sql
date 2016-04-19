CREATE TABLE IF NOT EXISTS `page` (
`id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `layout` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_public` int(11) DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `keywords` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
ALTER TABLE `page`
 ADD PRIMARY KEY (`id`), ADD KEY `url` (`url`), ADD KEY `parent_id` (`parent_id`);

ALTER TABLE `page`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=9;