<?php

use Nette\Security as NS;

/**
 * Users authenticator.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class Authenticator extends Nette\Object implements NS\IAuthenticator
{
	/**
	 * Performs an authentication
	 * @param  array
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;

		if ((($username === 'bzamecnik') && ($password === 'krysopras'))) {
			return new NS\Identity(1, NULL, array(
				'id' => 1,
				'username' => $username,
				'name'=>"Bohumír Zámečník"));
		} else if ((($username === 'iva') && ($password === 'vlastovenka'))) {
		return new NS\Identity(1, NULL, array(
			'id' => 2,
			'username' => $username,
			'name'=>"Iva Mimrová"));
		} else if ((($username === 'corale') && ($password === 'rudyvoko'))) {
		return new NS\Identity(1, NULL, array(
			'id' => 3,
			'username' => $username,
			'name'=>"Corale"));
		} else {
			throw new NS\AuthenticationException("Invalid username or password.",
				self::INVALID_CREDENTIAL);
		}
	}
}
?>