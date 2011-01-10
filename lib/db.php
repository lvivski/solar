<?php
interface Db
{
    public function insert(array $data, $table);
    
    public function update(array $data, $table, array $where);
    
    public function delete($table, array $where);
    
    public function select(array $data, $table, array $where);
}