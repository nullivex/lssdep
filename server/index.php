<?php
/*
 * LSS Core
 * OpenLSS - Light, sturdy, stupid simple
 * 2010 Nullivex LLC, All Rights Reserved.
 * Bryan Tong <contact@nullivex.com>
 *
 *   OpenLSS is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   OpenLSS is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with OpenLSS.  If not, see <http://www.gnu.org/licenses/>.
 */

//made a change
//made a new change

ob_start();
session_start();
define('START',microtime(true));

//load config
$config = array();
$dir = false;
if(is_dir('conf.d')) $dir = opendir('conf.d');
if($dir){
	while($dir && ($file = readdir($dir)) !== false){
		if(in_array($file,array('.','..'))) continue;
		if(is_dir($file)) continue;
		include('conf.d/'.$file);
	}
	closedir($dir);
}
@include_once('config.php');

//set timezone
date_default_timezone_set($config['info']['default_timezone']);

//set root path
define("ROOT",$config['paths']['lss']);

//load global funcs
require_once(ROOT.'/lib/router.php');
require_once(ROOT.'/lib/url.php');

try {
	
	//load global funcs
	$dir = false;
	if(is_dir('src.d')) $dir = opendir('src.d');
	if($dir){
		$files = array();
		while($dir && ($file = readdir($dir)) !== false){
			if(in_array($file,array('.','..'))) continue;
			if(is_dir($file)) continue;
			$files[] = $file;
		}
		closedir($dir);
		sort($files);
		foreach($files as $file) include('src.d/'.$file);
	}
	
	//load error codes
	$dir = false;  $err = array();
	if(is_dir('err.d')) $dir = opendir('err.d');
	if($dir){
		while(($file = readdir($dir)) !== false){
			if(in_array($file,array('.','..'))) continue;
			if(is_dir($file)) continue;
			registerErrCodes('err.d/'.$file,$err);
		}
		closedir($dir);
	}
	unset($err);

	//init modules
	$dir = false;
	if(is_dir('init.d')) $dir = opendir('init.d');
	if($dir){
		$files = array();
		while($dir && ($file = readdir($dir)) !== false){
			if(in_array($file,array('.','..'))) continue;
			if(is_dir($file)) continue;
			$files[] = $file;
		}
		closedir($dir);
		sort($files);
		foreach($files as $file) include('init.d/'.$file);
	}

	//router
	Router::init();
	Router::_get()->setDefault('ctl/home.php');
	$dir = false;
	if(is_dir('rtr.d')) $dir = opendir('rtr.d');
	if($dir){
		while(($file = readdir($dir)) !== false){
			if(in_array($file,array('.','..'))) continue;
			if(is_dir($file)) continue;
			include('rtr.d/'.$file);
		}
		closedir($dir);
	}
	require(Router::_get()->route(req('act'),req('do'),req('fire')));

} catch(Exception $e){
	sysError($e->getMessage());
}
