<?php
class Page extends Module
{
    function run()
    {
    	if (!App::$user->id) {
		    Route::go('/login.html');
        } else {
            Route::go('/owners.html');
        }
    }
}