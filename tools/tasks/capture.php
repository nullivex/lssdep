<?php

Task::_get()->prime('Start capture',1,5);

Task::_get()->update('Mounting NTFS from master',2);
run(sprintf('mount -t nfs %s:/images /mnt',SERVER));

Task::_get()->update('Priming destination',3);
run(sprintf('mkdir -p /mnt/%s',$_device['image']['name']));

Task::_get()->update('Checking partitions and starting capture',4);
foreach($_device['image']['drives'] as $drive){
	//capture mbr
	run(sprintf('dd if=%s of=/mnt/%s/drive-%d_mbr bs=1 skip=0 count=445',$drive['path'],$_device['image']['name'],$drive['image_drive_id']));
	//capture partitions
	foreach($drive['partitions'] as $part){
		Task::_get()->update('Capturing partition '.$part['number'] .' from drive '.$drive['path'],4);
		switch($part['filesystem']){
			case Device::FS_NTFS:
				//figure out paths
				$dest = sprintf(
					'/mnt/%s/drive-%d_part-%d_%s',
					$_device['image']['name'],
					$drive['image_drive_id'],
					$part['image_drive_partition_id'],
					$part['filesystem']
				);
				//find smallest size of drive
				$out = run(sprintf('ntfsresize -f -i %s%d',$drive['path'],$part['number']));
				preg_match('/or (\d+ \w{2})/si',$out,$m);
				if(!isset($m[1]) || empty($m[1])) throw new Exception('Could not determine smallest size of NTFS partition',ERR_PARTITION_NTFS_SIZE_MISSING);
				list($size,$delim) = explode(' ',rtrim($m[1],'bB')); unset($out,$m);
				//add to the smaller size so there is room for windows stuff
				switch($delim){
					case 'M':
						$size += 500;
						break;
					case 'G':
						$size += 1;
						break;
					default:
						break;
				}
				//resize partition
				run(sprintf('ntfsfix %s%d',$drive['path'],$part['number']));
				run(sprintf('echo y | ntfsresize -f -s %s%s %s%d',$size,$delim,$drive['path'],$part['number']));
				//capture
				run(sprintf('ntfsclone -f --overwrite %s -s %s%d',$dest,$drive['path'],$part['number']));
				//grow the partition back to its original
				run(sprintf('ntfsfix %s%d',$drive['path'],$part['number']));
				run(sprintf('echo y | ntfsresize -f %s%d',$drive['path'],$part['number']));
				break;
			default:
				throw new Exception('Partition filesystem unsupported',ERR_PARTITION_FS_UNSUPPORTED);
				break;
		}
	}
}

run('umount /mnt');
Task::_get()->update('Capture complete',5);
