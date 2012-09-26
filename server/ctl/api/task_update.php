<?php

require_once(ROOT.'/lib/tasks.php');
apiAuth(req('token'));

Tasks::_get()->update(req('task_id'),req('current_step'),req('step_desc'));
