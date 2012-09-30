<?php

Task::_get()->prime('Starting deploy',1,5);

Task::_get()->update('Preparing drives',2);
if($_device['dev']['is_wipe']){
	//wipe ALL physical drives
	foreach($_device['dev']['drives'] as $drive){
		Task::_get()->update('Wiping drive '.$drive['device_path'],2);
		run(sprintf('dd if=/dev/zero of=%s bs=1M count=100',$drive['device_path']));
	}
	//make partition tables where needed
	foreach($_device['image']['drives'] as $drive){
		Task::_get()->update('Setting up partition table ('.$drive['part_table'].') on '.$drive['path'],2);
		run(sprintf('parted -s %s mktable %s',$drive['path'],$drive['part_table']));
	}
}

Task::_get()->update('Creating partition(s) on layer 1 drives',3);
foreach($_device['image']['drives'] as $drive){
	if($drive['layer'] != 1) continue;
	foreach($drive['partitions'] as $part){
		//remove the partition if it exists
		run(sprintf('parted -s %s rm %d',$drive['path'],$part['number']));
		//create the partition
		if($part['is_grow']){
			$out = run(sprintf('parted -s %s mkpart %s %s %s %s',$drive['path'],$part['type'],$part['filesystem'],$part['start'],$part['end']));
			//if there is an error re-run with the acceptable size
			if(stripos($out,'error') !== false){
				//grab the erroneous output
				preg_match('/manage is [0-9a-z\.]+? to ([0-9a-z\.]+?)\.$/si',$out,$m);
				//fail if we cant grow
				if(!isset($m[1]) || empty($m[1])) throw new Exception('Could not grow partition, couldnt get maximum size: '.$out);
				else $end = $m[1];
				unset($m);
				//re-run with the proper ending size
				$out = run(sprintf('parted -s %s mkpart %s %s %s %s',$drive['path'],$part['type'],$part['filesystem'],$part['start'],$end));
			}
		} else {
			//make the new partition
			$out = run(sprintf('parted -s %s mkpart %s %s %s %s',$drive['path'],$part['type'],$part['filesystem'],$part['start'],$part['end']));
		}
		//check for error making partition
		if(stripos($out,'error') !== false) throw new Exception('Creation of partition failed: '.$out);
		//handle flags
		foreach(explode(',',trim($part['flags'])) as $flag){
			$out = run(sprintf('parted -s %s set %d %s on',$drive['path'],$part['number'],$flag));
			if(stripos($out,'error') !== false) throw new Exception('Setting partition flag '.$flag.' failed: '.$out);
		}
	}
}

//TODO: Come back to this section after testing
/*
Task::_get()->update('Creating layer 2 drives if any',4);
foreach($_device['image_drives'] as $drive){
	if($drive['layer'] != 2) continue;
	switch($drive['type']){
		case Device::DRIVE_RAID:
			Task::_get()->update('Creating RAID device '.$drive['path'],4);
			//TODO: setup raid
			break;
		case Device::DRIVE_LVM:
			Task::_get()->update('Creating LVM '.$drive['path'],4);
			//TODO: setup lvm
			break;
		case Device::DRIVE_RAW:
		default:
			//nothing
			continue;
			break;
	}
}


Task::_get()->update('Creating layer 2 partitions if any',5);
foreach($_device['image_drives'] as $drive){
	if($drive['layer'] != 2) continue;
	foreach($drive['partitions'] as $part){
		//TODO: create partitions
	}
}
*/

Task::_get()->update('Extracting images to partitions',6);
foreach($_device['image']['drives'] as $drive){
	foreach($drive['partitions'] as $part){
		switch($part['filesystem']){
			case Device::FS_NTFS:
				//this will download the partition from the server and stream it directly to the drive
				run(sprintf(
					'wget -O - http://%s/images/%s/drive-%d_part-%d_%s | ntfsclone --overwrite %s%d --restore-image -',
					SERVER,
					$_device['image']['name'],
					$drive['image_drive_id'],
					$part['image_drive_partition_id'],
					$part['filesystem'],
					$drive['path'],
					$part['number']
				));
				//resize the partition
				run(sprintf('ntfsresize -f %s%d',$drive['path'],$part['number']));
				break;
			default:
				throw new Exception('Image partition filesystem unsupport: '.$part['filesystem']);
				break;
		}
	}
}

Task::_get()->update('Install MBR where needed',7);
foreach($_device['image']['drives'] as $drive){
	//this will stream the MBR directly to the drive
	run(sprintf(
		'wget -O - http://%s/images/%s/drive-%d_mbr | dd of=%s bs=1 seek=0 count=445',
		SERVER,
		$_device['image']['name'],
		$drive['image_drive_id'],
		$drive['path']
	));
}

//this task is complete