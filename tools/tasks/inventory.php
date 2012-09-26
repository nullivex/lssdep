<?php

Task::_get()->prime('Starting inventory process',1,4);

//get inventory
Task::_get()->update('Capturing inventory from device',2);
run('lshw -xml > /tmp/hw_inventory');
$inventory = file_get_contents('/tmp/hw_inventory');
unlink('/tmp/hw_inventory');

//submit inventory
Task::_get()->update('Submitted inventory to server',3);
apiCall('inventory_submit',array('device_id'=>$_device['dev']['device_id'],'inventory'=>$inventory));

//process inventory
Task::_get()->update('Processing inventory on server',4);
apiCall('inventory_process',array('device_id'=>$_device['dev']['device_id']));