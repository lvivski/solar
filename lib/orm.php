<?php
class OrmException extends Exception {}

abstract class Orm
{

	protected $_fields = array(),
		      $_changed = array(),
		      $_id = 0,
		      $_table = null;

	public function init($id)
    {
		$this->_id = $id;
		if (App::$cache && $this->getCache()) {
			return true;
		}
		if ($this->getMysql()) {
			if (App::$cache)
				App::$cache->set($this->cacheKey(), $this->_fields, 60);
			return true;
		}
		return false;
	}

	protected function cacheKey()
	{
		return App::$config['orm_key'].':'.$this->_table.':id:'.$this->_id;
	}

	protected function getCache()
	{
		if (App::$cache && $data=App::$cache->get($this->cacheKey())) {
			$this->_fields = $data;
			return true;
		}
		return false;
	}

	protected function getMysql()
    {
		$data = App::$db->selectRow(array(),$this->_table,array('id' => $this->_id));
		if (App::$db->num_rows) {
			$this->_fields = $data;
			return true;
		}
		return false;
	}

	public function __get($field)
    {
		if(array_key_exists($field, $this->_fields)) {
			return $this->_fields[$field];
		} else {
			trigger_error('Object ['.get_class($this)."] doesn't has attribute [{$field}], operation [__get].", E_USER_WARNING);
			return null;
		}
	}
    
    public function __set($field, $value)
    {
		if (array_key_exists($field, $this->_fields)) {
			$this->_fields[$field]=$value;
			$this->_changed[$field]=$value;
		} else {
			throw new OrmException('Object ['.get_class($this)."] doesn't has attribute [{$field}], operation [__set].");
		}
	}
    
    public function __isset($attr)
    {
		if (isset($this->_fields[$attr])) {
            return (false === empty($this->_fields[$attr]));
        } else {
            return null;
        }
	}

	public function save()
    {
		if(count($this->_changed)) {
			App::$db->update($this->_changed, $this->_table, array('id'=>$this->id));
			App::$db->purge("select * from `{$this->_table}` WHERE id = '{$id}'");
			if(App::$cache)
				App::$cache->set($this->cacheKey(), $this->_fields, 60);
		}
	}

	public static function create()
    {
		trigger_error('Object ['.get_class(self).'] doesn\'t has method [create] defined.', E_ERROR);
	}

	public function delete()
    {
		trigger_error('Object ['.get_class($this).'] doesn\'t has method [delete] defined.', E_ERROR);
	}
}