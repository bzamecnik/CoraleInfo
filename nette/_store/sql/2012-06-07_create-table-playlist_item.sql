CREATE TABLE `corale_playlist_item` (
	`event_id` INT(10) NOT NULL,
	`song_id` INT(10) NOT NULL,
	`ord` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`event_id`, `song_id`)
)
COLLATE='utf8_czech_ci'
ENGINE=InnoDB;
