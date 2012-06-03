<?php

use Nette\Database\Connection,
	Nette\Database\Table\Selection;

/**
 * A many-to-many mapping between members and events with additional attributes.
 */
class Attendances extends Selection
{
	public function __construct(\Nette\Database\Connection $connection)
	{
		parent::__construct('corale_attendance', $connection);
	}
}
