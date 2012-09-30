<?php

require_once(ROOT.'/lib/devices.php');
require_once(ROOT.'/lib/images.php');
require_once(ROOT.'/lib/tasks.php');
require_once(ROOT.'/lib/tml.php');

apiAuth(req('token'));

$mac = req('mac');
if(!$mac) throw new Exception('Must have MAC address');

$dev = Devices::_get()->getByMAC($mac);
$dev['drives'] = Devices::_get()->getDrivesWP($dev['device_id']);
$tasks = Tasks::_get()->getByDevice($dev['device_id']);
$image = Images::_get()->get($dev['image_id']);
$image['drives'] = Images::_get()->getDrivesWPA($dev['image_id']);

echo TML::fromArray(array('response'=>array('dev'=>$dev,'image'=>$image,'tasks'=>$tasks)));
