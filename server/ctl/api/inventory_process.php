<?php

require_once(ROOT.'/lib/devices.php');
require_once(ROOT.'/lib/xml2array.php');

$device = Devices::_get()->get(post('device_id'));

//load xml into object
$xml = XML2Array::createArray($device['inventory_raw']);

var_dump($xml);