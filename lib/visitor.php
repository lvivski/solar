<?php
class Visitor
{
	public $id;
	
    function __construct()
	{
		$id = isset($_COOKIE['vs']) ? $_COOKIE['vs'] : null;
		if (mb_strlen($id) < 4) {
			$id = sprintf("%08x", ip2long($_SERVER['REMOTE_ADDR'])) . sprintf("%08x", crc32(microtime()));
			setcookie('vs', $id, mktime()+3600*24*365*2, "/", "." . App::$config['domain']);
		}
		$this->id = $id;
	}
}