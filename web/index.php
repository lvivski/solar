<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 'on');
ini_set('session.save_path', __DIR__ . '/../tmp/session');
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 1);
ini_set('session.name', 'sid');
ob_start();
$st = microtime(true);
set_time_limit(50);
$_GET['mod'] = '';
session_start();
try {
    chdir('../');
    require_once 'init.php';
    App::$cache = new Memcachedi(true);
    App::$cache->addServer(App::$config['memcache']);
    App::$db = new Mysqldb(App::$config['db']);
    App::$user = new User;
    App::$visitor = new Visitor;
    Route::parse();
    require_once "app/mod/{$_GET['mod']}.php";
    $page = new Page();
    $data = $page->exec();
    if ($data == -1)
        $template = Template::get('needlogin');
    elseif ($data === 0)
        $template = Template::get('nopage');
    else
        $template = Template::get($_GET['mod']);

    ob_clean();

    if ($template !== false) {
        ob_start();

        $inc = Template::inc('header', $_GET['mod']);
        include Template::get($inc, 'elem');
        extract((array) $data);
        include $template;

        $inc = Template::inc('footer', $_GET['mod']);
        include Template::get($inc, 'elem');
        $res = ob_get_clean();
		echo $res;
	} else {
        echo $data;
    }
}
catch (MysqldbException $e) {
	echo "Service is down.<br />DB error [" . $e->getCode () . "]: " . $e->getMessage ();
	dump($e->getTrace(),true);
}
catch (Exception $e) {
	echo 'Service is down.<br />Error ['.$e->getCode().']: '.$e->getMessage();
	dump($e->getTrace(),true);
}
ob_flush();