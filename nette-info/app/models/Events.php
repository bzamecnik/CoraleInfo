<?php

use Nette\Database\Connection,
	Nette\Database\Table\Selection;

class Events extends Selection
{
	const TYPE_CONCERT = 'concert';
	const TYPE_REHEARSAL = 'rehearsal';
	const TYPE_WORKSHOP = 'workshop';
	const TYPE_TRIP = 'trip';
	const TYPE_PARTY = 'party';
	const TYPE_OTHER = 'other';

	public function __construct(\Nette\Database\Connection $connection)
	{
		parent::__construct('corale_event', $connection);
	}
}

?>
