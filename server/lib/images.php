<?php

class Images {

	public $db;
	
	public static function _get(){
		return new Images();
	}
	
	public function __construct(){
		$this->db = Db::_get();
	}
	
	public function get($image_id){
		$query = $this->db->prepare('select * from images where image_id = ?');
		$query->execute(array($image_id));
		$result = $query->fetch(); $query->closeCursor();
		if(!$result) throw new Exception('Could not find image: '.$image_id,ERR_IMAGE_NOT_FOUND);
		return $result;
	}
	
	public function getDrivesWPA($image_id){
		$query = $this->db->prepare('select * from image_drives where image_id = ? order by path asc');
		$query->execute(array($image_id));
		$drives  = $query->fetchAll();
		foreach($drives as &$drive){
			$drive['partitions'] = $this->getDrivePartitions($drive['image_drive_id']);
			$drive['attributes'] = array();
			foreach($this->getDriveAttributes($drive['image_drive_id']) as $row){
				$drive['attributes'][$row['name']] = $row['value'];
			}
		}
		return $drives;
	}	
	
	public function getDrivePartitions($image_drive_id){
		$query = $this->db->prepare('select * from image_drive_partitions where image_drive_id = ? order by number asc');
		$query->execute(array($image_drive_id));
		return $query->fetchAll();
	}
	
	public function getDriveAttributes($image_drive_id){
		$query = $this->db->prepare('select * from image_drive_attributes where image_drive_id = ?');
		$query->execute(array($image_drive_id));
		return $query->fetchAll();
	}
	
}