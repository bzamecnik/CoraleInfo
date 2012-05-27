<?php
use Nette\Application\UI,
    Nette\Database\Table\Selection;

class SongList extends UI\Control
{
	/** @var \Nette\Database\Table\Selection */
	private $songs;
	
	/** @var Songs */
	private $table;

	public function __construct(Selection $songs, Songs $table)
	{
		parent::__construct(); // vždy je potřeba volat rodičovský konstruktor
		$this->songs = $songs;
		$this->table = $table;
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . '/SongList.latte');
		$this->template->songs = $this->songs;
		$this->template->render();
	}
}
