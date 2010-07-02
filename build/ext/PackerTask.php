<?php
require_once("phing/tasks/system/CopyTask.php");
require_once(dirname(__FILE__)."/packer/class.JavaScriptPacker.php");

class PackerTask extends CopyTask
{
	protected $filesets = array();	// all fileset objects assigned to this task
	
	/**
	 * Nested creator, creates a FileSet for this task
	 *
	 * @access  public
	 * @return  object  The created fileset object
	 */
	function createFileSet() 
	{
		$num = array_push($this->filesets, new FileSet());
		return $this->filesets[$num-1];
	}

	/**
	 * Validates attributes coming in from XML
	 *
	 * @access  private
	 * @return  void
	 * @throws  BuildException
	 */
	protected function validateAttributes()
	{
		if (count($this->filesets) === 0)
			throw new BuildException("PackerTask. Specify a fileset.");
	}
	
	public function init()
	{
	}

	public function pack($file)
	{
		$file = realpath($file);
		$home = dirname(__FILE__);
		$jar = realpath("$home/compiler.jar");
			
		$java = "java";
		exec("$java -version", &$output, &$result);
		if ($result != 0)
			throw new BuildException("Java not found.");
			
		// first we pack the file using DOJO shrinksafe
		$cmd = "$java -jar \"$jar\" --js=\"$file\" --js_output_file=\"$file.tmp\"";
		exec($cmd, &$output, &$result);

		if ($result != 0)
			throw new BuildException("Java error.");

		unlink($file);
		rename("$file.tmp", $file);
		
		// after DOJO, we use the Packer to tighten it up.
		$script = file_get_contents($file);
		$packer = new JavaScriptPacker($script, 62, true, false);
		$packed = $packer->pack();
		file_put_contents($file, $packed);

		$this->log("$file packed.", Project::MSG_INFO);
	}
	
	public function main()
	{
        foreach($this->filesets as $fs)
		{
			$ds = $fs->getDirectoryScanner($this->project);
			$files = $ds->getIncludedFiles();

			$dir = $fs->getDir($this->project);
			
			foreach($files as $file)
				$this->pack("$dir/$file");
        }
	}
}
