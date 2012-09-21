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

class Tpl {

	static $inst = false;

	protected $constants;
	protected $lang;
	protected $path;
	protected $theme;
	protected $body;
	protected $tpl;
	protected $file_ext = '.tpl.php';
	protected $holder = 'tpl';
	protected $theme_path;

	protected function __construct(){
		//Void
	}

	public static function _get(){
		if(self::$inst === false) self::$inst = new Tpl();
		return self::$inst;
	}

	public function setPath($value){
		$this->path = $value;
		return $this;
	}

	public function setThemePath($path){
		$this->theme_path = $path;
		return $this;
	}

	public function setConstant($name,$value,$overwrite=true){
		if(isset($this->constants[$name]) && $overwrite === false) return false;
		$this->constants[$name] = $value;
		return true;
	}

	public function setLang($name,$value){
		$name = "lang_".$name;
		$this->lang[$name] = $value;
	}

	public function setConstants($constants=array(),$overwrite=true){
		if(!is_array($constants)) return $this;
		foreach($constants AS $name => $value) $this->setConstant($name,$value,$overwrite);
		return $this;
	}

	public function getConstant($name){
		if(!isset($this->constants[$name])) return false;
		return $this->constants[$name];
	}

	public function getConstants(){
		return $this->constants;
	}

	public function getLang($name){
		$names = "lang_".$name;
		if(!isset($this->lang[$name])) return false;
		return $this->lang[$name];
	}

	public function addToBody($html){
		$this->body .= $html;
	}

	public function resetBody(){
		$this->body = '';
	}

	public function parse($file,$section,$tags=array(),$return=false){
		$this->load_file($file);
		if(!isset($this->tpl[$file][$section])) return false;
		$data = $this->tpl[$file][$section];
		//Replace Tags
		if(is_array($tags)){
			foreach($tags AS $tag => $value){
				$tag = strval($tag);
				if(empty($tag)) continue;
				$value = strval($value);
				$data = str_ireplace('{'.$tag.'}',$value,$data);
			}
		}
		if($return) return $data;
		$this->body .= $data;
		return $this;
	}

	protected function load_file($file){
		if(isset($this->tpl[$file])) return;
		include($this->path.'/'.$file.$this->file_ext);
		$holder = $this->holder;
		if(!isset($$holder)) $this->tpl[$file] = array();
		else $this->tpl[$file] = $$holder;
		unset($tpl);
	}

	protected function parseConstants(){
		$this->setConstants(Url::_all());
		if(!is_array($this->constants)) return false;
		foreach($this->constants AS $tag => $value){
			$tag = strval($tag);
			if(empty($tag)) continue;
			$value = strval($value);
			$this->body = str_ireplace('{'.$tag.'}',$value,$this->body);
		}
	}

	public function initConstants(){

		//Template Constants
		$this->setConstant('site_name',Config::get('info','site_name'));
		$this->setConstant('uri',Config::get('url','uri'));
		$this->setConstant('url',Config::get('url','url'));
		$this->setConstant('theme',$this->theme_path);
		$this->setConstant('cur_year',date('Y'));
		$this->setConstant('css',$this->theme_path.'/css');
		$this->setConstant('js',Config::get('url','uri').'/js');
		$this->setConstant('img',$this->theme_path.'/img');
		$this->setconstant('alert','');

		//set delayed alerts
		if(session('delayed_alert')){
			$this->setConstant('alert',$this->getConstant('alert').session('delayed_alert'));
			session('delayed_alert','');
		}

	}

	public function stats(){
		$end = microtime(true);
		$time = number_format(($end - START),5);
		if(class_exists('Db')) $db_stats = ' | Queries: '.Db::_get()->getQueryCount();
		else $db_stats = '';
		$this->setConstant('script_stats',
			'Execution: '.$time.
			$db_stats.
			' | Memory: '.number_format((memory_get_usage()/1024/1024),2).'MB'
		);
	}

	public function output(){
		$this->stats();
		$this->parseConstants();
		$this->parseConstants(); //2nd pass for const in const
		$body = $this->body;
		$this->resetBody();
		return trim($body);
	}

}


