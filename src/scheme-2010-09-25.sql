-- phpMyAdmin SQL Dump
-- version 2.11.8.1deb5+lenny6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 25. September 2010 um 14:06
-- Server Version: 5.0.51
-- PHP-Version: 5.2.6-1+lenny9
--
-- Schema 25.09.2010
--

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `radio`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `apikeys`
--

CREATE TABLE IF NOT EXISTS `apikeys` (
  `apikey` int(11) unsigned NOT NULL auto_increment,
  `key` varchar(45) default NULL,
  `flag` int(10) unsigned default NULL,
  `streamer` int(11) unsigned default NULL,
  `counter` int(10) unsigned NOT NULL,
  `lastaccessed` datetime default NULL,
  `application` varchar(100) default NULL,
  PRIMARY KEY  (`apikey`),
  UNIQUE KEY `key` (`key`),
  KEY `fk_apikeys_streamer` (`streamer`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `debuglog`
--

CREATE TABLE IF NOT EXISTS `debuglog` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `time` timestamp NULL default CURRENT_TIMESTAMP,
  `text` text,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2408 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `listenerhistory`
--

CREATE TABLE IF NOT EXISTS `listenerhistory` (
  `listenerhistory` int(11) unsigned NOT NULL auto_increment,
  `mount` int(11) unsigned NOT NULL,
  `connected` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `disconnected` timestamp NULL default NULL,
  `ip` int(11) unsigned default NULL,
  `useragent` text,
  `client` int(11) unsigned NOT NULL,
  `country` char(3) default NULL,
  `city` varchar(150) default NULL,
  PRIMARY KEY  (`listenerhistory`),
  KEY `fk_listenerhistory_mounts1` (`mount`),
  KEY `client` (`client`),
  KEY `connected` (`connected`,`disconnected`),
  KEY `connected_2` (`disconnected`,`connected`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15210 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `locales`
--

CREATE TABLE IF NOT EXISTS `locales` (
  `locale` int(11) unsigned NOT NULL auto_increment,
  `timezone` varchar(45) NOT NULL,
  `language` char(3) NOT NULL,
  `country` char(15) NOT NULL,
  `name` varchar(50) NOT NULL,
  `nativename` varchar(50) NOT NULL,
  PRIMARY KEY  (`locale`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `mounts`
--

CREATE TABLE IF NOT EXISTS `mounts` (
  `mount` int(11) unsigned NOT NULL auto_increment,
  `path` varchar(100) default NULL,
  `description` varchar(100) default NULL,
  `name` varchar(45) default NULL,
  `type` enum('LAME','OGG','AACP') NOT NULL default 'OGG',
  `quality` int(11) default NULL,
  `username` varchar(45) default NULL,
  `password` varchar(45) default NULL,
  PRIMARY KEY  (`mount`),
  UNIQUE KEY `path_UNIQUE` (`path`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `news`
--

CREATE TABLE IF NOT EXISTS `news` (
  `news` int(11) unsigned NOT NULL auto_increment,
  `streamer` int(11) unsigned NOT NULL,
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `description` varchar(150) NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY  (`news`),
  KEY `fk_news_streamer1` (`streamer`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `playlist`
--

CREATE TABLE IF NOT EXISTS `playlist` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `path` varchar(100) default NULL,
  `name` varchar(45) default NULL,
  `from` time NOT NULL,
  `to` time NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `shows`
--

CREATE TABLE IF NOT EXISTS `shows` (
  `show` int(11) unsigned NOT NULL auto_increment,
  `streamer` int(11) unsigned NOT NULL,
  `type` enum('PLANNED','UNPLANNED') default NULL,
  `begin` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `end` timestamp NULL default NULL,
  `description` text,
  `name` varchar(100) default NULL,
  PRIMARY KEY  (`show`),
  KEY `fk_shows_streamer` (`streamer`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=638 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `songhistory`
--

CREATE TABLE IF NOT EXISTS `songhistory` (
  `song` int(11) unsigned NOT NULL auto_increment,
  `show` int(11) unsigned NOT NULL,
  `artist` varchar(100) default NULL,
  `title` varchar(100) default NULL,
  `begin` timestamp NULL default NULL,
  `end` timestamp NULL default NULL,
  PRIMARY KEY  (`song`),
  KEY `fk_songhistory_shows` (`show`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6194 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `streamer`
--

CREATE TABLE IF NOT EXISTS `streamer` (
  `streamer` int(11) unsigned NOT NULL auto_increment,
  `session` varchar(32) default NULL,
  `username` varchar(50) default NULL,
  `password` char(40) default NULL,
  `country` char(3) NOT NULL,
  `status` enum('NOT_CONNECTED','CONNECTED','STREAMING','LOGGED_IN') default 'NOT_CONNECTED',
  `streampassword` varchar(50) default NULL,
  `ban` timestamp NULL default NULL,
  PRIMARY KEY  (`streamer`),
  UNIQUE KEY `session_UNIQUE` (`session`),
  UNIQUE KEY `username_UNIQUE` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=142 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `streamersettings`
--

CREATE TABLE IF NOT EXISTS `streamersettings` (
  `id` int(11) NOT NULL auto_increment,
  `streamer` int(11) unsigned NOT NULL,
  `key` varchar(45) default NULL,
  `value` varchar(200) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `key` (`streamer`,`key`),
  KEY `fk_streamersettings_streamer1` (`streamer`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=127 ;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `apikeys`
--
ALTER TABLE `apikeys`
  ADD CONSTRAINT `fk_apikeys_streamer1` FOREIGN KEY (`streamer`) REFERENCES `streamer` (`streamer`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `listenerhistory`
--
ALTER TABLE `listenerhistory`
  ADD CONSTRAINT `fk_listenerhistory_mounts1` FOREIGN KEY (`mount`) REFERENCES `mounts` (`mount`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `fk_news_streamer1` FOREIGN KEY (`streamer`) REFERENCES `streamer` (`streamer`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `shows`
--
ALTER TABLE `shows`
  ADD CONSTRAINT `fk_shows_streamer` FOREIGN KEY (`streamer`) REFERENCES `streamer` (`streamer`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `songhistory`
--
ALTER TABLE `songhistory`
  ADD CONSTRAINT `fk_songhistory_shows` FOREIGN KEY (`show`) REFERENCES `shows` (`show`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `streamersettings`
--
ALTER TABLE `streamersettings`
  ADD CONSTRAINT `fk_streamersettings_streamer1` FOREIGN KEY (`streamer`) REFERENCES `streamer` (`streamer`) ON DELETE CASCADE ON UPDATE CASCADE;
