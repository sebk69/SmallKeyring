ALTER TABLE `user`
ADD COLUMN `private` VARCHAR(255) NULL AFTER `roles`,
ADD COLUMN `has_key_password` INT(1) NULL AFTER `private`;
