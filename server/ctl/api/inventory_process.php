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
		case 'scsi':
			processNICs($device_id,$el);
			processDrives($device_id,$el);
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

function processNICs($device_id,$xml){
	foreach($xml->xpath('node') as $el){
		if($el->node->count() === 0) continue;
		$e = $el->node;
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
		Devices::_get()->NICAdd($device_id,$vendor,$product,$logicalname,$mac,$speed,$irq);
	}
}

function processDrives($device_id,$xml){
	foreach($xml->xpath('node') as $el){
		if($el->node->count() === 0) continue;
		$e = $el->node;
		$id = $e->attributes(); $id = $id['id'];
		if($id != 'disk') continue;
		//start adding disk
		$controller = $vendor = $product = $device_path = $serial = $size = $bus = $physid = null;
		$controller = (string) $el->product;
		$vendor = (string) $e->vendor;
		$product = (string) $e->product;
		$device_path = (string) $e->logicalname;
		$serial = (string) $e->serial;
		$size = (int) $e->size;
		$bus = (string) $e->businfo;
		$physid = (string) $e->physid;
		Devices::_get()->driveAdd($device_id,$controller,$vendor,$product,$device_path,$serial,$size,$bus,$physid);
	}
}
