<?php

require_once(ROOT.'/lib/tasks.php');
apiAuth(req('token'));

Tasks::_get()->prime(req('task_id'),req('total_steps'),req('current_step'),req('step_desc'));
