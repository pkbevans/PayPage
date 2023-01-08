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
  CONSTRAINT `ORDER`
    FOREIGN KEY (`orderId`)
    REFERENCES `bondevans_com`.`orders` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
