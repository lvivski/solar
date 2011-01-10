<?php
class Memcachedi implements Cache
{
    protected $_backend,
              $_useMemcached = false;

    public $compressed = 0;

    public function __construct($memcached = false)
    {
        if (! $memcached) {
            $this->_backend = new Memcache;
        } else {
            $this->_useMemcached = true;
            $this->_backend = new Memcached;
        }
    }

    public function addServer($server)
    {
        if ($this->_useMemcached) {
            $this->_backend->addServer($server['host'],$server['port'],$server['weight']);
        } else {
            $this->_backend->addServer($server['host'],$server['port'],$server['persistent'],$server['weight'],$server['timeout'],$server['status']);
        }
    }

    public function get($key)
	{
		return $this->_backend->get($key);
	}

    public function getMulti($keys)
    {
        return $this->_useMemcached
                ? $this->_backend->getMulti($keys)
                : $this->_backend->get($keys);
    }

    public function set($key, $value, $expire = 0)
    {
        if($expire>0)
			$expire += time();

        return $this->_useMemcached
                ? $this->_backend->set($key, $value, $expire)
                : $this->_backend->set($key, $value, $this->compressed, $expire);
    }

    public function add($key, $value, $expire = 0)
    {
        if($expire>0)
			$expire += time();

        return $this->_useMemcached
                ? $this->_backend->set($key, $value, $expire)
                : $this->_backend->set($key, $value, $this->compressed, $expire);
    }

    public function delete($key)
	{
		return $this->_backend->delete($key);
	}

    public function flush()
	{
		return $this->_backend->flush();
	}
}