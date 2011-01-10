<?php
class Page extends Module
{
	function init()
	{
		$this->_param = array(
			'login' => $_REQUEST['login'],
			'password'	=> $_REQUEST['password'],
			'remember' => isset($_REQUEST['remember'])
		);
	}
	function run()
	{
		if(!App::$user->id) {
			if($this->login && $this->password) {
				try {
					User::authenticate($this->login, $this->password, $this->remember);
					Route::go('/index.html');
				} catch(UserException $e) {
					$return['error'] = $e->getMessage();
				}
			}
			return $return;
		} else {
			Route::go('index.html');
		}
	}
}