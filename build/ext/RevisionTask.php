<?php
require_once("phing/Task.php");

class RevisionTask extends Task
{
	private $hgpath = null;
	private $workingcopy = null;
	private $propertyname = null;

	public function init()
	{
	}
	
	public function setHgPath($str)
	{
		$this->hgpath = $str;
	}
	
	public function setWorkingCopy($str)
	{
		$this->workingcopy = $str;
	}

	public function setPropertyName($value)
	{
		$this->propertyname = $value;
	}
	
    public function main()
    {
		if ($this->getProject()->getUserProperty($this->propertyname))
			return;
		
		$rev = '???';
		
		try
		{
			$output = array();
			$this->workingcopy = str_replace(" ", "\ ", $this->workingcopy);
			$this->workingcopy = realpath($this->workingcopy);

			exec("\"$this->hgpath\" tip", $output);

			foreach ($output as $index => $line)
				if (preg_match("/^changeset:\s*(\\d+):/", $line, &$matches))
				{
					$rev = $matches[1];
					break;
				}
		}
		catch (Exception $e)
		{
		}

		$this->getProject()->setUserProperty($this->propertyname, $rev);
	}
}