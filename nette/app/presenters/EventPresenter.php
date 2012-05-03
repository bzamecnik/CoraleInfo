<?php

use Nette\Application\UI\Form;

class EventPresenter extends BasePresenter
{
	private $eventTypes = array(
		Events::TYPE_CONCERT => 'Koncert',
		Events::TYPE_REHEARSAL => 'Zkouška',
		Events::TYPE_WORKSHOP => 'Soustředění',
		Events::TYPE_TRIP => 'Výlet',
		Events::TYPE_PARTY => 'Oslava',
		Events::TYPE_OTHER => 'Různé'
		);
	
	const SESSION_KEY_SHOW_HIDDEN_EVENTS = 'EventPresenter.showHiddenEvents';
	
	/** @persistent int */
	public $id;
	/** @var Events */
	private $event;
	/** @var boolean */
	private $filterFromNow = false;
	/** @var int */
	private $filterYear = '';
	/** @var string */
	private $filterType = '';
	/** @var array */
	private $years;

	public function actionDefault($type)
	{
		$this->filterFromNow = true;
		
		$this->filterType = $type;
	}
	
	public function actionArchive($year, $type)
	{
		$this->years = $this->getEventYears();
		$this->filterYear = !empty($year) ? $year : date('Y');
		
		$this->filterType = $type;
	}

	public function actionDetails($id)
	{
		$this->event = $this->context->createEvents()->get($id);
		if ($this->event === FALSE) {
			$this->setView('notFound');
		}
	}
	
	public function actionCreate()
	{
		$this->ensureLoggedUser();
	}
	
	public function actionEdit($id)
	{
		$this->ensureLoggedUser();
		$this->event = $this->context->createEvents()->get($id);
		if ($this->event === FALSE) {
			$this->setView('notFound');
		}
		$this["eventForm"]->setDefaults($this->event);
	}
	
	public function actionDelete($id)
	{
		$this->ensureLoggedUser();
		$this->event = $this->context->createEvents()->get($id);
		if ($this->event === FALSE) {
			$this->setView('notFound');
			return;
		}
		$this->context->createEvents()->where(array('id' => $id))->delete();
		$this->flashMessage('Událost byla smazána.', 'success');
		$this->id = null;
		$this->redirect('default');
	}
	
	public function handleSetShowHidden($showHidden) {
		$this->setShowHidden((bool)$showHidden);
	}

	public function renderDefault()
	{
		$this->pushTaskListTemplateParams();
	}
	
	public function renderArchive()
	{
		$this->pushTaskListTemplateParams();		
	}
	
	public function renderEdit()
	{
		$this->template->event = $this->event;
	}
	
	private function pushTaskListTemplateParams() {
		$this->template->years = $this->years;
		$this->template->types = $this->eventTypes;
		$this->template->type = $this->filterType;
		$this->template->year = $this->filterYear;
		$this->template->showHidden = $this->getShowHidden();
	}
	
	private function getEventYears()
	{
		return $this->context->createEvents()
			->select('DISTINCT year(date_start) y')
			->order('date_start DESC')->fetchPairs('y');
	}
	
	public function renderDetails()
	{
		$this->template->event = $this->event;
		$this->template->years = $this->getEventYears();
		$this->template->type = $this->filterType;
	}
	
	public function createComponentConcerts()
	{
		return $this->createComponentEvents(Events::TYPE_CONCERT);
	}
	
	public function createComponentRehearsals()
	{
		return $this->createComponentEvents(array(
			Events::TYPE_REHEARSAL,	Events::TYPE_WORKSHOP));
	}
	
	public function createComponentOtherEvents()
	{
		return $this->createComponentEvents(array(
			Events::TYPE_TRIP, Events::TYPE_PARTY, Events::TYPE_OTHER));
	}

	public function createComponentEvents($eventTypes)
	{
		$query = $this->context->createEvents()->where('type', $eventTypes);
		if (!$this->getShowHidden()) {
			$query->where('hidden', false);
		}
		if ($this->filterFromNow) {
			$query->where('date_start > ?', date("Y-m-d H:i:s"));
		}
		if ($this->filterYear) {
			$query->where('year(date_start)', $this->filterYear);
		}
		$query->order('date_start ASC');
		return new EventList($query, $this->context->createEvents());	
	}
	
	protected function createComponentEventForm()
	{
		$form = new Form();
		$form->addSelect('type', 'Typ', $this->eventTypes)
			->setPrompt('- Vyberte -')
			->addRule(Form::FILLED, 'Je nutné vybrat typ události.');
		$form->addDateTimePicker('date_start', 'Datum a čas (od)')
			->addRule(Form::FILLED, 'Je nutné zadat počáteční datum.');
		$form->addDateTimePicker('date_end', 'Datum a čas (do)');
		$form->addText('place', 'Místo');
		$form->addTextArea('description', 'Popis');
		$form->addCheckbox('hidden', 'Skrýt')
			->setDefaultValue(FALSE);
		$form->addCheckbox('attendance_active', 'Zobrazit v účasti')
			->setDefaultValue(FALSE);
		
		$form->addSubmit('save', ($this->event) ? 'Upravit' : 'Vytvořit');
		$form->onSuccess[] = callback($this, 'processEventForm');
		
		$presenter = $this;
		$form->addSubmit('cancel', 'Zrušit')
			->setValidationScope(FALSE)
			->onClick[] = function () use ($presenter) {
				$presenter->id = null;
				$presenter->redirect('default');
			};		
		return $form;
	}

	public function processEventForm(Form $form)
	{
		$this->ensureLoggedUser();
		if ($this->id AND !$this->event) {
			// record existence check in case of editing
			throw new BadRequestException;
		}
		$values = $form->values;
		foreach (array('date_start', 'date_end', 'place', 'description') as $field) {
			$values[$field] = $this->nullizeEmptyString($values[$field]);
		}
		if ($this->id) {
			$this->context->createEvents()
				->where(array('id' => $this->id))
				->update($values);
			$this->flashMessage('Událost byla upravena.', 'success');
		} else {
			$this->context->createEvents()->insert($values);
			$this->flashMessage('Událost byla vytvořena.', 'success');
		}
		$this->id = null;
		$this->redirect('default');
	}

	private function getShowHidden() {
		$session = $this->getUserPrefsSession();
		return $session[self::SESSION_KEY_SHOW_HIDDEN_EVENTS];
	}
	
	private function setShowHidden($showHidden) {
		$session = $this->getUserPrefsSession();
		$session[self::SESSION_KEY_SHOW_HIDDEN_EVENTS] = $showHidden;
	}
}
