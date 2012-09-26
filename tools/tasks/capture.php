<?php

Task::_get()->prime('Start capture',1,5);

Task::_get()->update('Mounting NTFS from master',2);
run('mount -t nfs '.SERVER.':/images /mnt');

Task::_get()->update('Priming destination',3);
run('mkdir -p /mnt/'.$_device['image']['name']);

Task::_get()->update('Checking partitions and starting capture',4);
foreach($_device['image']['partitions'] as $part){
	Task::_get()->update('Capturing partition '.$part['number'] .' from drive '.$part['drive_letter'],4);
	switch($part['filesystem']){
		case Device::FS_NTFS:
			//figure out paths
			$drive = '/dev/sd'.$part['drive_letter'].$part['number'];
			$dest = '/mnt/'.$_device['image']['name'].'/part_'.$part['drive_letter'].'_'.$part['number'].'.ntfsclone';
			//find smallest size of drive
			$out = run('ntfsresize -i '.$drive);
			preg_match('/or (\d+ \w{2})/si',$out,$m);
			if(!isset($m[1]) || empty($m[1])) throw new Exception('Could not determine smallest size of NTFS partition',ERR_PARTITION_NTFS_SIZE_MISSING);
			$size = str_replace(' ','',rtrim($m[1],'bB')); unset($out,$m);
			//resize partition
			run('echo y | ntfsresize -f -s '.$size.' '.$drive);
			//capture
			run('rm -rf '.$dest.' '.$dest.'.bz2');
			run('ntfsclone -sf -O '.$dest.' '.$drive);
			run('bzip2 '.$dest);
			//grow the partition back to its original
			run('echo y | ntfsresize -f '.$drive);
			break;
		default:
			throw new Exception('Partition filesystem unsupported',ERR_PARTITION_FS_UNSUPPORTED);
			break;
	}
}

run('umount /mnt');
Task::_get()->update('Capture complete',5);