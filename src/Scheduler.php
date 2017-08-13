<?php 
namespace Domyway\Coroutine; 


class Scheduler 
{
	protected $maxTaskId = 0;
	protected $taskMap = [];
	protected $taskQueue ;
	public function __construct()
	{
		$this->taskQueue = new \SplQueue;
	}

	public function newTask(\Generator $coroutine)
	{
		$tid = ++$this->maxTaskId;
		$task = new Task($tid, $coroutine);
		$this->taskMap[$tid] = $task;
		$this->schedule($task);
		return $tid;
	}

	public function schedule(Task $task)
	{
		$this->taskQueue->enqueue($task);
	}

	public function killTask($tid)
	{
		if(!isset($this->taskMap[$tid]))
		{
			return false;
		}

		unset($this->taskMap[$tid]);

		foreach($this->taskQueue as $i => $task)
		{
			if($task->getTaskId() === $tid)
			{
				unset($this->taskQueue[$tid]);
				break;
			}
		}

		return true;

	}

	public function run()
	{
		$res = [];
		while(!$this->taskQueue->isEmpty())
		{
			$task = $this->taskQueue->dequeue();
			$retval = $task->run();

			if($retval instanceof SystemCall)
			{
				try {
					$retval($task, $this);
				} catch(\Exception $e) {
					$task->exception($e);
					$this->schedule($task);
				}

				continue;
			}

			if($task->isFinished())
			{
				unset($this->taskMap[$task->getTaskId()]);
				$res[$task->getTaskId()] = $task->getReturn();
			}
			else
			{
				$this->schedule($task);
			}
		}

		return $res;
	}
}