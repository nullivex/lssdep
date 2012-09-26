<?php

class Task {

	public $err = false;
	public $device = false;
	public $task = false;
	public $cur_step = 1; 
	public $total_steps = 1;
	
	static $inst = false;

	public static function run($task,&$_device){
		if(is_null($task['type'])) return;
		$file = ROOT.'/tasks/'.$task['type'].'.php';
		if(!file_exists($file)) throw new Exception('Task type unsupported: '.$task['type'],ERR_TASK_UNSUPPORTED);
		//register task handler
		self::$inst = new Task($task,$_device);
		try {
			//require the controller file and let it handle the action
			require($file);
		} catch(Exception $e){
			//set error to true so the task wont report completed
			//instead it will update with the error message
			self::_get()->err = $e;
			//rethrow the exception so the system can pick it up
			throw $e;
		}
		//unregister task handler (this completes the task)
		self::$inst = false;
	}
	
	public static function _get(){
		if(is_object(self::$inst)) return self::$inst;
		throw new Exception('No task has been initiated',ERR_TASK_UNINITIATED);
	}
	
	private function __construct($task,$device){
		$this->task = $task;
		$this->device = $device;
	}
	
	public function prime($desc,$cur_step,$total_steps){
		Ui::out($cur_step."\n");
		$this->cur_step = $cur_step;
		$this->total_steps = $total_steps;
		apiCall('task_prime',array(
			'task_id'		=>	$this->task['task_id'],
			'total_steps'	=>	$total_steps,
			'current_step'	=>	$cur_step,
			'step_desc'		=>	$desc
		));
	}
	
	public function update($desc,$cur_step){
		Ui::out($cur_step."\n");
		$this->cur_step = $cur_step;
		apiCall('task_update',array('task_id'=>$this->task['task_id'],'step_desc'=>$desc,'current_step'=>$cur_step));
	}
	
	public function complete(){
		apiCall('task_complete',array('task_id'=>$this->task['task_id']));
	}
	
	public function error($msg,$code){
		apiCall('task_error',array('task_id'=>$this->task['task_id'],'err_msg'=>$msg,'err_code'=>$code));
	}
	
	public function __destruct(){
		if($this->err === false) $this->complete();
		else $this->error($this->err->getMessage(),$this->err->getCode());
	}
	
}