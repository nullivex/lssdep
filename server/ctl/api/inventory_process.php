<?php

require_once(ROOT.'/lib/devices.php');
require_once(ROOT.'/lib/xml2array.php');

$device = Devices::_get()->getFull(post('device_id'));
$device_id = $device['device_id'];

//flush the inventory and start new
Devices::_get()->inventoryFlush($device['device_id']);

//maybe some simplexml will help
$xml = new SimpleXMLElement($device['inventory_raw']);

//update motherboard
processMotherboard($device_id,$xml);

//grab children of mother board
foreach($xml->xpath('node/node') as $el){
	$id = $el->attributes(); $id = (string) $id['id'];
	switch($id){
		case 'cpu':
			processCPUs($device_id,$el);
			break;
		case 'memory':
			processMemory($device_id,$el);
			break;
		case 'pci':
			processPCI($device_id,$el);
			break;
		case 'serial':
			processSCSI($device_id,$el);
			break;
		default:
			continue;
			break;
	}
}

function processMotherboard($device_id,$xml){
	$uuid = $product = $serial = null;
	foreach($xml->configuration->children() as $el){
		if($el['id'] == 'uuid') $uuid = (string) $el['value'];
	}
	$vendor = (string) $xml->node->vendor;
	$product = (string) $xml->node->product;
	$serial = (string) $xml->node->serial;
	Devices::_get()->motherboardUpdate($device_id,$uuid,$vendor,$product,$serial);
	return null;
}

function processCPUs($device_id,$xml){
	$bus = $vendor = $product = $cores = $bits = $speed = $cache = $capabilities = null;
	$bus = (string) $xml->businfo;
	$vendor = (string) $xml->vendor;
	$product = (string) $xml->product;
	$cores = (string) $xml->physid;
	$bits = (string) $xml->width;
	$speed = (string) $xml->size;
	foreach($xml->xpath('node') as $el){
		if(strpos($el['id'],'cache') !== 0) continue;
		else $cache += (int) $el->size;
	}
	foreach($xml->capabilities->children() as $el) $capabilities .= $el['id'].' ';
	Devices::_get()->cpuAdd($device_id,$bus,$vendor,$product,$cores,$speed,$bits,$cache,trim($capabilities));
	return null;
}

function processMemory($device_id,$xml){
	foreach($xml->xpath('node') as $el){
		if(stripos($el->description,'[empty]') !== false) continue;
		$vendor = $product = $description = $serial = $slot = $size = $bits = $clock = null;
		$vendor = (string) $el->vendor;
		$product = (string) $el->product;
		$description = (string) $el->description;
		$serial = (string) $el->serial;
		$slot = (string) $el->slot;
		$size = (int) $el->size;
		$bits = (int) $el->width;
		$clock = (int) $el->clock;
		Devices::_get()->memoryAdd($device_id,$vendor,$product,$description,$serial,$slot,$size,$bits,$clock);
	}
	return null;
}

function processPCI($device_id,$xml){
	foreach($xml->xpath('node') as $el){
		$vendor = $product = $description = $physid = $bus = $bits = $irq = null;
		$vendor = (string) $el->vendor;
		$product = (string) $el->product;
		$description = (string) $el->description;
		$physid = (string) $el->physid;
		$bus = (string) $el->businfo;
		$bits = (int) $el->width;
		if($el->resources->count()){
			foreach($el->resources->children() as $resource){
				if(strpos($resource['type'],'irq') !== false) $irq = (int) preg_replace('/\D+/','',$resource['type']);
			}
		}
		$device_pci_id = Devices::_get()->PCIAdd($device_id,$vendor,$product,$description,$physid,$bus,$bits,$irq);
		//add any drives or nics that are hanging on this PCI device
		processNICs($device_id,$device_pci_id,$el);
		processDrives($device_id,$device_pci_id,null,$el);
	}
}

function processSCSI($device_id,$xml){
	foreach($xml->xpath('node') as $el){
		$vendor = $product = $description = $physid = $bus = $bits = $irq = null;
		$vendor = (string) $el->vendor;
		$product = (string) $el->product;
		$description = (string) $el->description;
		$physid = (string) $el->physid;
		$bus = (string) $el->businfo;
		$bits = (int) $el->width;
		if($el->resources->count()){
			foreach($el->resources->children() as $resource){
				if(strpos($resource['type'],'irq') !== false) $irq = (int) preg_replace('/\D+/','',$resource['type']);
			}
		}
		$device_scsi_id = Devices::_get()->SCSIAdd($device_id,$vendor,$product,$description,$physid,$bus,$bits,$irq);
		//add any drives that are hanging on this SCSI device
		processDrives($device_id,null,$device_scsi_id,$el);
	}
}

function processNICs($device_id,$device_pci_id,$el){
	if(!$el->node->count()) return;
	foreach($el->xpath('node') as $e){
		$id = $e->attributes(); $id = $id['id'];
		if($id != 'network') continue;
		//start adding nic
		$product = $vendor = $logicalname = $mac = $speed = $irq = null;
		$product = (string) $e->product;
		$vendor = (string) $e->vendor;
		$logicalname = (string) $e->logicalname;
		$mac = (string) $e->serial;
		$speed = (int) $e->size;
		foreach($e->resources->children() as $resource){
			if(strpos($resource['type'],'irq') !== false) $irq = (int) preg_replace('/\D+/','',$resource['type']);
		}
		Devices::_get()->NICAdd($device_id,$device_pci_id,$vendor,$product,$logicalname,$mac,$speed,$irq);
	}
}

function processDrives($device_id,$device_pci_id,$device_scsi_id,$el){
	if(!$el->node->count()) return;
	foreach($el->xpath('node') as $e){
		$id = $e->attributes(); $id = $id['id'];
		if($id != 'disk') continue;
		//start adding disk
		$controller = $vendor = $product = $device_path = $serial = $size = $bus = $physid = null;
		$partition_table = Devices::DRIVE_PART_TYPE_NONE;
		$controller = (string) $el->product;
		$vendor = (string) $e->vendor;
		$product = (string) $e->product;
		$device_path = (string) $e->logicalname;
		$serial = (string) $e->serial;
		$size = (int) $e->size;
		$bus = (string) $e->businfo;
		$physid = (string) $e->physid;
		//figureout partition table type
		foreach($e->capabilities->children() as $capability){
			if(strpos($capability['id'],'dos') !== false){
				$partition_table = Devices::DRIVE_PART_TYPE_MSDOS;
				break;
			}
			if(strpos($capability['id'],'gpt') !== false){
				$partition_table = Devices::DRIVE_PART_TYPE_GPT;
				break;
			}
		}
		$device_drive_id = Devices::_get()->driveAdd(
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
		);
		//process partitions
		foreach($e->xpath('node') as $p){
			//primary partitions are at this level
			$device_drive_partition_id = partitionAddPrimary($device_id,$device_drive_id,$p);
			//see if there are logical partitions
			if($p->node->count()){
				//logical partitions would be here
				foreach($p->xpath('node') as $l){
					partitionAddLogical($device_id,$device_drive_id,$device_drive_partition_id,$l);
				}
			}
		}
	}
}

function partitionAddPrimary($device_id,$device_drive_id,$p){
	$segment = Devices::DRIVE_SEGMENT_PRIMARY;
	$is_bootable = 0;
	$filesystem = Devices::DRIVE_FS_NONE;
	$physid = $serial = $size = $partition_path = $mount_point = null;
	$physid = (string) $p->physid;
	$serial = (string) $p->serial;
	$size = (int) $p->capacity;
	$names = $p->xpath('logicalname');
	if(isset($names[0])) $partition_path = $names[0];
	if(isset($names[1])) $mount_point = $names[1];
	if($p->configuration->count()){
		foreach($p->configuration->children() as $c){
			if($c['id'] != 'filesystem') continue;
			$filesystem = $c['value'];
		}
	}
	if($p->capabilities->count()){
		foreach($p->capabilities->children() as $c){
			//check if we have swap
			if($c['id'] == 'nofs' && stripos($p->description,'swap') !== false){
				$filesystem = Devices::DRIVE_FS_SWAP;
				break;
			}
			if($c['id'] != 'bootable') continue;
			$is_bootable = 1;
		}
	}
	return Devices::_get()->drivePartitionAdd(
		$device_id,
		$device_drive_id,
		null,
		$segment,
		$partition_path,
		$mount_point,
		$physid,
		$serial,
		$size,
		$filesystem,
		$is_bootable
	);
}

function partitionAddLogical($device_id,$device_drive_id,$device_drive_partition_id,$p){
	$segment = Devices::DRIVE_SEGMENT_LOGICAL;
	$is_bootable = 0;
	$filesystem = Devices::DRIVE_FS_NONE;
	$physid = $serial = $size = $partition_path = $mount_point = null;
	$physid = (string) $p->physid;
	$size = (int) $p->capacity;
	$names = $p->xpath('logicalname');
	if(isset($names[0])) $partition_path = $names[0];
	if(isset($names[1])) $mount_point = $names[1];
	if($p->configuration->count()){
		foreach($p->configuration->children() as $c){
			//check if we have swap
			if($c['id'] == 'nofs' && stripos($p->description,'swap') !== false){
				$filesystem = Devices::DRIVE_FS_SWAP;
				break;
			}
			if($c['id'] != 'mount.fstype') continue;
			$filesystem = $c['value'];
		}
	}
	return Devices::_get()->drivePartitionAdd(
		$device_id,
		$device_drive_id,
		$device_drive_partition_id,
		$segment,
		$partition_path,
		$mount_point,
		$physid,
		$serial,
		$size,
		$filesystem,
		$is_bootable
	);
}
