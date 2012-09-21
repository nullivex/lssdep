<?php

class Tasks {

	public $db;
	
	public static function _get(){
		return new Tasks();
	}
	
	public function __construct(){
		$this->db = Db::_get();
	}
	
	public function getByDevice($device_id){
		$query = $this->db->prepare('select * from tasks where device_id = ?');
		$query->execute(array($device_id));
		return $query->fetchAll();
	}
	
}