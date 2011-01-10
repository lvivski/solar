<?php
class Route
{
  public static function parse()
  {
    $path = strtok($_SERVER['REQUEST_URI'], '?');
        foreach(App::$config['router'] as $route => $value) {
          if ($route{0} == '~') {
				if(preg_match(mb_substr($route, 1), $path, $match)) {
					foreach($value as $param => $_value) {
						if((int) $_value)
							$_GET[$param] = $match[$_value];
						else
							$_GET[$param] = $value;
					}
					return;
				}
			} else {
				if($route == $path) {
					$_GET[$param] = $match[$value];
					return;
				}
			}
        }
        $_GET['mod'] = 'nopage';
    }
    
    public static function go($url, $code=302, $replace=true)
    {
		if ($code == 301) {
            header("HTTP/1.x 301 Moved Permanently");
        }
		if ($url != '') {
			header('Location: '.$url, $replace, $code);
		} else {
			header('Location: /', $replace, $code);
		}
		die;
	}
}