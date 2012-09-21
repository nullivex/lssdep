<?php

class Router {

	static $inst = null;

	const DEF = '__default__';

	private $default = null;
	private $apps = array();

	public static function init(){
		if(is_null(self::$inst)) self::$inst = new Router();
	}

	public static function _get(){
		if(!is_object(self::$inst)) throw new Exception('Router object not initialized');
		return self::$inst;
	}

	public function setDefault($dest){$this->default = $dest; return $dest;}

	public function register($act,$do=array()){
		$this->apps[$act] = $do;
	}

	public function route($act=null,$do=null,$fire=null){
		$keys = array($act,$do,$fire); $i=0; $dest = null;
		do {
			$dest = $this->doRoute($keys[$i],$dest);
			$i++;
			if($i > 2 && is_array($dest)) throw new Exception('Routing cannot pass 3 levels');
		} while(is_array($dest));
		if(is_null($dest)) return $this->default;
		$dest = ROOT.$dest;
		if(is_string($dest) && file_exists($dest)) return $dest;
		else throw new Exception('Could not route request');
	}

	protected function doRoute($key,$arr=null){
		if(is_null($arr)) $arr = $this->apps;
		foreach($arr as $act => $val){
			if($act !== $key) continue;
			return $val;
		}
		if(isset($arr[Router::DEF])) return ROOT.$arr[Router::DEF];
		return null;
	}

}
