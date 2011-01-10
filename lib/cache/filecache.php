<?php
class Filecache implements Cache
{
    public $path = 'tmp/cache', // path to cached files
           $ext = '.bin'; //default cache extension
    protected $_gced, // is garbage collected
              $_gcProbability = 100; // the probability (parts per million) that garbage collection (GC) should be performed when storing a piece of data

    public function get($key)
	{
		$file=$this->_getFile($key);
		if(($time = @filemtime($file)) > time())
			return file_get_contents($file);
		else if($time>0)
			@unlink($file);
		return false;
	}
    
    public function getMulti($keys)
    {
        return false;
    }

    public function set($key, $value, $expire)
	{
        if (! $this->_gced && mt_rand(0,1000000) < $this->_gcProbability) {
			$this->_gc();
			$this->_gced=true;
		}
		if ($expire <= 0)
			$expire = 31536000; // 1 year
		$expire += time();

		$file = $this->_getFile($key);
		if (@file_put_contents($file, $value, LOCK_EX) == strlen($value)) {
			@chmod($file,0777);
			return @touch($file, $expire);
		} else {
            return false;
        }
	}

    public function add($key, $value, $expire)
	{
		$file=$this->_getFile($key);
		if(@filemtime($file)>time())
			return false;
		return $this->set($key, $value, $expire);
	}
    
    public function delete($key)
	{
		$file=$this->_getFile($key);
		unlink($file);
	}

    public function flush()
	{
		return $this->_gc(false);
	}
    
    protected function _getFile($key)
	{
	    return $this->path.DIRECTORY_SEPARATOR.$key.$this->ext;
	}

    protected function _gc($expired = true, $path = null)
	{
		if ($path === null)
			$path=$this->path;
		if (($handle = opendir($path)) === false)
			return;
		while (($file = readdir($handle)) !== false) {
			if($file[0] === '.')
				continue;
			$path = $path.DIRECTORY_SEPARATOR.$file;
			if (is_dir($path))
				$this->_gc($expired,$path);
			else if ($expired && @filemtime($path)<time() || ! $expired)
				@unlink($path);
		}
		closedir($handle);
	}

    public function setGc($value)
	{
		$value = (int) $value;
		if ($value < 0)
			$value = 0;
		if ($value > 1000000)
			$value = 1000000;
		$this->_gcProbability = $value;
	}
}