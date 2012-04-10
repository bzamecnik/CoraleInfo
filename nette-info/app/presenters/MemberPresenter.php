<?php

use Nette\Application\UI\Form;

class MemberPresenter extends BasePresenter
{
	/** @persistent int */
  public $id;
	/** @var Members */
	private $member;
	/** @var boolean */
	private $showActive = true;
	/** @var boolean */
	private $showGuest = false;
	
	public function actionList($active, $guest) {
		$this->showActive = $active;
		$this->showGuest = $guest;
	}

	public function actionDetails($id)
	{
		$this->member = $this->context->createMembers()->get($id);
		if ($this->member === FALSE) {
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
		$this->member = $this->context->createMembers()->get($id);
		if ($this->member === FALSE) {
			$this->setView('notFound');
		}
		$this["memberForm"]->setDefaults($this->member);
	}
	
//	public function actionDelete($id)
//	{
//		$this->ensureLoggedUser();
//		$this->member = $this->context->createMembers()->get($id);
//		if ($this->member === FALSE) {
//			$this->setView('notFound');
//		}
//		$this->context->createMembers()->where(array('id' => $id))->delete();
//		$this->flashMessage('Osoba byla smazána.', 'success');
//		$this->id = null;
//		$this->redirect('default');
//	}

	public function beforeRender()
	{
		$this->template->setTranslator(new MemberTranslator);
	}
	
	public function renderDefault()
	{
		$this->getMemberList();
	}
	
	public function renderList()
	{
		$this->getMemberList();
	}
	
	private function getMemberList() {
		$query = $this->context->createMembers();
		if ($this->showActive !== NULL) {
			$query->where('active', $this->showActive);
		}
		if ($this->showGuest !== NULL) {
			$query->where('guest', $this->showGuest);
		}
		$query->order('last_name,first_name');
		$this->template->members = $query;

		$this->template->list_title = $this->getListTitle($this->showActive, $this->showGuest);
	}
	
	private function getListTitle($active, $guest) {
		$title;
		if ($active !== NULL) {
			$title = $active ? 'Aktivní ' : 'Bývalí '; 
		} else {
			$title = 'Všichni ';
		}
		if ($guest !== NULL) {
			$title .= $guest ? 'hosté' : 'členové'; 
		}
		return trim($title);
	}
	
	public function renderDetails()
	{
		$this->template->member = $this->context->createMembers()->get($this->id);
	}
	
	protected function createComponentMemberForm()
	{
		$form = new Form();
		$form->addText('first_name', 'Jméno *')
			->addRule(Form::FILLED, 'Je nutné zadat jméno.');
		$form->addText('last_name', 'Příjmení *')
			->addRule(Form::FILLED, 'Je nutné zadat příjmení.');
		$form->addSelect('voice_type', 'Hlas', MemberTranslator::$voice_types)
			->setPrompt('- Vyberte -');
		$form->addText('instruments', 'Nástroje', 50);
		$form->addText('email', 'E-mail', 50);
		$form->addText('phone', 'Telefon');
		$form->addCheckbox('active', 'Aktivní')
			->setDefaultValue(TRUE);
		$form->addCheckbox('guest', 'Host')
			->setDefaultValue(FALSE);
		$form->addTextArea('description', 'Další informace');
		
		if ($this->member) {
			$form->addSubmit('edit', 'Upravit');
		} else {
			$form->addSubmit('create', 'Vytvořit');
		}
		$form->onSuccess[] = callback($this, 'processMemberForm');
		
		$presenter = $this;
		$form->addSubmit('cancel', 'Zrušit')
			->setValidationScope(FALSE)
			->onClick[] = function () use ($presenter) {
				$presenter->id = null;
				$presenter->redirect('default');
			};
		return $form;
	}
	
	public function processMemberForm(Form $form)
	{
		$this->ensureLoggedUser();
		if ($this->id AND !$this->member) {
			// record existence check in case of editing
    	throw new BadRequestException;
    }
    $values = $form->values;
    foreach (array('voice_type', 'instruments', 'email', 'phone', 'description')
			as $field) {
    	$values[$field] = $this->nullizeEmptyString($values[$field]);
    }
		if ($this->id) {
			$this->context->createMembers()
				->where(array('id' => $this->id))
				->update($values);
			$this->flashMessage('Informace o osobě byly upraveny.', 'success');
		} else {
			$this->context->createMembers()->insert($values);
			$this->flashMessage('Osoba byla přidána.', 'success');
		}
		$this->id = null;
		$this->redirect('default');
	}
}

class MemberTranslator implements Nette\Localization\ITranslator
{
	public static $voice_types = array(
		VoiceType::TYPE_SOPRANO => 'soprán',
		VoiceType::TYPE_ALTO => 'alt',
		VoiceType::TYPE_TENOR => 'tenor',
		VoiceType::TYPE_BASS => 'bas',
		);
	private $messages;
	
	public function __construct() {
		$this->messages = self::$voice_types;
	}

	/**
	 * Translates the given string.
	 * @param  string   message
	 * @param  int	  plural count
	 * @return string
	 */
	public function translate($message, $count = NULL)
	{
		return isset($this->messages[$message]) ? $this->messages[$message] : $message;
	}
}
?>