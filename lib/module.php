<?php
class Module
{
    protected $_param = array(),
              $_cache= null;

    public function __construct()
    {
         $this->_cache = App::$cache;
    }

    public function exec()
	{
		$this->init();
		if ($this->_cache && $data = $this->_cache->get('mod:'.__FILE__.':'.md5(serialize($this->_param)))) {
            return $data;
        } else {
            $data = $this->run();
            if($this->_cache)
                $this->_cache->set('mod:'.__FILE__.':'.md5(serialize($this->_param)), $data, 60);
		    return $data;
        }
	}

	public function & __get($name)
	{
		if (array_key_exists($name, $this->_param)) {
			return $this->_param[$name];
		}
		return $name = null;
	}

    public function __set($name, $value)
	{
        $this->_param[$name] = $value;
	}

	public function init()
    {}

	public function run()
    {}
}