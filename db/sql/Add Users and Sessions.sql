
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema paypage
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `paypage` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci ;
USE `paypage` ;

-- -----------------------------------------------------
-- Table `paypage`.`users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `paypage`.`users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `firstName` VARCHAR(255) NOT NULL,
  `lastName` VARCHAR(255) NOT NULL,
  `userName` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `userActive` VARCHAR(1) NOT NULL DEFAULT 'N',
  `loginAttempts` INT NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  UNIQUE INDEX `username_UNIQUE` (`userName` ASC))
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `paypage`.`sessions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `paypage`.`sessions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `userId` INT NOT NULL,
  `accessToken` VARCHAR(100) NOT NULL,
  `accessTokenExpiry` DATETIME NOT NULL,
  `refreshToken` VARCHAR(100) NOT NULL,
  `refreshTokenExpiry` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `accessToken` (`accessToken` ASC),
  INDEX `refreshToken` (`refreshToken` ASC),
  INDEX `sessionuserid_fk_idx` (`userId` ASC),
  CONSTRAINT `sessionuserid_fk`
    FOREIGN KEY (`userId`)
    REFERENCES `paypage`.`users` (`id`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
