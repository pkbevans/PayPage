-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema bondevans_com
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema bondevans_com
-- -----------------------------------------------------
USE `bondevans_com` ;

-- -----------------------------------------------------
-- Table `bondevans_com`.`orders`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `bondevans_com`.`payments` ;
DROP TABLE IF EXISTS `bondevans_com`.`orders` ;

CREATE TABLE IF NOT EXISTS `bondevans_com`.`orders` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `merchantReference` VARCHAR(50) NOT NULL,
  `amount` FLOAT NOT NULL DEFAULT 0,
  `refundAmount` FLOAT NOT NULL DEFAULT 0,
  `currency` VARCHAR(3) NOT NULL,
  `customerId` VARCHAR(32) NOT NULL,
  `customerEmail` VARCHAR(45) NOT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'NEW',
  `datetime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `customerUserId` INT NOT NULL,
  `orderDetails` MEDIUMTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  INDEX `MRN` (`merchantReference` ASC))
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `bondevans_com`.`payments`
-- -----------------------------------------------------

CREATE TABLE IF NOT EXISTS `bondevans_com`.`payments` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `orderId` INT NOT NULL,
  `type` VARCHAR(20) NOT NULL DEFAULT 'PAYMENT',
  `amount` FLOAT NOT NULL DEFAULT 0,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'GBP',
  `cardNumber` VARCHAR(19) NOT NULL,
  `cardType` VARCHAR(20) NOT NULL,
  `authCode` VARCHAR(45) NOT NULL,
  `gatewayRequestId` VARCHAR(45) NOT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'NEW',
  `captured` INT NOT NULL DEFAULT 0,
  `datetime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  INDEX `ORDER` (`orderId` ASC),
  CONSTRAINT `PAYMENTS_ORDER`
    FOREIGN KEY (`orderId`)
    REFERENCES `bondevans_com`.`orders` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `bondevans_com`.`users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `firstName` VARCHAR(255) NOT NULL,
  `lastName` VARCHAR(255) NOT NULL,
  `userName` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `userActive` VARCHAR(1) NOT NULL DEFAULT 'N',
  `loginAttempts` INT NOT NULL DEFAULT '0',
  `customerId` VARCHAR(45) NOT NULL,
  `type` VARCHAR(45) NOT NULL COMMENT 'CUSTOMER/INTERNAL',
  `admin` VARCHAR(1) NOT NULL DEFAULT 'N',
  `verificationCode` VARCHAR(256) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  UNIQUE INDEX `username_UNIQUE` (`userName` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 19
DEFAULT CHARACTER SET = utf8mb3
COMMENT = '	';


-- -----------------------------------------------------
-- Table `bondevans_com`.`sessions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bondevans_com`.`sessions` (
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
    REFERENCES `bondevans_com`.`users` (`id`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB
AUTO_INCREMENT = 911
DEFAULT CHARACTER SET = utf8mb3;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;




















