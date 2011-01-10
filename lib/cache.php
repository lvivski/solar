<?php
interface Cache
{
    public function get($key);

    public function getMulti($keys);
    
    public function set($key, $data, $expire);

    public function add($key, $data, $expire);

    public function delete($key);

    public function flush();
}