<?php

class Devices {

	public $db;
	
	public static function _get(){
		return new Devices();
	}
	
	public function __construct(){
		$this->db = Db::_get();
	}
	
	public function get($device_id){
		$query = $this->db->prepare('select * from devices where device_id = ?');
		$query->execute(array($device_id));
		$result = $query->fetch(); $query->closeCursor();
		if(!$result) throw new Exception('Could not find device: '.$device_id);
		return $result;
	}
	
	public function getByMAC($mac){
		$query = $this->db->prepare('select * from devices where mac = ?');
		$query->execute(array($mac));
		$result = $query->fetch(); $query->closeCursor();
		if(!$result) throw new Exception('Could not find device by MAC address: '.$mac);
		return $result;
	}
	
	public function inventoryRawUpdate($device_id,$inventory_raw){
		$query = $this->db->prepare('update devices set inventory_raw = ? where device_id = ?');
		$query->execute(array($inventory_raw,$device_id));
		return true;
	}
	
}