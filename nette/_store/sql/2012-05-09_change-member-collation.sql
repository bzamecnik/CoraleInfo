ALTER TABLE `corale_member`
	CHANGE COLUMN `email` `email` TINYTEXT NULL COLLATE 'utf8_general_ci' AFTER `instruments`,
	CHANGE COLUMN `phone` `phone` VARCHAR(13) NULL DEFAULT NULL COLLATE 'utf8_general_ci' AFTER `email`;
