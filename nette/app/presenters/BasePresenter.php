<?php

/**
 * Base class for all application presenters.
 *
 * @author John Doe
 * @package MyApplication
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	/** Identifier of the session for storing user preferences. */
	const SESSION_USER_PREFS = 'SESSION_USER_PREFS';

	public function startup() {
		parent::startup();
	
		$this->getUserPrefsSession()->setExpiration('+ 14 days');
	
		Nette\Forms\Container::extensionMethod('addDateTimePicker',
			function (Nette\Forms\Container $container, $name, $label = NULL) {
				return $container[$name] = new Extras\Forms\DateTimePicker($label);
			});

		Nette\Forms\Container::extensionMethod('addDatePicker',
			function (Nette\Forms\Container $container, $name, $label = NULL) {
				return $container[$name] =  new JanTvrdik\Components\DatePicker($label);
			});
	}

	protected function afterRender() {
		if ($this->isAjax()) {
			$this->invalidateControl('flashMessages');
		}
	}
	
	protected function ensureLoggedUser()
	{
		if (!$this->getUser()->isLoggedIn()) {
			$backlink = $this->getApplication()->storeRequest();
			$this->redirect('WordPressSign:in', array('backlink' => $backlink));
		}
	}
	
	protected function nullizeEmptyString($str) {
		$str = trim($str);
		return !empty($str) ? $str : NULL; 
	}
	
	protected function getUserPrefsSession() {
		return $this->getSession(self::SESSION_USER_PREFS);
	}
}
