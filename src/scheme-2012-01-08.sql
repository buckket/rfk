-- database scheme for RfK
-- version 08.01.2012
-- created manually from individual phpMyadmin table scheme dumps
--
-- don't forget to import locales-2012-01-08.sql

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

/*!40101 SET NAMES binary*/;
/*!40014 SET FOREIGN_KEY_CHECKS=0*/;

CREATE TABLE IF NOT EXISTS `apikeys` (
  `apikey` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(45) DEFAULT NULL,
  `flag` int(10) unsigned DEFAULT NULL,
  `streamer` int(11) unsigned DEFAULT NULL,
  `counter` int(10) unsigned NOT NULL,
  `lastaccessed` datetime DEFAULT NULL,
  `application` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`apikey`),
  UNIQUE KEY `key` (`key`),
  KEY `fk_apikeys_streamer` (`streamer`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COMMENT='InnoDB free: 9216 kB; (`streamer`) REFER `radio/streamer`(`s';

/*!40101 SET NAMES binary*/;
/*!40014 SET FOREIGN_KEY_CHECKS=0*/;

CREATE TABLE IF NOT EXISTS `artists` (
  `artist` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sameAs` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `flag` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`artist`),
  UNIQUE KEY `name_UNIQUE` (`name`),
  KEY `fk_artists_artists1` (`sameAs`),
  CONSTRAINT `fk_artists_artists1` FOREIGN KEY (`sameAs`) REFERENCES `artists` (`artist`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10925 DEFAULT CHARSET=utf8;

/*!40101 SET NAMES binary*/;
/*!40014 SET FOREIGN_KEY_CHECKS=0*/;

CREATE TABLE IF NOT EXISTS `debuglog` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `text` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1759 DEFAULT CHARSET=utf8;


/*!40101 SET NAMES binary*/;
/*!40014 SET FOREIGN_KEY_CHECKS=0*/;

CREATE TABLE IF NOT EXISTS `donations` (
  `donation` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `time` datetime NOT NULL,
  `inValue` varchar(10) NOT NULL,
  `inCurrency` varchar(5) NOT NULL,
  `outValue` varchar(10) NOT NULL,
  `outCurrency` varchar(5) NOT NULL,
  `method` varchar(50) NOT NULL,
  `country` varchar(3) NOT NULL,
  PRIMARY KEY (`donation`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;


/*!40101 SET NAMES binary*/;
/*!40014 SET FOREIGN_KEY_CHECKS=0*/;

CREATE TABLE IF NOT EXISTS `listenerhistory` (
  `listenerhistory` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mount` int(11) unsigned NOT NULL,
  `connected` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `disconnected` timestamp NULL DEFAULT NULL,
  `ip` int(11) unsigned DEFAULT NULL,
  `useragent` text,
  `client` int(11) unsigned NOT NULL,
  `country` char(3) DEFAULT NULL,
  `city` varchar(150) DEFAULT NULL,
  `relay` int(10) unsigned NOT NULL,
  PRIMARY KEY (`listenerhistory`),
  KEY `fk_listenerhistory_mounts1` (`mount`),
  KEY `client` (`client`),
  KEY `connected` (`connected`,`disconnected`),
  KEY `connected_2` (`disconnected`,`connected`),
  KEY `fk_listenerhistory_relays1` (`relay`)
) ENGINE=InnoDB AUTO_INCREMENT=232713 DEFAULT CHARSET=utf8;


/*!40101 SET NAMES binary*/;
/*!40014 SET FOREIGN_KEY_CHECKS=0*/;

CREATE TABLE IF NOT EXISTS `locales` (
  `locale` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `timezone` varchar(45) NOT NULL,
  `language` char(3) NOT NULL,
  `country` char(15) NOT NULL,
  `name` varchar(50) NOT NULL,
  `nativename` varchar(50) NOT NULL,
  PRIMARY KEY (`locale`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;


/*!40101 SET NAMES binary*/;
/*!40014 SET FOREIGN_KEY_CHECKS=0*/;

CREATE TABLE IF NOT EXISTS `mount_relay` (
  `mount_relay` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mount` int(11) unsigned NOT NULL,
  `maxlistener` int(10) unsigned DEFAULT NULL,
  `relay` int(10) unsigned NOT NULL,
  `status` enum('OFFLINE','ONLINE','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  PRIMARY KEY (`mount_relay`),
  KEY `fk_mount_relay_mounts` (`mount`),
  KEY `fk_mount_relay_relays1` (`relay`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;


/*!40101 SET NAMES binary*/;
/*!40014 SET FOREIGN_KEY_CHECKS=0*/;

CREATE TABLE IF NOT EXISTS `mounts` (
  `mount` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `path` varchar(100) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  `name` varchar(45) DEFAULT NULL,
  `type` enum('LAME','LAMEVBR','OGG','AACP') NOT NULL DEFAULT 'OGG',
  `quality` int(11) DEFAULT NULL,
  `username` varchar(45) DEFAULT NULL,
  `password` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`mount`),
  UNIQUE KEY `path_UNIQUE` (`path`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;


/*!40101 SET NAMES binary*/;
/*!40014 SET FOREIGN_KEY_CHECKS=0*/;

CREATE TABLE IF NOT EXISTS `news` (
  `news` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `streamer` int(11) unsigned NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` varchar(150) NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`news`),
  KEY `fk_news_streamer1` (`streamer`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;


/*!40101 SET NAMES binary*/;
/*!40014 SET FOREIGN_KEY_CHECKS=0*/;

CREATE TABLE IF NOT EXISTS `playlist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `path` varchar(100) DEFAULT NULL,
  `name` varchar(45) DEFAULT NULL,
  `from` time NOT NULL,
  `to` time NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;


/*!40101 SET NAMES binary*/;
/*!40014 SET FOREIGN_KEY_CHECKS=0*/;

CREATE TABLE IF NOT EXISTS `recordings` (
  `recording` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `show` int(11) unsigned NOT NULL,
  `filename` varchar(45) DEFAULT NULL,
  `status` enum('RECORDING','FINISHED','FAILED') DEFAULT NULL,
  PRIMARY KEY (`recording`),
  KEY `fk_recordings_shows1` (`show`),
  CONSTRAINT `fk_recordings_shows1` FOREIGN KEY (`show`) REFERENCES `shows` (`show`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;


/*!40101 SET NAMES binary*/;
/*!40014 SET FOREIGN_KEY_CHECKS=0*/;

CREATE TABLE IF NOT EXISTS `relays` (
  `relay` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('MASTER','RELAY') NOT NULL,
  `hostname` varchar(50) DEFAULT NULL,
  `port` int(11) NOT NULL DEFAULT '8000',
  `status` enum('OFFLINE','ONLINE','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
  `bandwidth` int(10) unsigned DEFAULT NULL,
  `tx` int(10) unsigned DEFAULT NULL,
  `query_method` enum('NO_QUERY','LOCAL_VNSTAT','REMOTE_ICECAST2_KH') NOT NULL,
  `query_user` varchar(40) DEFAULT NULL,
  `query_pass` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`relay`),
  UNIQUE KEY `hostname_UNIQUE` (`hostname`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;


/*!40101 SET NAMES binary*/;
/*!40014 SET FOREIGN_KEY_CHECKS=0*/;

CREATE TABLE IF NOT EXISTS `shows` (
  `show` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `streamer` int(11) unsigned NOT NULL,
  `type` enum('PLANNED','UNPLANNED') DEFAULT NULL,
  `begin` timestamp NULL DEFAULT NULL,
  `end` timestamp NULL DEFAULT NULL,
  `description` text,
  `name` varchar(100) DEFAULT NULL,
  `thread` int(11) DEFAULT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'time this entry was last touched',
  `flag` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`show`),
  KEY `fk_shows_streamer` (`streamer`),
  KEY `begin` (`begin`),
  KEY `begin_2` (`begin`,`end`)
) ENGINE=InnoDB AUTO_INCREMENT=6639 DEFAULT CHARSET=utf8;


/*!40101 SET NAMES binary*/;
/*!40014 SET FOREIGN_KEY_CHECKS=0*/;

CREATE TABLE IF NOT EXISTS `songhistory` (
  `song` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `show` int(11) unsigned NOT NULL,
  `artist` varchar(100) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `begin` timestamp NULL DEFAULT NULL,
  `end` timestamp NULL DEFAULT NULL,
  `titleid` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`song`),
  KEY `fk_songhistory_shows` (`show`),
  KEY `fk_songhistory_titles` (`titleid`),
  CONSTRAINT `fk_songhistory_titles` FOREIGN KEY (`titleid`) REFERENCES `titles` (`title`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=80527 DEFAULT CHARSET=utf8;


/*!40101 SET NAMES binary*/;
/*!40014 SET FOREIGN_KEY_CHECKS=0*/;

CREATE TABLE IF NOT EXISTS `streamer` (
  `streamer` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `session` varchar(32) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` char(40) DEFAULT NULL,
  `uilanguage` char(3) DEFAULT NULL,
  `country` char(3) NOT NULL,
  `status` enum('NOT_CONNECTED','CONNECTED','STREAMING','LOGGED_IN') DEFAULT 'NOT_CONNECTED',
  `streampassword` varchar(50) DEFAULT NULL,
  `ban` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`streamer`),
  UNIQUE KEY `session_UNIQUE` (`session`),
  UNIQUE KEY `username_UNIQUE` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=625 DEFAULT CHARSET=utf8;


/*!40101 SET NAMES binary*/;
/*!40014 SET FOREIGN_KEY_CHECKS=0*/;

CREATE TABLE IF NOT EXISTS `streamersettings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `streamer` int(11) unsigned NOT NULL,
  `key` varchar(45) DEFAULT NULL,
  `value` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`streamer`,`key`),
  KEY `fk_streamersettings_streamer1` (`streamer`)
) ENGINE=InnoDB AUTO_INCREMENT=2172 DEFAULT CHARSET=utf8 COMMENT='InnoDB free: 9216 kB; (`streamer`) REFER `radio/streamer`(`s';


/*!40101 SET NAMES binary*/;
/*!40014 SET FOREIGN_KEY_CHECKS=0*/;

CREATE TABLE IF NOT EXISTS `titles` (
  `title` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sameAs` int(10) unsigned DEFAULT NULL,
  `artist` int(10) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `length` int(10) unsigned DEFAULT NULL,
  `lengthweight` int(10) unsigned DEFAULT NULL,
  `flag` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`title`),
  UNIQUE KEY `artist` (`name`,`artist`),
  KEY `fk_title_artists` (`artist`),
  KEY `fk_titles_titles1` (`sameAs`),
  CONSTRAINT `fk_titles_titles1` FOREIGN KEY (`sameAs`) REFERENCES `titles` (`title`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_title_artists` FOREIGN KEY (`artist`) REFERENCES `artists` (`artist`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29131 DEFAULT CHARSET=utf8;


