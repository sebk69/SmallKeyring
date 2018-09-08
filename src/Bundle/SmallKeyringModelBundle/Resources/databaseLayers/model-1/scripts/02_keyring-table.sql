CREATE TABLE IF NOT EXISTS `key` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT,
  `tag` VARCHAR(255) NOT NULL,
  `data` LONGBLOB,
  PRIMARY KEY (`id`),
  INDEX `user_id` (`user_id` ASC))
ENGINE = InnoDB;