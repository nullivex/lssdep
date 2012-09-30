<?php

class Devices {

	//drive partition tables
	const DRIVE_PART_TYPE_MSDOS = 'msdos';
	const DRIVE_PART_TYPE_GPT = 'gpt';
	const DRIVE_PART_TYPE_NONE = 'none';
	
	//drive partition types
	const DRIVE_SEGMENT_PRIMARY = 'primary';
	const DRIVE_SEGMENT_LOGICAL = 'logical';
	const DRIVE_SEGMENT_NONE = 'none';
	
	//drive partition filesystems
	const DRIVE_FS_NONE = 'none';
	const DRIVE_FS_SWAP = 'swap';
	const DRIVE_FS_NTFS = 'ntfs';
	const DRIVE_FS_FAT = 'fat';
	const DRIVE_FS_VFAT = 'vfat';
	const DRIVE_FS_FAT16 = 'fat16';
	const DRIVE_FS_FAT32 = 'fat32';
	const DRIVE_FS_EXT2 = 'ext2';
	const DRIVE_FS_EXT4 = 'ext4';
	const DRIVE_FS_XFS = 'xfs';
	const DRIVE_FS_BTRFS = 'btrfs';
	const DRIVE_FS_JFS = 'jfs';
	const DRIVE_FS_REISERFS = 'reiserfs';
	const DRIVE_FS_REISER4 = 'reiser4';

	public $db;
	
	public static function _get(){
		return new Devices();
	}
	
	public function __construct(){
		$this->db = Db::_get();
	}
	
	public function get($device_id){
		$query = $this->db->prepare('select device_id,image_id,mac,name,added,is_wipe from devices where device_id = ?');
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
		$query = $this->db->prepare('select device_id,image_id,mac,name,added,is_wipe from devices where mac = ?');
		$query->execute(array($mac));
		$result = $query->fetch(); $query->closeCursor();
		if(!$result) throw new Exception('Could not find device by MAC address: '.$mac);
		return $result;
	}
	
	public function getDrivesWP($device_id){
		$query = $this->db->prepare('select * from device_drives where device_id = ? order by device_path asc');
		$query->execute(array($device_id));
		$drives = $query->fetchAll();
		foreach($drives as &$drive){
			$drive['partitions'] = $this->getDrivePartitions($drive['device_drive_id']);
		}
		return $drives;
	}
	
	public function getDrivePartitions($device_drive_id){
		$query = $this->db->prepare('select * from device_drive_partitions where device_drive_id = ? order by physid asc');
		$query->execute(array($device_drive_id));
		return $query->fetchAll();
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
	
	public function PCIAdd($device_id,$vendor,$product,$description,$physid,$bus,$bits,$irq){
		$query = $this->db->prepare('
			insert into device_pci
			(
				device_id,
				vendor,
				product,
				description,
				physid,
				bus,
				bits,
				irq
			) values (?,?,?,?,?,?,?,?)
		');
		$query->execute(array(
			$device_id,
			$product,
			$vendor,
			$description,
			$physid,
			$bus,
			$bits,
			$irq
		));
		return $this->db->lastInsertId();
	}
	
	public function SCSIAdd($device_id,$vendor,$product,$description,$physid,$bus,$bits,$irq){
		$query = $this->db->prepare('
			insert into device_scsi
			(
				device_id,
				vendor,
				product,
				description,
				physid,
				bus,
				bits,
				irq
			) values (?,?,?,?,?,?,?,?)
		');
		$query->execute(array(
			$device_id,
			$product,
			$vendor,
			$description,
			$physid,
			$bus,
			$bits,
			$irq
		));
		return $this->db->lastInsertId();
	}
	
	public function NICAdd($device_id,$device_pci_id,$vendor,$product,$logicalname,$mac,$speed,$irq){
		$query = $this->db->prepare('
			insert into device_nics
			(
				device_id,
				device_pci_id,
				vendor,
				product,
				logicalname,
				mac,
				speed,
				irq
			) values (?,?,?,?,?,?,?,?)
		');
		$query->execute(array(
			$device_id,
			$device_pci_id,
			$vendor,
			$product,
			$logicalname,
			$mac,
			$speed,
			$irq
		));
		return $this->db->lastInsertId();
	}
	
	public function driveAdd(
		$device_id,
		$device_pci_id,
		$device_scsi_id,
		$controller,
		$vendor,
		$product,
		$device_path,
		$serial,
		$size,
		$bus,
		$physid,
		$partition_table
	){
		$query = $this->db->prepare('
			insert into device_drives
			(
				device_id,
				device_pci_id,
				device_scsi_id,
				controller,
				vendor,
				product,
				device_path,
				serial,
				bus,
				physid,
				size,
				partition_table
			) values (?,?,?,?,?,?,?,?,?,?,?,?)
		');
		$query->execute(array(
			$device_id,
			$device_pci_id,
			$device_scsi_id,
			$controller,
			$vendor,
			$product,
			$device_path,
			$serial,
			$bus,
			$physid,
			$size,
			$partition_table
		));
		return $this->db->lastInsertId();
	}
	
	public function drivePartitionAdd(
		$device_id,
		$device_drive_id,
		$parent_device_drive_partition_id,
		$segment,
		$partition_path,
		$mount_point,
		$physid,
		$serial,
		$size,
		$filesystem,
		$is_bootable
	){
		$query = $this->db->prepare('
			insert into device_drive_partitions
			(
				device_id,
				device_drive_id,
				parent_device_drive_partition_id,
				segment,
				partition_path,
				mount_point,
				physid,
				serial,
				size,
				filesystem,
				is_bootable
			) values (?,?,?,?,?,?,?,?,?,?,?)
		');
		$query->execute(array(
			$device_id,
			$device_drive_id,
			$parent_device_drive_partition_id,
			$segment,
			$partition_path,
			$mount_point,
			$physid,
			$serial,
			$size,
			$filesystem,
			$is_bootable
		));
		return $this->db->lastInsertId();
	}
	
	public function inventoryFlush($device_id){
		$this->deleteCPUs($device_id);
		$this->deleteDrivePartitions($device_id);
		$this->deleteDrives($device_id);
		$this->deleteMemoryModules($device_id);
		$this->deleteNICs($device_id);
		$this->deletePCI($device_id);
		$this->deleteSCSI($device_id);
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
	
	public function deletePCI($device_id){
		$query = $this->db->prepare('delete from device_pci where device_id = ?');
		$query->execute(array($device_id));
		return true;
	}
	
	public function deleteSCSI($device_id){
		$query = $this->db->prepare('delete from device_scsi where device_id = ?');
		$query->execute(array($device_id));
		return true;
	}
	
}