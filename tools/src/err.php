<?php
foreach(array(
 1001=>'ERR_UNDEFINED'
,1002=>'ERR_NO_ACTION'
,1003=>'ERR_MAC_NOT_FOUND'
,1004=>'ERR_STARTUP_PARAMS_MISSING'
,1005=>'ERR_TML_PARSE_FAILED'
) as $k=>$v)
	define($v,(int)$k);
