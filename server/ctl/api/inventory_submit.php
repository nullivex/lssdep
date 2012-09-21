<?php

require_once(ROOT.'/lib/devices.php');

Devices::_get()->inventoryRawUpdate(post('device_id'),post('inventory'));
echo 'success';