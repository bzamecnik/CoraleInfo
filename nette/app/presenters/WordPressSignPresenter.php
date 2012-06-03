<?php

class WordPressSignPresenter extends BasePresenter
{
	/** var WordPressAuthenticator */
	private $wpAuthenticator;

	/**
     * @param WordPressAuthenticator
     */
    public function startup()
    {
		parent::startup();
        $this->wpAuthenticator = $this->context->container->wpAuthenticator;
	}
	
	public function actionIn()
	{
		if ($this->getUser()->isLoggedIn()) {
			$this->flashMessage("Už jste byli přihlášeni pomocí WordPressu.", 'success');
			$this->redirectBackOrHome();
			return;
		}
	
		$wpUserId = $this->wpAuthenticator->getLoggedWordPressUserId();
		$loggedToWordPress = $wpUserId != 0;
		if ($loggedToWordPress)
		{
			// find matching ChoirMaster member id and create their identity
			$identity = $this->wpAuthenticator->getMemberIdentityForWpUser($this->context, $wpUserId);
			if ($identity != NULL) {
				$this->getUser()->login($identity);
				$this->flashMessage("Přihlášení pomocí WordPressu proběhlo úspěšně.", 'success');
				$this->redirectBackOrHome();
			} else {
				$this->flashMessage("Přihlášený uživatel WordPressu (ID: $wpUserId) není asociován k žádnému členovi.", 'error');
			}
		} else {
			$this->redirectToWpLoginPage();
		}
	}
	
	public function actionOut()
	{
		// clear the ChoirMaster login cookie (leave the user logged into WordPress)
		$this->getUser()->logout();
		$this->flashMessage('Byli jste odhlášeni.', 'success');
		$this->redirect('Homepage:');
	}
	
	public function actionProfile() {
		$this->redirectUrl($this->wpAuthenticator->getWpUserProfileUrl());
	}
	
	/** redirect to the WordPress login page */
	public function redirectToWpLoginPage() {
		$url = $this->wpAuthenticator->getWpLoginPageUrl();
		$this->redirectUrl($url);
	}
	
	private function redirectBackOrHome() {
		$backlink = $this->getParam('backlink');
		if ($backlink) {
			$this->getApplication()->restoreRequest($backlink);
		} else {
			$this->redirect('Homepage:');
		}
	}
}
