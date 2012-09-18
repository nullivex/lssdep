<?php
//output levels
define('OUT_ERR',1);
define('OUT_WARN',2);
define('OUT_STD',1);
define('OUT_VERBOSE',2);
define('OUT_INFO',3);
define('OUT_DEBUG',4);

interface UIInt {

	public static function init($type);
	public static function _get();
	public static function out($string,$level=OUT_STD,$code=null);

	// title(t) : ask a true/false question (or yes/no)
	// - q : the title string ("SomeApp v3.2.1")
	public static function title($t);

	// ask(q,a) : ask a true/false question (or yes/no)
	// - q       : the question string ("do you like grits?")
	// - a       : an array such as array('true','false') or array('y','n')
	// - default : which answer is default (optional)
	// returns true or false based on user input (a[0] is always the truth value)
	public function ask($q,$a,$default=false);

	// input(q,a) : ask for a freeform value (like HTML INPUT field)
	// - q should be the question string ("what is your username?")
	// - a if != null, is displayed as the default answer
	//     if == null, a sane default (from the pkgdef) is preselected
	//     else, displayed as the default answer (editable)
	//     on exit, set by reference to whatever string the user inputs
	public function input($q,&$a);

	// password(q,a) : ask for a freeform value but hidden (like HTML INPUT TYPE=PASSWORD field)
	// - q should be the question string ("enter your password, heathen:")
	// - a if != null, is displayed as the default answer
	//     if == null, a sane default (autogenned randomly) is preselected and unmasked
	//     else, displayed as the default answer (editable)
	//     on exit, set by reference to whatever string the user inputs
	public function password($q,&$a);

	// select(q,a) : one-of-many box (like HTML SELECT)
	// - q should be the question string ("select a flavor of syrup:")
	// - a is an array of answers like array('optiontext'=>'optionvalue')
	// returns the user-selected option value
	public function select($q,&$a,$default=0);

}

abstract class UI {
	//base user interface object
	static $inst   = false;
	public $handle = false;

	static $debug = false;

	//ui-type handling
	const TEXT    = 0;
	const TEXT_DESC = 'Text';
	const MENU = 1;
	const MENU_DESC = 'Menu';
	const HTML    = 2;
	const HTML_DESC = 'HTML';

	//screen definitions
	public $type = self::TEXT; // simplest interface type by default
	public $is_a_tty = false;
	private $backtitle = false;

	public static function init($type=self::TEXT,$title=false){
		$is_a_tty = @posix_isatty(STDOUT);
		// handle automatic fallback if we're piped
		if(($type == self::MENU) && (!$is_a_tty))
			$type = self::TEXT;
		switch($type){
			case self::TEXT:
				require_once(ROOT.'/tools/lib/ui_text.php');
				self::$inst = new UIText();
				break;
			case self::MENU:
				require_once(ROOT.'/tools/lib/ui_menu.php');
				self::$inst = new UIMenu();
				break;
			case self::HTML:
				require_once(ROOT.'/tools/lib/ui_html.php');
				self::$inst = new UIHTML();
				break;
		}
		self::$inst->type = $type;
		self::$inst->is_a_tty = $is_a_tty;
		self::$inst->basetitle = $title;
		self::$inst->_init();
	}

	public static function _get(){
		if(!is_object(self::$inst)) throw new Exception('UI has not been initialized',ERR_NOT_INITIALIZED);
		return self::$inst;
	}

	public static function switchType($type=self::TEXT){
		self::$inst->_deinit();
		self::init($type);
	}

	public function __destruct(){
		$this->_deinit(false);
	}

	public function __destruct_by_signal(){
		$this->_deinit(true);
	}

	public function __toString(){
		switch($this->type){
			case self::TEXT:	return self::TEXT_DESC;
			case self::MENU:	return self::MENU_DESC;
			case self::HTML:	return self::HTML_DESC;
		}
	}

	// out(string,err) : general output (print / echo)
	// - string : the output
	// - err    : if false, output normally (STDOUT or equivalent) [default]
	//            if true, output as error (STDERR or equivalent)
	private function _out($string,$level=OUT_STD,$code=null){
		//uses out() as implemented by the extender
		$newline = (substr($string,-1) == "\n") ? true : false;
		$a = (is_array($string)) ? $string : split("\n",$string);
		if(($a[max(array_keys($a))]=='')) unset($a[max(array_keys($a))]);
		foreach(array_keys($a) as $k)
			if($k !== max(array_keys($a))) $a[$k] .= "\n";
			else if($newline)  $a[$k] .= "\n";
		foreach($a as $line) $this->__out($line,$level,$code);
		return $this;
	}

	public static function out($string,$level=OUT_STD,$code=null){
		if(!is_object(self::$inst)) throw new Exception('UI has not been initialized',ERR_NOT_INITIALIZED);
		//replace code with undefined if its zero
		if($code === 0) $code = ERR_UNDEFINED;
		//do the output
		$rv = null;
		if(!(defined('OUT_LEVEL') && $level > OUT_LEVEL)) $rv = self::$inst->_out($string,$level,$code);
		//error lets exit
		if(!is_null($code)){
			//subtract 1000 to get a usable code
			$code = $code - 1000;
			//mark out of range codes so users know
			if($code > 255) $code = ERR_CODE_OUT_OF_RANGE - 1000;
			exit($code);
		}
		return $rv;
	}

	public static function title($t=false){
		if(!is_object(self::$inst)) throw new Exception('UI has not been initialized',ERR_NOT_INITIALIZED);
		self::$inst->basetitle = $t;
		return self::$inst;
	}

}
