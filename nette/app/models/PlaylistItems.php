<?php

use Nette\Database\Connection,
	Nette\Database\Table\Selection;

/**
 * An ordered many-to-many mapping between songs and events.
 */
class PlaylistItems extends Selection
{
	public function __construct(\Nette\Database\Connection $connection)
	{
		parent::__construct('corale_playlist_item', $connection);
	}
}
