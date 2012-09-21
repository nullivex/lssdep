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

define('inc','/');

class Url {

	static $urls = array(
		'home',
		'page',
		'register',
		'login',
		'logout',
		'profile'
	);

	public static function prep(){
		return Config::get('url','url');
	}

	public static function _all(){
		$urls = array();
		foreach(self::$urls as $func){
			$urls['url_'.$func] = self::$func();
		}
		return $urls;
	}

	public static function home(){
		return self::prep().inc.'index.php';
	}

	public static function register(){
		return self::prep().inc.'index.php?act=register';
	}

	public static function login(){
		return self::prep().inc.'index.php?act=login';
	}

	public static function logout(){
		return self::prep().inc.'index.php?logout=true';
	}

	public static function page(){
		return self::prep().inc.'index.php?act=pages&page=';
	}

	public static function profile(){
		return self::prep().inc.'index.php?act=profile';
	}

}
