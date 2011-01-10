<?php
class Page extends Module
{
	function run()
	{
		if(App::$user->id)
			App::$user->logout();
		Route::go('/index.html');
	}
}