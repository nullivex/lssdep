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

//this is not a config file

class Config {

	static $inst = false;

	public $config;

	public static function _get(){
		if(self::$inst == false) self::$inst = new Config();
		return self::$inst;
	}

	public function setConfig($config){
		$this->config = $config;
	}

	public static function set($sec,$name,$value=null){
		Config::_get()->config[$sec][$name] = $value;
	}

	public static function get($sec,$name=null){
		if($name === null){
			if(!isset(Config::_get()->config[$sec])) throw new Exception("config: sec does not exist: $sec");
			return Config::_get()->config[$sec];
		} else {
			if(!isset(Config::_get()->config[$sec][$name])) throw new Exception("config: var not found: $sec,$name");
			return Config::_get()->config[$sec][$name];
		}
	}

}
