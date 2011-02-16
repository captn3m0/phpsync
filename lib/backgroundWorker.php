<?php
/**
 * @author Abhay Rana
 * @version 0.1
 */
/**
 * @brief backgroundWorker allows you to run simultaneous processes in the background
 *   while specifying a callback function which will be called appropriately
 */
class backgroundWorker{
	/** Error constant for invalid worker id */
	const INVALID_WORKER=1;
	/** Error constant for a busy worker */
	const WORKER_BUSY=2;
	/** Error constant when no callback function is specified */
	const NO_CALLBACK=3;
	/** The executable that must be called to create a new worker */
	public $executable;
	/** The array that holds all the workers */
	private $listOfWorkers=array();
	/** The callback function for each status update */
	public $callback;
	/** 
	 * @brief A wrapper function tp set the callback function
	 * @param func (function) the callback function to call for each update
	 */
	/**
	 * @brief The status update that results in killing the worker
	 * Usually signals end of work
	 */
	public $killString;
	function setCallback($func) {
		$this->callback=$func;
	}
	/**
	 * @brief Constructor
	 * @param exec (string) The path to the executable 
	 */
	function __construct($exec)	{
		$this->executable=$exec;
	}
	/**
	 * @brief Cleanup function for all workers, kills all worker threads
	 */
	public function killAll() {
		foreach($this->listOfWorkers as $id=>&$p)
			$this->end($id);
	}
	/**
	 * @brief Create a new worker thread
	 * @returns id for that particular thread
	 */
	public function start($argument) {
		$this->listOfWorkers[]=popen($this->executable." $argument",'r');		
		$id=key($this->listOfWorkers);
		stream_set_blocking($this->listOfWorkers[$id],0);
		return $id;
	}
	/**
	 * @brief End a particular thread
	 * @returns The return value of the process.
	 */
	public function end($id) {
		$return_value=pclose($this->listOfWorkers[$id]);
		unset($this->listOfWorkers[$id]);
		return $return_value;
	}
	/**
	 * @brief Private function to query status for a particular worker
	 * @returns fread from stdout of the worker thread or WORKER_BUSY in
	 * case it recieved no response from the worker
	 */
	private function __status($id) {
		if(!$this->listOfWorkers[$id])
			return self::INVALID_WORKER;
		if($status=fread($this->listOfWorkers[$id],4096))
			return $status;
		else
			return self::WORKER_BUSY;		
	}
	/**
	 * @brief Rechecks the status of all the workers
	 *   and calls the callback function for each update found
	 *   in the pool
	 * @returns none
	 * @calls callback
	 */
	public function recheck() {
		if(!$this->callback)
			return self::NO_CALLBACK;
		$callback=$this->callback;
		foreach($this->listOfWorkers as $id=>&$p)
		{
			$status=$this->__status($id);
			if($status!=self::WORKER_BUSY)
			{
				$callback($id,$status);
				if($status==$this->killString)
					$this->end($id);
			}
		}
	}
}
?>
