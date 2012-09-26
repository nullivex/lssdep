<?php

require_once(ROOT.'/lib/devices.php');
require_once(ROOT.'/lib/tasks.php');
require_once(ROOT.'/lib/tml.php');

apiAuth(req('token'));

$mac = req('mac');
if(!$mac) throw new Exception('Must have MAC address');

$dev = Devices::_get()->getByMAC($mac);
$tasks = Tasks::_get()->getByDevice($dev['device_id']);

echo TML::fromArray(array('response'=>array('dev'=>$dev,'tasks'=>$tasks)));
