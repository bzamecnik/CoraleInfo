<?php

use Nette\Database\Connection,
	Nette\Database\Table\Selection;

class Members extends Selection
{
	public function __construct(\Nette\Database\Connection $connection)
	{
		parent::__construct('corale_member', $connection);
	}
}

class VoiceType {
	const TYPE_SOPRANO = 'soprano';
	const TYPE_ALTO = 'alto';
	const TYPE_TENOR = 'tenor';
	const TYPE_BASS = 'bass';
} 

?>
