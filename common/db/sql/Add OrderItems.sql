
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema paypage
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `paypage` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci ;
USE `paypage` ;

-- -----------------------------------------------------
-- Table `paypage`.`orderItems`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `paypage`.`orderItems` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `orderId` INT NOT NULL,
  `productCode` VARCHAR(30) NOT NULL,
  `description` VARCHAR(50) NOT NULL,
  `quantity` FLOAT NOT NULL,
  `unitPrice` FLOAT NOT NULL,
  `totalAmount` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  INDEX `ORDER` (`orderId` ASC),
  CONSTRAINT `ORDER`
    FOREIGN KEY (`orderId`)
    REFERENCES `paypage`.`orders` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
