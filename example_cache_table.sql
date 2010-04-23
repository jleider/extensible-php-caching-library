
CREATE TABLE IF NOT EXISTS `cache` (
  `column1` int(11) NOT NULL,
  `column2` int(11) NOT NULL,
  `column3` int(11) NOT NULL,
  `data` mediumblob NOT NULL,
  `expiration` int(11) NOT NULL,
  UNIQUE KEY `key1` (`column1`,`column2`,`column3`)
) ENGINE=MyISAM;
