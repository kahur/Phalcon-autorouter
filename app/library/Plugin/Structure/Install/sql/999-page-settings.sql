CREATE TABLE IF NOT EXISTS `page_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX (`page_id`),
  CONSTRAINT `page_fk`  
  FOREIGN KEY (`page_id`) 
        REFERENCES `page`(`id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
) ENGINE=INNODB  DEFAULT CHARSET=UTF8 AUTO_INCREMENT=1;

