<?php
require_once("phing/Task.php");

class ReadFileTask extends Task
{
	private $file = null;
	private $returnProperty = null;
	private $flatLines = 0;
	private $stripTabs = 0;

	public function init()
	{
	}
	
	public function setFile($str)
	{
		$this->file = $str;
	}

	public function setReturnProperty($value)
	{
		$this->returnProperty = $value;
	}
	
	public function setFlatLines($value)
	{
		$this->flatLines = $value;
	}
	
	public function setStripTabs($value)
	{
		$this->stripTabs = $value;
	}
	
    public function main()
    {
		$value = file_get_contents($this->file);
		
		$replace = array();
		
		if ($this->flatLines == 1)
		{
			array_push($replace, "\n");
			array_push($replace, "\r");
		}
		
		if ($this->stripTabs == 1)
			array_push($replace, "\t");
			
		$value = str_replace($replace, '', $value);
			
		$this->getProject()->setNewProperty($this->returnProperty, $value);
	}
}