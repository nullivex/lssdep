#!/usr/bin/php
<?php

require_once('src/boot.php');

$lo = array(
	'live',		//start the live system
);
$opts = getopt('',$lo);

foreach(array_keys($opts) as $act){
	switch($act){
		case 'live':
			live();
			exit;
			break;
		default:
			continue;
			break;
	}
}

throw new Exception('No action supplied',ERR_NO_ACTION);
