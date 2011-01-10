<?php
mb_internal_encoding("UTF-8");
foreach(glob("etc/conf.d/*.php") as $file) {
	App::$config += require_once $file;
}

function __autoload($class)
{
	$class = strtolower(str_replace('_', DIRECTORY_SEPARATOR, $class));
    if (strpos($class,'cache') == true)
        require_once "lib/cache/{$class}.php";
    else if (strpos($class,'db') == true)
        require_once "lib/db/{$class}.php";
	else if (file_exists("lib/{$class}.php"))
		require_once "lib/{$class}.php";
    else
        require_once "app/lib/{$class}.php";
}

function dump($msg, $die = false)
{
	var_dump($msg);
	if ($die) die;
}