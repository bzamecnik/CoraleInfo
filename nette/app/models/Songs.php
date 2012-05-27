<?php

use Nette\Database\Connection,
	Nette\Database\Table\Selection;

class Songs extends Selection
{
	public function __construct(\Nette\Database\Connection $connection)
	{
		parent::__construct('corale_song', $connection);
	}
}
