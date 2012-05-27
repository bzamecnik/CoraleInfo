<?php

use Nette\Application\UI\Form;

class SongPresenter extends BasePresenter
{
	/** @persistent int */
	public $id;
	/** @var Songs */
	private $song;
		/** @var Songs */
	private $songs;
	
	private $query;

	public function actionDetails($id)
	{
		$this->song = $this->context->createSongs()->get($id);
		if ($this->song === FALSE) {
			$this->setView('notFound');
			return;
		}
	}

	public function actionEdit($id)
	{
		$this->ensureLoggedUser();
		$this->song = $this->context->createSongs()->get($id);
		if ($this->song === FALSE) {
			$this->setView('notFound');
			return;
		}
		$this["songForm"]->setDefaults($this->song);
	}
	
	public function actionDelete($id)
	{
		$this->ensureLoggedUser();
		$this->song = $this->context->createSongs()->get($id);
		if ($this->song === FALSE) {
			$this->setView('notFound');
			return;
		}
		$this->context->createSongs()->where(array('id' => $id))->delete();
		$this->flashMessage('Písnička byla smazána.', 'success');
		$this->id = null;
		$this->redirect('default');
	}
	
	public function actionSearch($query)
	{
		$this->query = $query;
		$this["searchForm"]->setDefaults(array('query' => $this->query));
	}

	public function renderDetails()
	{
		$this->template->song = $this->song;
	}
	
	public function renderEdit()
	{
		$this->template->song = $this->song;
	}
	
	public function renderSearch() {
		$this->template->query = $this->query;
	}

	public function createComponentSongs()
	{
		$q = $this->context->createSongs();
		if ($this->query) {
			$term = "%$this->query%";
			$q->where("title LIKE ? OR author LIKE ? OR description LIKE ? OR lyrics LIKE ?",
				array($term, $term, $term, $term));
		}
		$q->order('title');
		return new SongList($q, $this->context->createSongs());
	}

	protected function createComponentSongForm()
	{
		$form = new Form();
		$form->addText('title', 'Název')
			->addRule(Form::FILLED, 'Je nutné zadat název písničky.');
		$form->addText('author', 'Autor');
		$form->addTextArea('description', 'Popis');
		$form->addTextArea('lyrics', 'Text písničky');
		
		$form->addSubmit('save', ($this->song) ? 'Upravit' : 'Vytvořit');
		$form->onSuccess[] = callback($this, 'processSongForm');
		
		$presenter = $this;
		$form->addSubmit('cancel', 'Zrušit')
			->setValidationScope(FALSE)
			->onClick[] = function () use ($presenter) {
				$presenter->id = null;
				$presenter->redirect('default');
			};
		return $form;
	}

	public function processSongForm(Form $form)
	{
		$this->ensureLoggedUser();
		if ($this->id AND !$this->song) {
			// record existence check in case of editing
			throw new BadRequestException;
		}
		$values = $form->values;
		foreach (array('author', 'description', 'lyrics') as $field) {
			$values[$field] = $this->nullizeEmptyString($values[$field]);
		}
		if ($this->id) {
			$this->context->createSongs()
				->where(array('id' => $this->id))
				->update($values);
			$this->flashMessage('Písnička byla upravena.', 'success');
		} else {
			$this->id = $this->context->createSongs()->insert($values)->id;
			$this->flashMessage('Písnička byla vytvořena.', 'success');
		}
		$this->redirect('details', array('id' => $this->id));
	}
	
	protected function createComponentSearchForm()
	{
		$form = new Form();
		$form->addText('query', 'Hledaný text')
			->addRule(Form::FILLED, 'Je nutné zadat hledaný text.');
		$form->addSubmit('search', 'Hledat');
		return $form;
	}
}
