<?php 
namespace Domyway\Coroutine;


class SystemCall 
{
	protected $callback;

	public function __construct(callable $callback)
	{
		$this->callback = $callback;
	}

	public function __invoke(Task $task, Scheduler $scheduler)
	{
		$callback = $this->callback;
		return $callback($task, $scheduler);
	}

	public static function getTaskId()
	{
		return new SystemCall(function (Task $task, Scheduler $scheduler) {
			$task->setSendValue($task->getTaskId());
			$scheduler->schedule($task);
		});
	}

	public static function killTask($tid)
	{
		return new SystemCall(function (Task $task, Scheduler $scheduler) use($tid) {
			if ($scheduler->killTask($id))
			{
				$scheduler->schedule($task);
			}
			else
			{
				throw new \InvalidArgumentException('Invalid task ID!');
			}

		});
	}

	public static function newTask(\Generator $corotine)
	{
		return new SystemCall(
			function(Task $task, Scheduler $scheduler) use ($corotine) {
				$task->setSendValue($scheduler->newTask($corotine));
				$scheduler->schedule($task);
			}
		);
	}

	public static function retval($value)
	{
		return new CorotineReturnValue($value);
	}
}