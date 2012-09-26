<?php

Task::_get()->prime('Initiating reboot',1,1);
if(OUT_LEVEL < OUT_DEBUG) run('reboot');
else UI::out('Would have rebooted the machine if not in debug mode',OUT_DEBUG);
