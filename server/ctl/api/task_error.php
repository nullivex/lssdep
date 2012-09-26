<?php

require_once(ROOT.'/lib/tasks.php');
apiAuth(req('token'));

Tasks::_get()->error(req('task_id'),req('err_msg'),req('err_code'));
