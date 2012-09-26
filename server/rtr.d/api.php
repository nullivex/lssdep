<?php

Router::_get()->register('api',array(
        'task_check'            =>      '/ctl/api/task_check.php',
		'task_prime'			=>		'/ctl/api/task_prime.php',
		'task_update'			=>		'/ctl/api/task_update.php',
		'task_complete'			=>		'/ctl/api/task_complete.php',
		'task_error'			=>		'/ctl/api/task_error.php',
		'inventory_submit'		=>		'/ctl/api/inventory_submit.php',
		'inventory_process'		=>		'/ctl/api/inventory_process.php',
        Router::DEF             =>      '/ctl/api/no_action.php'
));