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
		$query = $this->db->prepare('select device_id,mac,name,added from devices where device_id = ?');
		$query->execute(array($device_id));
		$result = $query->fetch(); $query->closeCursor();
		if(!$result) throw new Exception('Could not find device: '.$device_id);
		return $result;
	}
	
	public function getFull($device_id){
		$query = $this->db->prepare('select * from devices where device_id = ?');
		$query->execute(array($device_id));
		$result = $query->fetch(); $query->closeCursor();
		if(!$result) throw new Exception('Could not find device: '.$device_id);
		return $result;
	}
	
	public function getByMAC($mac){
		$query = $this->db->prepare('select device_id,mac,name,added from devices where mac = ?');
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
	
	public function motherboardUpdate($device_id,$uuid,$vendor,$product,$serial){
		try {
			$query = $this->db->prepare('insert into device_motherboard (device_id,uuid,vendor,product,serial)values(?,?,?,?,?)');
			$query->execute(array($device_id,$uuid,$vendor,$product,$serial));
		} catch(PDOException $e){
			if($e->getCode() != ERR_DB_DUPLICATE_VALUE) throw $e;
			$query = $this->db->prepare('update device_motherboard set uuid = ?, vendor = ?, product = ?, serial = ? where device_id = ?');
			$query->execute(array($uuid,$vendor,$product,$serial,$device_id));
		}
		return $device_id;
	}
	
	public function cpuAdd($device_id,$bus,$vendor,$product,$cores,$speed,$bits,$cache,$capabilities){
		$query = $this->db->prepare('
			insert into device_cpus 
			(
				device_id,
				bus,
				vendor,
				product,
				cores,
				speed,
				bits,
				cache,
				capabilities
			) values (?,?,?,?,?,?,?,?,?)
		');
		$query->execute(array(
			$device_id,
			$bus,
			$vendor,
			$product,
			$cores,
			$speed,
			$bits,
			$cache,
			$capabilities
		));
		return $this->db->lastInsertId();
	}
	
	public function memoryAdd($device_id,$vendor,$product,$description,$serial,$slot,$size,$bits,$clock){
		$query = $this->db->prepare('
			insert into device_memory_modules
			(
				device_id,
				vendor,
				product,
				description,
				serial,
				slot,
				size,
				bits,
				clock
			) values (?,?,?,?,?,?,?,?,?)
		');
		$query->execute(array(
			$device_id,
			$vendor,
			$product,
			$description,
			$serial,
			$slot,
			$size,
			$bits,
			$clock
		));
		return $this->db->lastInsertId();
	}
	
	public function NICAdd($device_id,$vendor,$product,$logicalname,$mac,$speed,$irq){
		$query = $this->db->prepare('
			insert into device_nics
			(
				device_id,
				vendor,
				product,
				logicalname,
				mac,
				speed,
				irq
			) values (?,?,?,?,?,?,?)
		');
		$query->execute(array(
			$device_id,
			$vendor,
			$product,
			$logicalname,
			$mac,
			$speed,
			$irq
		));
		return $this->db->lastInsertId();
	}
	
	public function driveAdd($device_id,$controller,$vendor,$product,$device_path,$serial,$size,$bus,$physid){
		$query = $this->db->prepare('
			insert into device_drives
			(
				device_id,
				controller,
				vendor,
				product,
				device_path,
				serial,
				bus,
				physid,
				size
			) values (?,?,?,?,?,?,?,?,?)
		');
		$query->execute(array(
			$device_id,
			$controller,
			$vendor,
			$product,
			$device_path,
			$serial,
			$bus,
			$physid,
			$size
		));
		return $this->db->lastInsertId();
	}
	
	public function inventoryFlush($device_id){
		$this->deleteCPUs($device_id);
		$this->deleteDrivePartitions($device_id);
		$this->deleteDrives($device_id);
		$this->deleteMemoryModules($device_id);
		$this->deleteNICs($device_id);
		return $device_id;
	}
	
	public function deleteCPUs($device_id){
		$query = $this->db->prepare('delete from device_cpus where device_id = ?');
		$query->execute(array($device_id));
		return true;
	}
	
	public function deleteDrivePartitions($device_id){
		$query = $this->db->prepare('delete from device_drive_partitions where device_id = ?');
		$query->execute(array($device_id));
		return true;
	}
	
	public function deleteDrives($device_id){
		$query = $this->db->prepare('delete from device_drives where device_id = ?');
		$query->execute(array($device_id));
		return true;
	}
	
	public function deleteMemoryModules($device_id){
		$query = $this->db->prepare('delete from device_memory_modules where device_id = ?');
		$query->execute(array($device_id));
		return true;
	}
	
	public function deleteNICs($device_id){
		$query = $this->db->prepare('delete from device_nics where device_id = ?');
		$query->execute(array($device_id));
		return true;
	}
	
}