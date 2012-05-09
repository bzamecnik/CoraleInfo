<?php

use Nette\Security as NS;

class WordPressAuthenticator extends Nette\Object
{
	/** WordPress document root relative to $_SERVER['DOCUMENT_ROOT'] (with trailing /) */
	private $wpDocRoot;
	/** WordPress base URL (with trailing /) */
	private $wpUrl;
	
	public function __construct($wpDocRoot, $wpUrl)
	{
		$this->wpDocRoot = $wpDocRoot;
		$this->wpUrl = $wpUrl;
	}

	public function getLoggedWordPressUserId()
	{
		// boot WordPress
		define('WP_USE_THEMES', false);
		global $wp_rewrite;
		global $wp;
		require_once($_SERVER['DOCUMENT_ROOT'].$this->wpDocRoot.'wp-load.php');
		
		// get the logged WP user
		global $current_user;
		get_currentuserinfo();

		return $current_user->ID;
	}
	
	/**
	 * $return member identity
	 */
	public function getMemberIdentityForWpUser($context, $wpUserId)
	{
		$binding = $context->createUserBindings()->where(array('wp_user_id' => $wpUserId))->fetch();
		if ($binding != FALSE) {
			$memberId = $binding['member_id'];
			$member = $context->createMembers()->get($memberId);
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
	
	public function getWpLoginPageUrl()
	{
		$url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		return $this->wpUrl."wp-login.php?redirect_to=$url";
	}
	
	public function getWpUserProfileUrl() {
		return $this->wpUrl."wp-admin/profile.php";
	}
	
	public function getWpBaseUrl() {
		return $this->wpUrl;
	}
}
