CREATE TABLE `corale_song` (
	`id` INT(10) NOT NULL AUTO_INCREMENT,
	`title` TINYTEXT NOT NULL COLLATE 'utf8_czech_ci',
	`author` TINYTEXT NULL COLLATE 'utf8_czech_ci',
	`description` TEXT NULL COLLATE 'utf8_czech_ci',
	`lyrics` TEXT NULL COLLATE 'utf8_czech_ci',
	PRIMARY KEY (`id`)
)
COLLATE='utf8_czech_ci'
ENGINE=InnoDB;
