<?php
use Nette\Application\UI,
    Nette\Database\Table\Selection;

class PlaylistEditor extends UI\Control
{
	public function __construct()
	{
		parent::__construct();
	}

	public function render($eventId, $playlist, $repertoire)
	{
		$this->template->setFile(__DIR__ . '/PlayListEditor.latte');
		$this->template->eventId = $eventId;
		$this->template->playlist = $playlist;
		$this->template->repertoire = $repertoire;
		$this->template->render();
	}
	
	// /**
	 // * event id
	 // * comma separated list of song ids
	 // */
	// public function handleUpdatePlaylist($eventId, $songIds) {
		
	// }
}
