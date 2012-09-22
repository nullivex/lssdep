<?php

function live(){
	UI::out("Welcome to LSSDep live\n");
	UI::out("Checking for task, will reboot if no task is found\n");
	$mac = getMAC(NETDEV);
	UI::out("Found MAC Address: $mac\n");
	$_device = array_shift(TML::toArray(apiCall('task_check',array('mac'=>$mac))));
	if(!isset($_device['tasks'])) throw new Exception('No tasks to be run');
	foreach($_device['tasks'] as $task){
		switch($task['type']){
			case 'inventory':
				require(ROOT.'/tasks/inventory.php');
				break;
			default:
				throw new Exception('Task type unsupported: '.$task['type']);
				break;
		}
	}
}

function getMAC($dev){
	$out = run('ip link show '.$dev);
	preg_match('/link\/ether ([0-9a-z:]+) brd/i',$out,$m);
	if(!isset($m[1]) || empty($m[1])) throw new Exception('Could not find MAC address for: '.$dev,ERR_MAC_NOT_FOUND);
	return trim($m[1]);
}

function apiCall($call,$vars=array()){
	//add token
	$api_url = 'http://'.SERVER.'/server/index.php?act=api&do=';
	if(!isset($vars['token']) || empty($vars['token'])) $vars['token'] = TOKEN;
	switch($call){
		case 'task_check':
		case 'inventory_submit':
		case 'inventory_process':
			return _apiCall($api_url.$call,$vars);
			break;
		default:
			throw new Exception('API call not supported: '.$call,ERR_API_CALL_UNSUPPORTED);
			break;
	}
	return false;
}

function _apiCall($url,$vars=array(),$post=true){
	if(DEBUG) UI::out("\n\n\n====== API Call ======\nURL: $url\nDATA: ".print_r($vars,true)."\n\n");
	$ch = curl_init($url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
	if($post){
		curl_setopt($ch,CURLOPT_POST,true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);
	} else {
		curl_setopt($ch,CURLOPT_URL,$url.'&'.http_build_query($vars));
	}
	$out = curl_exec($ch);
	if(DEBUG) UI::out("=== API RESPONSE ===\n\n".$out."\n\n====== END API CALL ======\n$url\n\n\n");
	return $out;
}
