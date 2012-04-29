-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.5.20-log - MySQL Community Server (GPL)
-- Server OS:                    Win32
-- HeidiSQL version:             7.0.0.4053
-- Date/time:                    2012-04-10 19:21:48
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET FOREIGN_KEY_CHECKS=0 */;

-- Dumping structure for table corale_cz.corale_attendance
DROP TABLE IF EXISTS `corale_attendance`;
CREATE TABLE IF NOT EXISTS `corale_attendance` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(10) unsigned NOT NULL,
  `member_id` int(10) unsigned NOT NULL,
  `attend` tinyint(1) DEFAULT NULL,
  `note` tinytext COLLATE utf8_czech_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_id_member_id` (`event_id`,`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- Data exporting was unselected.


-- Dumping structure for table corale_cz.corale_event
DROP TABLE IF EXISTS `corale_event`;
CREATE TABLE IF NOT EXISTS `corale_event` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT 'other',
  `date_start` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  `place` tinytext COLLATE utf8_czech_ci,
  `description` text COLLATE utf8_czech_ci,
  `hidden` tinyint(1) NOT NULL DEFAULT '1',
  `attendance_active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- Data exporting was unselected.


-- Dumping structure for table corale_cz.corale_member
DROP TABLE IF EXISTS `corale_member`;
CREATE TABLE IF NOT EXISTS `corale_member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `guest` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `voice_type` varchar(20) COLLATE utf8_czech_ci DEFAULT NULL,
  `instruments` tinytext COLLATE utf8_czech_ci,
  `email` tinytext COLLATE utf8_czech_ci,
  `phone` varchar(13) COLLATE utf8_czech_ci DEFAULT NULL,
  `description` text COLLATE utf8_czech_ci,
  PRIMARY KEY (`id`),
  KEY `guest_active` (`guest`,`active`),
  KEY `voice_type` (`voice_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- Data exporting was unselected.


-- Dumping structure for table corale_cz.corale_user_binding
DROP TABLE IF EXISTS `corale_user_binding`;
CREATE TABLE IF NOT EXISTS `corale_user_binding` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL,
  `wp_user_id` bigint(20) unsigned DEFAULT NULL COMMENT 'wordpress',
  `wiki_user_id` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'dokuwiki',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- Data exporting was unselected.
/*!40014 SET FOREIGN_KEY_CHECKS=1 */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
