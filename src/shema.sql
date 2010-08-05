SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

DROP SCHEMA IF EXISTS `radio` ;
CREATE SCHEMA IF NOT EXISTS `radio` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
SHOW WARNINGS;
USE `radio` ;

-- -----------------------------------------------------
-- Table `radio`.`streamer`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `radio`.`streamer` ;

SHOW WARNINGS;
CREATE  TABLE IF NOT EXISTS `radio`.`streamer` (
  `streamer` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `session` VARCHAR(32) NULL ,
  `username` VARCHAR(50) NULL ,
  `password` CHAR(32) NULL ,
  `status` ENUM('NOT_CONNECTED','CONNECTED','STREAMING','LOGGED_IN') NULL DEFAULT 'NOT_CONNECTED' ,
  `streampassword` VARCHAR(50) NULL ,
  PRIMARY KEY (`streamer`) ,
  UNIQUE INDEX `session_UNIQUE` (`session` ASC) ,
  UNIQUE INDEX `username_UNIQUE` (`username` ASC) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `radio`.`debuglog`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `radio`.`debuglog` ;

SHOW WARNINGS;
CREATE  TABLE IF NOT EXISTS `radio`.`debuglog` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `time` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  `text` TEXT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `radio`.`shows`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `radio`.`shows` ;

SHOW WARNINGS;
CREATE  TABLE IF NOT EXISTS `radio`.`shows` (
  `show` INT(11) UNSIGNED NOT NULL ,
  `streamer` INT(11) UNSIGNED NOT NULL ,
  `type` ENUM('PLANNED','UNPLANNED') NULL ,
  `begin` TIMESTAMP NOT NULL ,
  `end` TIMESTAMP NULL ,
  `description` TEXT NULL ,
  `name` VARCHAR(100) NULL ,
  PRIMARY KEY (`show`) ,
  INDEX `fk_shows_streamer` (`streamer` ASC) ,
  CONSTRAINT `fk_shows_streamer`
    FOREIGN KEY (`streamer` )
    REFERENCES `radio`.`streamer` (`streamer` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `radio`.`songhistory`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `radio`.`songhistory` ;

SHOW WARNINGS;
CREATE  TABLE IF NOT EXISTS `radio`.`songhistory` (
  `song` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `show` INT(11) UNSIGNED NOT NULL ,
  `artist` VARCHAR(100) NULL ,
  `title` VARCHAR(100) NULL ,
  `begin` TIMESTAMP NULL ,
  `end` TIMESTAMP NULL ,
  PRIMARY KEY (`song`) ,
  INDEX `fk_songhistory_shows` (`show` ASC) ,
  CONSTRAINT `fk_songhistory_shows`
    FOREIGN KEY (`show` )
    REFERENCES `radio`.`shows` (`show` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `radio`.`mounts`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `radio`.`mounts` ;

SHOW WARNINGS;
CREATE  TABLE IF NOT EXISTS `radio`.`mounts` (
  `mount` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `path` VARCHAR(100) NULL ,
  `description` VARCHAR(100) NULL ,
  `name` VARCHAR(45) NULL ,
  `type` ENUM('LAME','OGG','AACP') NOT NULL DEFAULT 'OGG' ,
  `quality` INT(11)  NULL ,
  `username` VARCHAR(45) NULL ,
  `password` VARCHAR(45) NULL ,
  PRIMARY KEY (`mount`) ,
  UNIQUE INDEX `path_UNIQUE` (`path` ASC) )
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `radio`.`listenerhistory`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `radio`.`listenerhistory` ;

SHOW WARNINGS;
CREATE  TABLE IF NOT EXISTS `radio`.`listenerhistory` (
  `listenerhistory` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `mount` INT(11) UNSIGNED NOT NULL ,
  `connected` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `disconnected` TIMESTAMP NULL ,
  `ip` INT(11) UNSIGNED NULL ,
  `useragent` TEXT NULL ,
  `client` INT(11) UNSIGNED NOT NULL ,
  PRIMARY KEY (`listenerhistory`) ,
  INDEX `fk_listenerhistory_mounts1` (`mount` ASC) ,
  INDEX `client` (`client` ASC) ,
  CONSTRAINT `fk_listenerhistory_mounts1`
    FOREIGN KEY (`mount` )
    REFERENCES `radio`.`mounts` (`mount` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `radio`.`streamersettings`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `radio`.`streamersettings` ;

SHOW WARNINGS;
CREATE  TABLE IF NOT EXISTS `radio`.`streamersettings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `streamer` INT(11) UNSIGNED NOT NULL ,
  `key` VARCHAR(45) NULL ,
  `value` VARCHAR(200) NULL ,
  INDEX `fk_streamersettings_streamer1` (`streamer` ASC) ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `key` (`streamer` ASC, `key` ASC) ,
  CONSTRAINT `fk_streamersettings_streamer1`
    FOREIGN KEY (`streamer` )
    REFERENCES `radio`.`streamer` (`streamer` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

SHOW WARNINGS;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
