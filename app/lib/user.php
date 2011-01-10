<?php
class UserException extends Exception {}

class User extends Orm
{
	protected $_table = 'users';
	
	const COOKIE_SECRET = ')5(#*3$^#lkj8sdf';
	
	const E_CLASS_FAILED = 1,
		  E_CREATE_FAILED = 2,
		  E_AUTH_INCORRECT = 3;
	
	public function __construct($login = true)
	{    
		$this->_fields['id'] = 0;
		if($login) $this->login_from_session() || $this->login_from_cookie();
	}
	
	public static function authenticate($login, $pass, $remember = true)
	{
		if (! $data = App::$db->selectRow(array(), 'users', array('login' => $login, 'password' => md5($pass)))) {
			throw new UserException('Wrong login or password', self::E_AUTH_INCORRECT);
		}

		$u = new User(false); //create user but do not invoke login_from_* methods
		$u->init($data['id']);

		$_SESSION['user'] = $data;
		if($remember)
			$u->remember();

		return $u;
	}
	
	public function login_from_session()
	{
		if ((int) $_SESSION['user']['id'] > 0) {
			$this->init($_SESSION['user']['id']);
			if (! $this->_fields) {
				$this->cleanup();
				return false;
			}
			$_SESSION['user'] = $this->_fields;
			return true;
		} else {
			$this->cleanup();
			return false;
		}
	}

	public function login_from_cookie()
	{
		if (!isset($_COOKIE['auth_hash']) || !isset($_COOKIE['auth_uid']))
			return false;
		
		if (strlen($_COOKIE['auth_hash']) != 32 || ! intval($_COOKIE['auth_uid'])) {
			$this->cleanup();
			return false;
		}
		
		$this->init((int) $_COOKIE['auth_uid']);

		if (! $this->_fields) {
			$this->cleanup();
			return false;
		}

		$rand = md5($this->_fields['id'].$this->_fields['password'].$this->_fields['login'].self::COOKIE_SECRET);
		if ($_COOKIE['auth_hash'] != $rand) {
			$this->cleanup();
			return false;
		}
		$_SESSION['user'] = $this->_fields;
		return true;

	}

	protected function cleanup()
	{
		$_SESSION['user'] = array('id' => 0 );
		$this->_fields = array('id' => 0 );
	}

	public function remember()
	{
		$_SESSION['user'] = $this->_fields;

		$time = mktime() + 86400 * 365;
		$rand = md5($this->_fields['id'].$this->_fields['password'].$this->_fields['login'].self::COOKIE_SECRET);

		setcookie('auth_uid', $this->_fields['id'], $time, '/', '.'.App::$config['domain']);
		setcookie('auth_hash', $rand, $time, '/', '.'.App::$config['domain']);
	}

	public function logout()
	{
		$this->cleanup();

		setcookie('auth_uid', 0, time()-3600, '/', '.'.App::$config['domain']);
		setcookie('auth_hash', 0, time()-3600, '/', '.'.App::$config['domain']);
	}
	
	public static function create(array $data, $return_object = true)
    {
		self::_validate($data);
		App::$db->insert($data, $this->_table);
        if(!$id = $db->last_id) {
			throw new UserException('Can\'t add User', self::E_CREATE_FAILED);
		}
        if ($return_object) {
			return new User($id);
		} else {
			return $id;
		}
	}
}