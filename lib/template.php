<?php
class TemplateException extends Exception {}

class Template
{
    public static $js_files=array(),
                  $js_keys=array();

    public static function get($name, $path = false, $cache = null)
    {
        if (! $path) {
            $source = getcwd()."/app/view/{$name}.phtml";
		    $destination = getcwd()."/tmp/cache/{$name}.php";
            
            if(! file_exists($source)) {
                return false;
            }
        } else {
            $source = getcwd()."/app/view/{$path}/{$name}.phtml";
		    $destination = getcwd()."/tmp/cache/{$path}/{$name}.php";
        }

        $s_time = filemtime($source);
		$d_time = file_exists($destination) ? filemtime($destination) : 0;

        if ($s_time > $d_time) {
		    $tpl_dir = dirname($destination);
		    if (! is_dir($tpl_dir)) {
				mkdir($tpl_dir, 0777, true);
		    }

		    $tpl = file_get_contents($source);

            if ($tpl === false) {
				throw new TplException("TPL: can't read file '{$source}'");
		    }

            $pattern = array(
                '/<%=?\s?\$([a-zA-Z_]+.*)\s?%>/U',
            );
            $replace = array(
                '<?php echo \$$1; ?>',
            );
            $tpl = preg_replace($pattern, $replace, $tpl);

		    if (file_put_contents($destination, $tpl) === false) {
				throw new TemplateException("TPL: can't write file '{$destination}'");
		    }
		}
		return $destination;
    }

    public static function inc($type, $module)
    {
		$mod = 'default';
		foreach (App::$config['alt_tpl'] as $k => $v) {
		    if (in_array($module, $v)) {
			    $mod = $k;
			    break;
		    }
		}
		if (! file_exists("view/elem/{$mod}/{$type}.phtml"))
		    return "default/{$type}";
		else
		    return "{$mod}/{$type}";
    }

    public static function truncate_string($str='')
    {
		global $_CONF;

        if (empty($str) || mb_strlen($str) <= $_CONF['MAX_LINE']) return $str;
			return mb_strcut($str, 0, ($_CONF['MAX_LINE'] / 2)).'...'.mb_strcut($str, -($_CONF['MAX_LINE'] / 2));
    }

    public static function escape($str='')
    {
		return htmlspecialchars($str, ENT_QUOTES);
    }
}