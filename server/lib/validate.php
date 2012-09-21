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

class Validate {

	static $inst;
	public $data;
	public $var;
	public $error = false;

	public static function prime($data){
		self::$inst = new Validate($data);
	}

	public static function go($var){
		return self::_get()->setVar($var);
	}

	public static function _get(){
		return self::$inst;
	}

	public function __construct($data){
		$this->data = $data;
		$this->error = false;
	}

	public function error($err){
		$this->error .= $err."\n";
		return $this;
	}

	public function setVar($var){
		$this->var = $var;
		return $this;
	}

	public function get(){
		if(isset($this->data[$this->var])) return $this->data[$this->var];
		return null;
	}

	public function min($min){
		if(strlen($this->get())<$min) $this->error($this->var.' is shorter than '.$min);
		return $this;
	}

	public function max($max){
		if(strlen($this->get())>$max) $this->error($this->var.' is longer than '.$max);
		return $this;
	}

	public function not($type){
		if(self::type($type) === true) $this->error($this->var.' is '.$type);
		return $this;
	}

	public function is($type){
		if(self::type($type) !== true) $this->error($this->var.' is not '.$type);
		return $this;
	}

	public function type($type){
		switch($type){
			case 'blank':
				if($this->get() == '') return true;
				break;
			case 'empty':
				$val = $this->get();
				if(empty($val)) return true;
				break;
			case 'null':
				if(is_null($this->get())) return true;
				break;
			case 'ip':
				if(preg_match('/^[0-9\.]*$/',$this->get())) return true;
				break;
			case 'mac':
				if(preg_match('/^[0-9a-z:]*$/',$this->get())) return true;
				break;
			case 'domain':
				if(preg_match('/^[0-9a-z\-\.]*$/i',$this->get())) return true;
				break;
			case 'num':
				if(preg_match('/^[0-9]*$/',$this->get())) return true;
				break;
			case 'alpha':
			case 'al':
				if(preg_match('/^[0-9a-z]*$/i',$this->get())) return true;
				break;
			case 'als':
			case 'alnums':
			case 'en':
				if(preg_match('/^[0-9a-z\s\-"]*$/i',$this->get())) return true;
				break;
			case 'email':
				if(preg_match('/^.+?\@[0-9a-z\-\.]+$/i',trim($this->get()))) return true;
				break;
		}
		return $this;
	}

	public static function paint(){
		if(self::$inst->error !== false) throw new Exception(nl2br(self::$inst->error));
		return true;
	}

}
