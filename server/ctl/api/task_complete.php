<?php

require_once(ROOT.'/lib/tasks.php');
apiAuth(req('token'));

Tasks::_get()->complete(req('task_id'));
