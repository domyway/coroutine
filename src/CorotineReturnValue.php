<?php 
namespace Domyway\Coroutine;

class CorotineReturnValue 
{

	protected $value;

	public function __construct($value)
	{
		$this->value = $value;
	}

	public function getValue()
	{
		return $this->value;
	}
}