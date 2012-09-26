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
		$query = $this->db->prepare('select * from tasks where device_id = ? and is_complete = ? and is_error = ? order by queued asc');
		$query->execute(array($device_id,0,0));
		return $query->fetchAll();
	}
	
	public function prime($task_id,$total_steps,$current_step,$step_desc){
		$query = $this->db->prepare('update tasks set total_steps = ?, current_step = ?, step_desc = ? where task_id = ?');
		$query->execute(array($total_steps,$current_step,$step_desc,$task_id));
		return $task_id;
	}
	
	public function update($task_id,$current_step,$step_desc){
		$query = $this->db->prepare('update tasks set current_step = ?, step_desc = ? where task_id = ?');
		$query->execute(array($current_step,$step_desc,$task_id));
		return $task_id;
	}
	
	public function complete($task_id){
		$query = $this->db->prepare('update tasks set is_complete = ? where task_id = ?');
		$query->execute(array(1,$task_id));
		return $task_id;
	}
	
	public function error($task_id,$err_msg,$err_code){
		$query = $this->db->prepare('update tasks set is_error = ?, err_msg = ?, err_code = ? where task_id = ?');
		$query->execute(array(1,$err_msg,$err_code,$task_id));
		return $task_id;
	}
	
}