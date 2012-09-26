#!/usr/bin/php
<?php

//boot
require_once('src/boot.php');

//require
require_once(ROOT.'/src/func_lssdep.php');
require_once(ROOT.'/lib/device.php');
require_once(ROOT.'/lib/tml.php');

$lo = array(
	'live',		//start the live system
	'netdev:',	//network interface to use
	'server:',	//address of the control server
	'token:',	//token to communicate with the control server
	'debug',	//turn on debug mode
);
$opts = getopt('',$lo);

//define system constants
define('SERVER',mda_get($opts,'server'));
define('NETDEV',mda_get($opts,'netdev'));
define('TOKEN',mda_get($opts,'token'));
if(!SERVER || !NETDEV || !TOKEN)
	throw new Exception('Missing required startup params: server,netdev,token',ERR_STARTUP_PARAMS_MISSING);

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
