<?php

/**
 * Base class for all application presenters.
 *
 * @author John Doe
 * @package MyApplication
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	public function startup() {
		parent::startup();
	
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
	
	public function handleSignOut()
	{
		$this->getUser()->logout();
		$this->redirect('Sign:in');
	}
	
	protected function ensureLoggedUser()
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
	}
	
	protected function nullizeEmptyString($str) {
		$str = trim($str);
		return !empty($str) ? $str : NULL; 
	}
}
?>