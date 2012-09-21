<?php

//get inventory
run('lshw -xml > /tmp/hw_inventory');
$inventory = file_get_contents('/tmp/hw_inventory');
unlink('/tmp/hw_inventory');

//submit inventory
apiCall('inventory_submit',array('device_id'=>$_device['dev']['device_id'],'inventory'=>$inventory));

//process inventory
apiCall('inventory_process',array('device_id'=>$_device['dev']['device_id']));