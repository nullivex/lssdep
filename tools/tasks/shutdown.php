<?php

Task::_get()->prime('Initiating shutdown',1,1);
if(OUT_LEVEL < OUT_DEBUG) run('shutdown -h now');
else UI::out('Would have shutdown the machine if not in debug mode',OUT_DEBUG);