<?php
use Nette\Application\UI,
    Nette\Database\Table\Selection;

class EventList extends UI\Control
{
	/** @var \Nette\Database\Table\Selection */
	private $events;
	
	/** @var Events */
	private $table;

	public function __construct(Selection $events, Events $table)
	{
		parent::__construct(); // vždy je potřeba volat rodičovský konstruktor
		$this->events = $events;
		$this->table = $table;
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . '/EventList.latte');
		$this->template->events = $this->events;
		$this->template->render();
	}
}
