<?php

class UIText extends UI implements UIInt {

	protected function _init(){
	// needs to be here even if empty
		if($this->basetitle !== false && OUT_LEVEL > OUT_STD) $this->out($this->basetitle."\n");
		if($this::$debug) $this->out("[UIText init()]\n");
	}

	protected function _deinit(){
	// needs to be here even if empty
		if($this::$debug) $this->out("[UIText deinit()]\n");
	}

	public function __out($string,$level=OUT_STD,$code=null){
		fwrite(!is_null($code)?STDERR:STDOUT,$string);
		fflush(!is_null($code)?STDERR:STDOUT);
	}

	public function ask($q,$a,$default=false){
		$question = $q.' ('.$a[0].'/'.$a[1].'): ';
		$a[0] = ($default) ? strtoupper($a[0]) : strtolower($a[0]);
		$a[1] = ($default) ? strtolower($a[1]) : strtoupper($a[1]);
		$b = array(strtolower(substr($a[0],0,1)),strtolower(substr($a[1],0,1)));
		$this->out($question);
		$in = ($this->is_a_tty) ? null : $b[($default)?0:1];
		while(is_null($in)){
			$in = strtolower(substr(trim(fgets(STDIN)),0,1));
			if($in == '') return $default;
			if(!in_array($in,$b)){
				$in = null;
				if($this->is_a_tty) $this->out($question);
			}
		}
		if(!$this->is_a_tty) $this->out($in."\n");
		if($in !== $b[0]) return false;
		return true;
	}

	public function input($q,&$a){
		$command = "/usr/bin/env bash -c 'echo OK'";
		if (rtrim(shell_exec($command)) !== 'OK') {
			trigger_error("Can't invoke bash");
			return;
		}
		$command = "/usr/bin/env bash -c 'read -s \""
			. addslashes($prompt)
			. "\" myinput && echo \$myinput'";
		$val = rtrim(shell_exec($command));
		echo "\n";
		return $val;
	}

	public function password($q,&$a){
		$command = "/usr/bin/env bash -c 'echo OK'";
		if (rtrim(shell_exec($command)) !== 'OK') {
			trigger_error("Can't invoke bash");
			return;
		}
		$command = "/usr/bin/env bash -c 'read -s -p \""
			. addslashes($prompt)
			. "\" mypassword && echo \$mypassword'";
		$val = rtrim(shell_exec($command));
		echo "\n";
		return $val;
	}

	public function select($q,&$a,$default=0){
		//TODO: this.
	}

}
