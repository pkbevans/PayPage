-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema paypage
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema paypage
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `paypage` DEFAULT CHARACTER SET utf8 ;
USE `paypage` ;

-- -----------------------------------------------------
-- Table `paypage`.`orders`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `paypage`.`orders` ;

CREATE TABLE IF NOT EXISTS `paypage`.`orders` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `merchantReference` VARCHAR(50) NOT NULL,
  `amount` FLOAT NOT NULL DEFAULT '0',
  `refundAmount` FLOAT NOT NULL DEFAULT '0',
  `currency` VARCHAR(3) NOT NULL,
  `customerId` VARCHAR(32) NOT NULL,
  `customerEmail` VARCHAR(45) NOT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'NEW',
  `datetime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  INDEX `MRN` (`merchantReference` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 235
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `paypage`.`apilogs`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `paypage`.`apilogs` ;

CREATE TABLE IF NOT EXISTS `paypage`.`apilogs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `orderId` INT NOT NULL,
  `payload` BLOB NOT NULL,
  `type` VARCHAR(45) NOT NULL,
  `status` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  INDEX `ORDER_idx` (`orderId` ASC),
  CONSTRAINT `ORDERS_APILOGS`
    FOREIGN KEY (`orderId`)
    REFERENCES `paypage`.`orders` (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 6
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `paypage`.`payments`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `paypage`.`payments` ;

CREATE TABLE IF NOT EXISTS `paypage`.`payments` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `orderId` INT NOT NULL,
  `type` VARCHAR(20) NOT NULL DEFAULT 'PAYMENT',
  `amount` FLOAT NOT NULL DEFAULT '0',
  `currency` VARCHAR(3) NOT NULL DEFAULT 'GBP',
  `cardNumber` VARCHAR(19) NOT NULL,
  `cardType` VARCHAR(20) NOT NULL,
  `authCode` VARCHAR(45) NOT NULL,
  `gatewayRequestId` VARCHAR(45) NOT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'NEW',
  `captured` INT NOT NULL DEFAULT '0',
  `datetime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  INDEX `ORDER` (`orderId` ASC),
  CONSTRAINT `PAYMENTS_ORDER`
    FOREIGN KEY (`orderId`)
    REFERENCES `paypage`.`orders` (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 127
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `paypage`.`users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `paypage`.`users` ;

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
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `paypage`.`sessions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `paypage`.`sessions` ;

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
ENGINE = InnoDB
AUTO_INCREMENT = 109
DEFAULT CHARACTER SET = utf8mb3;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
