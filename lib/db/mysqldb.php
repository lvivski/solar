<?php
class MysqldbException extends Exception {}

class Mysqldb implements Db
{
	protected $_backend = null;
	
	public    $affected_rows,
			  $num_rows,
			  $last_id;
	
	function __construct(array $config)
	{
		$this->_backend = new Mysqli($config['hostname'],$config['username'],$config['password'],$config['database']);
		
		if($this->_backend->connect_error) {
			throw new MysqldbException($this->_backend->connect_error, $this->_backend->connect_errno);
		}
		$this->_backend->query('SET NAMES utf8');
	}
	
	/**
     * Insert into database
     * @param array $ar insert Array
     * @param string $table inserting table
     * @param string $command INSERT or UPDATE
     * @return mixed
     */
	public function insert(array $arr, $table, $command='')
	{
		$q = self::_recognize_command($command, 'INSERT')
			. " INTO `{$table}` SET\n"
			. $this->_generate_key_value_pairs($arr);
		return $this->query($q);
	}
	
	public function select(array $arr, $table, array $where = array())
	{
		$q = 'SELECT ';
		if (! count($arr)) {
			$q .= '*';
		} else {
			$q .= implode(', ',$arr);
		}
		$q .= " FROM `{$table}`\n".$this->_generate_where_pairs($where);
		
		return $this->query($q);
	}

    /**
     * Update database
     * @param mixed $ar
     * @param string $table
     * @param array $where
     * @param string $command
     * @return mixed
     */
	public function update(array $arr, $table, array $where, $command='')
	{
		$q = self::_recognize_command($command, 'UPDATE')
			. " `{$table}` SET\n".$this->_generate_key_value_pairs($arr)
			. $this->_generate_where_pairs($where);
		return $this->query($q);
	}

    /**
     * Delete from databese
     * @param string $table
     * @param array $where
     * @return mixed
     */
	public function delete($table, array $where)
	{
		$q = 'DELETE FROM'
			. " `{$table}`\n"
			. $this->_generate_where_pairs($where);
		return $this->query($q);
	}
	
	public function query($q, $cache = 10, $return_raw_data = false)
	{
		if (! isset($_SERVER['HTTP_HOST']))
			App::$cache=null;

		$is_select=0;
		if (preg_match('/(^|\()select /i', $q))
			$is_select=1;

		if ($is_select && App::$cache)
			$cached = App::$cache->get('query:'.md5($q));
			
		if (sizeof($cached) == 3){
			$this->num_rows = $cached[1];
			$this->affected_rows = $cached[2];
			return $cached[0];
		}
		
		if (! $result = $this->_backend->query($q)) {
			throw new MysqldbException($this->_backend->error, $this->_backend->errno);
		}
		
		if (preg_match('/insert /i', $q))
			$this->last_id = $this->_backend->insert_id;
			
		$this->affected_rows = $this->_backend->affected_rows;
		if($is_select) {
			$this->num_rows = $result->num_rows;
			while($row = $result->fetch_assoc()) {
				$rows[]= $row;
			}
		
			if (App::$cache)
				App::$cache->set('query:'.md5($q),array($rows,$this->num_rows, $this->affected_rows), $cache);
			if($return_raw_data)
				return $result;
			else
				return $rows;
		}
		return true;
	}
	
	public function & selectOne($field, $table, array $where = array())
	{
		$q = 'SELECT ' . $field . " FROM `{$table}`\n".$this->_generate_where_pairs($where) . ' LIMIT 1';
		if (! $result = $this->query($q))
			return false;
		foreach ($result as $row){
			if (! isset($row[$column])){
				throw new MysqldbException ( "Field '{$column}' not in result of '{$q}'");
			}
			$return = $row[$column];
		}
		return $return;
		
	}
	
	public function & selectRow(array $arr, $table, array $where = array())
	{
		$q = 'SELECT ';
		if (! count($arr)) {
			$q .= '*';
		} else {
			$q .= implode(', ',$arr);
		}
		$q .= " FROM `{$table}`\n".$this->_generate_where_pairs($where).' LIMIT 1';
		$return = $this->query($q);
		
		return isset($return[0])?$return[0]:$return = false;
	}
	
	public function & selectAssigned(array $arr, $table, array $where, $field)
	{
		$q = 'SELECT ';
		if (! count($arr)) {
			$q .= '*';
		} else {
			$q .= implode(', ',$arr);
		}
		$q .= " FROM `{$table}`\n".$this->_generate_where_pairs($where);
		if (! $result = $this->query($q))
			return false;
		$return = array();
		foreach ($result as $row){
			$return[$row[$field]] = $row;
		}
		return $return;
	}
	
	/**
	 * Gets specified column
	 * @param $q
	 * @param $column
	 * @param $cache
	 * @return array
	 */
	public function & selectColumn(array $arr, $table, array $where, $column)
	{
		$q = 'SELECT ';
		if (! count($arr)) {
			$q .= '*';
		} else {
			$q .= implode(', ',$arr);
		}
		$q .= " FROM `{$table}`\n".$this->_generate_where_pairs($where);
		if (! $result = $this->query($q))
			return false;
		foreach ($result as $row){
			if (! isset($row[$column])){
				throw new MysqldbException ( "Column '{$column}' not in result of '{$q}'");
			}
			$return[] = $row[$column];
		}
		return $return;
	}
	
	public function ping()
	{
		return $this->_backend->ping();
	}

	public function close()
	{
		$this->_backend->close();
	}

	public function purge($q)
	{
		if (App::$cache)
			App::$cache->delete('query:'.md5($q));
	}

    /**
     * Generates key->value pairs for statements
     * @access protected
     * @param array $pairs
     * @return string
     */
	protected function & _generate_key_value_pairs($pairs)
    {
		$out = '';
		foreach ($pairs as $k => $v) {
			if ($k{0} != '~') {
				$v = $this->_escape($v);
				$out .= " `{$k}`='{$v}',\n";
			} else {
				$out .= " `".mb_substr($k, 1)."`= {$v},\n";
			}
		}
		$out = mb_substr($out, 0, -2).PHP_EOL; //remove last ",\n" from query
		return $out;
	}

    /**
     * Generates WHERE statment
     * @access protected
     * @param array $pairs
     * @return string
     */
	protected function & _generate_where_pairs($pairs)
    {
    	if(!count($pairs)) return;
		$out = '';
		$sep = ' WHERE ';
		foreach ($pairs as $k => $v) {
			if (is_array($v)) {
				$out .= "{$sep} `{$k}` IN ('".implode("','",$v)."')\n";
			} else if($k{0}!='~') {
				$v = $this->_escape($v);
				$out .= "{$sep} `{$k}` ='{$v}'\n";
			} else {
				$out .= "{$sep} `".mb_substr($k, 1)."` = {$v}\n";
			}
			$sep = ' AND ';
		}
		return $out;
	}

    /**
     * Gets MySQL command
     * @access protected
     * @param string $command
     * @param string $default
     * @return string
     */
	protected static function _recognize_command($command, $default)
	{
		switch ($command) {
			case 'ignore':
				return 'INSERT IGNORE';
				break;
			case 'replace':
				return 'REPLACE';
				break;
			case 'update':
				return 'UPDATE';
				break;
			case 'insert':
				return 'INSERT';
				break;
		}
		return $default;
	}
	
	protected function _escape($data)
	{
		return $this->_backend->real_escape_string($data);
	}
}