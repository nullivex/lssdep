<?php

function run($cmd,&$return=null){
	$output = '';
	$cmd = '/bin/bash -c "'.addslashes($cmd).'"';
	exec($cmd,$output,$return);
	$output = implode("\n",$output);
	dolog($cmd.': '.$output);
	return $output;
}

function dolog($msg,$output=true){
	$msg = date('m/d/y g:i:s').' -- '.$msg;
	if($output) UI::out($msg."\n",OUT_VERBOSE);
	$handle = fopen('/var/log/openlss','a');
	fwrite($handle,$msg."\n");
	fclose($handle);
}

function file_array($path,$exclude='',$recursive=false){
	$path = rtrim($path,'/') . '/';
	if(!is_dir($path)) return array(rtrim($path,'/'));
	$folder_handle = opendir($path);
	$exclude_array = array_merge(array('.','..'),explode('|',$exclude));
	$result = array();
	while(false !== ($filename = readdir($folder_handle))){
		if(!in_array($filename,$exclude_array)){
			$subpath = $path . $filename . '/';
			if(is_dir($subpath)){
				if($recursive) $result = array_merge($result,file_array($subpath,$exclude,true));
			} else {
				$result[] = $path.$filename;
			}
		}
	}
	sort($result);
	return $result;
}

function remove_dups(&$arr){
	//deal with uniques in the manifest
	$tmp = array_unique($arr,SORT_STRING);
	asort($tmp,SORT_STRING);
	$arr = array_merge($tmp);
	unset($tmp);
}

function urlname($name){
	$name = preg_replace('/\W+/',' ',strtolower($name));
	$name = preg_replace('/\s+/','-',$name);
	return $name;
}

function shortname($name){
	return preg_replace('/-/','',urlname($name));
}

function dumpvar(){
	ob_start();
	call_user_func_array('var_dump',func_get_args());
	UI::out(ob_get_clean());
}

function getStatusCode($headers){
	$status_code = false;
	if(is_scalar($headers))
		$headers = array($headers);
	if(is_array($headers))
		foreach($headers as $h){
			$m = array ();
			if(preg_match('/^HTTP\/\d\.\d\s+([0-9]{3})/',$h,$m)){
				$status_code = (int)$m[1];
				break;
			}
		}
	return $status_code;
}
