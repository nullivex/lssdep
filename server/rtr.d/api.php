<?php

Router::_get()->register('api',array(
        'task_check'            =>      '/ctl/api/task_check.php',
		'inventory_submit'		=>		'/ctl/api/inventory_submit.php',
		'inventory_process'		=>		'/ctl/api/inventory_process.php',
        Router::DEF             =>      '/ctl/api/no_action.php'
));