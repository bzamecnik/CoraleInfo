<?php

use Nette\Security as NS;

class WordPressSignPresenter extends BasePresenter
{
	public function actionIn()
	{
		if ($this->getUser()->isLoggedIn()) {
			$this->flashMessage("Už jste byli přihlášeni pomocí WordPressu.", 'success');
			$this->redirect('Homepage:');
		}
	
		$wpUserId = $this->getLoggedWordPressUserId();
		$loggedToWordPress = $wpUserId != 0;
		if ($loggedToWordPress)
		{
			// find matching ChoirMaster member id and create their identity
			$identity = $this->getMemberIdentityForWpUser($wpUserId);
			if ($identity != NULL) {
				$this->getUser()->login($identity);
				$this->flashMessage("Přihlášení pomocí WordPressu proběhlo úspěšně.", 'success');
				$this->redirect('Homepage:');
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
	
	private function getLoggedWordPressUserId()
	{
		// boot WordPress
		define('WP_USE_THEMES', false);
		global $wp_rewrite;
		global $wp;
		require_once($_SERVER['DOCUMENT_ROOT'].'/corale.cz/wordpress/wp-load.php');
		
		// get the logged WP user
		global $current_user;
		get_currentuserinfo();

		return $current_user->ID;
	}
	
	/**
	 * $return identity
	 */
	private function getMemberIdentityForWpUser($wpUserId)
	{
		$binding = $this->context->createUserBindings()->where(array('wp_user_id' => $wpUserId))->fetch();
		if ($binding != FALSE) {
			$memberId = $binding['member_id'];
			$member = $this->context->createMembers()->get($memberId);
			$userData = array(
				'id' => $memberId,
				'username' => $current_user->user_login,
				'name' => $member['first_name']." ".$member['last_name'],
				);
			return new NS\Identity($memberId, NULL, $userData);
		} else {
			return NULL;
		}
	}
	
	/** redirect to the WordPress login page */
	private function redirectToWpLoginPage()
	{
		$url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$this->redirectUrl("/corale.cz/wordpress/wp-login.php?redirect_to=$url");
	}
}