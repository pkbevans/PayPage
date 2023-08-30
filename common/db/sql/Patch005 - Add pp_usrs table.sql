-- -----------------------------------------------------
-- Table `pp_usrs`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `pp_usrs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `firstName` VARCHAR(255) NOT NULL,
  `lastName` VARCHAR(255) NOT NULL,
  `userName` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `userActive` VARCHAR(1) NOT NULL DEFAULT 'N',
  `loginAttempts` INT NOT NULL DEFAULT '0',
  `customerId` varchar(45) NOT NULL,
  `type` varchar(45) NOT NULL COMMENT 'CUSTOMER/INTERNAL',
  `admin` varchar(1) NOT NULL DEFAULT 'N',
  `verificationCode` VARCHAR(256) default '' not null,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  UNIQUE INDEX `username_UNIQUE` (`userName` ASC));

CREATE TABLE IF NOT EXISTS `pp_sessions` (
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
  CONSTRAINT `pp_sessionuserid_fk`
    FOREIGN KEY (`userId`)
    REFERENCES `pp_usrs` (`id`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT);