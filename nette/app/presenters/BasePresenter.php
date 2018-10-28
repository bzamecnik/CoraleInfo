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

	public function ensureLoggedUser()
	{
		if (!$this->isUserLoggedIn()) {
			$backlink = $this->getApplication()->storeRequest();
			$this->redirect('WordPressSign:in', array('backlink' => $backlink));
		}
	}

	public function isUserLoggedIn()
	{
		return $this->getUser()->isLoggedIn();
	}

	// Renders full name if user is logged in but incomplete name for public view.
	public function renderPublicMemberName($member)
	{
		$last_name = $this->isUserLoggedIn()
			? $member->last_name
			: mb_substr($member->last_name, 0, 3) . "...";
		$full_name = $member->first_name . " " . $last_name;
		return $full_name;
	}

	protected function nullizeEmptyString($str) {
		$str = trim($str);
		return !empty($str) ? $str : NULL;
	}

	protected function getUserPrefsSession() {
		return $this->getSession(self::SESSION_USER_PREFS);
	}
}
