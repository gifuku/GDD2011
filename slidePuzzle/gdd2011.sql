
CREATE TABLE IF NOT EXISTS `tpuzzle` (
  `pzlId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pzlRootId` int(10) unsigned NOT NULL,
  `pzlParentId` int(10) unsigned NOT NULL DEFAULT '0',
  `pzlAction` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `pzlWidth` int(1) NOT NULL,
  `pzlHeight` int(1) NOT NULL,
  `pzlWalls` int(11) NOT NULL,
  `pzlCurrent` char(36) COLLATE utf8_unicode_ci NOT NULL,
  `pzlAnswer` char(36) COLLATE utf8_unicode_ci NOT NULL,
  `pzlProcess` char(100) COLLATE utf8_unicode_ci NOT NULL,
  `pzlProcessDate` int(11) NOT NULL,
  PRIMARY KEY (`pzlId`),
  KEY `pzlRootId` (`pzlRootId`,`pzlParentId`,`pzlCurrent`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5001 ;
