SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS `category` (
  `id_category` mediumint(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `group` (
  `id_group` mediumint(8) NOT NULL AUTO_INCREMENT,
  `generation` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `section` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `item` (
  `id_item` mediumint(8) NOT NULL AUTO_INCREMENT,
  `id_category` mediumint(8) NOT NULL DEFAULT '0',
  `body` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id_item`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `online` (
  `id_user` mediumint(8) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `time` int(10) NOT NULL DEFAULT '0',
  UNIQUE KEY `id_user` (`id_user`),
  KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `response` (
  `id_response` mediumint(8) NOT NULL AUTO_INCREMENT,
  `id_user` mediumint(8) NOT NULL DEFAULT '0',
  `id_item` mediumint(8) NOT NULL DEFAULT '0',
  `value` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_response`),
  UNIQUE KEY `id_user` (`id_user`,`id_item`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `user` (
  `id_user` mediumint(8) NOT NULL AUTO_INCREMENT,
  `uid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ssid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `first_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `id_group` mediumint(8) NOT NULL DEFAULT '0',
  `registered` int(10) NOT NULL DEFAULT '0',
  `admin` tinyint(4) NOT NULL DEFAULT '0',
  `login_count` mediumint(8) NOT NULL DEFAULT '0',
  `last_login` int(10) NOT NULL DEFAULT '0',
  `last_password_change` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_user`),
  KEY `registered` (`registered`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `user` (`id_user`, `uid`, `ssid`, `first_name`, `last_name`, `password`, `id_group`, `registered`, `admin`) VALUES
(1, '8a5f064371', 'admin', 'admin', 'admin', '7110eda4d09e062aa5e4a390b0a572ac0d2c0220', 0, 1398802656, 1);
