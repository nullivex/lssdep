<?php

define('ROOT',dirname(dirname(__FILE__)));
chdir(ROOT);

//load global src files
require_once(ROOT.'/src/err.php');
require_once(ROOT.'/src/func.php');
require_once(ROOT.'/src/mda.php');

//load the User Interface
$opts = getopt('vq');
//setup output level
define('OUT_LEVEL',(1 + count((array) mda_get($opts,'v')) - count((array) mda_get($opts,'q'))));

//setup ui
UI::init();

//setup exception handling
function sysError($e){
	$msg = 'ERROR['.$e->getCode().'@'.$e->getFile().':'.$e->getLine().']: '.$e->getMessage()."\n";
	UI::out($msg,true,$e->getCode());
}
set_exception_handler('sysError');
