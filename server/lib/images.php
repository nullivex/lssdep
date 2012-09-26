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
	
	public function getPartitions($image_id){
		$query = $this->db->prepare('select * from image_partitions where image_id = ?');
		$query->execute(array($image_id));
		return $query->fetchAll();
	}
	
}