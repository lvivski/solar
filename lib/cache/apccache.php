<?php
class Apccache implements Cache
{
    public function get($key)
	{
		return apc_fetch($key);
	}

    protected function getMulti($keys)
	{
		return apc_fetch($keys);
	}

    public function set($key, $value, $expire = 0)
	{
		return apc_store($key, $value, $expire);
	}

    public function add($key, $value, $expire = 0)
	{
		return apc_add($key, $value, $expire);
	}

    public function delete($key)
	{
		return apc_delete($key);
	}

	public function flush()
	{
		return apc_clear_cache('user');
	}
}