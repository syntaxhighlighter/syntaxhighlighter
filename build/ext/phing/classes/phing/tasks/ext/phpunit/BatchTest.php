<?php
/**
 * $Id: BatchTest.php 350 2008-02-06 15:06:57Z mrook $
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

require_once 'phing/types/FileSet.php';

/**
 * Scans a list of files given by the fileset attribute, extracts
 * all subclasses of PHPUnit(2)_Framework_TestCase / PHPUnit(2)_Framework_TestSuite.
 *
 * @author Michiel Rook <michiel.rook@gmail.com>
 * @version $Id: BatchTest.php 350 2008-02-06 15:06:57Z mrook $
 * @package phing.tasks.ext.phpunit
 * @since 2.1.0
 */
class BatchTest
{
	/** the list of filesets containing the testcase filename rules */
	private $filesets = array();

	/** the reference to the project */
	private $project = NULL;

	/** the classpath to use with Phing::__import() calls */
	private $classpath = NULL;
	
	/** names of classes to exclude */
	private $excludeClasses = array();
	
	/**
	 * Create a new batchtest instance
	 *
	 * @param Project the project it depends on.
	 */
	function __construct(Project $project)
	{
		$this->project = $project;
	}
	
	/**
	 * Sets the classes to exclude
	 */
	function setExclude($exclude)
	{
		$this->excludeClasses = explode(" ", $exclude);
	}

	/**
	 * Sets the classpath
	 */
	function setClasspath(Path $classpath)
	{
		if ($this->classpath === null)
		{
			$this->classpath = $classpath;
		}
		else
		{
			$this->classpath->append($classpath);
		}
	}

	/**
	 * Creates a new Path object
	 */
	function createClasspath()
	{
		$this->classpath = new Path();
		return $this->classpath;
	}

	/**
	 * Returns the classpath
	 */
	function getClasspath()
	{
		return $this->classpath;
	}

	/**
	 * Add a new fileset containing the XML results to aggregate
	 *
	 * @param FileSet the new fileset containing XML results.
	 */
	function addFileSet(FileSet $fileset)
	{
		$this->filesets[] = $fileset;
	}

	/**
	 * Iterate over all filesets and return the filename of all files.
	 *
	 * @return array an array of filenames
	 */
	private function getFilenames()
	{
		$filenames = array();

		foreach ($this->filesets as $fileset)
		{
			$ds = $fileset->getDirectoryScanner($this->project);
			$ds->scan();

			$files = $ds->getIncludedFiles();

			foreach ($files as $file)
			{
				$filenames[] = $ds->getBaseDir() . "/" . $file;
			}
		}

		return $filenames;
	}
	
	/**
	 * Checks wheter $input is a subclass of PHPUnit(2)_Framework_TestCasse
	 * or PHPUnit(2)_Framework_TestSuite
	 */
	private function isTestCase($input)
	{
		if (PHPUnitUtil::$installedVersion == 3)
			return is_subclass_of($input, 'PHPUnit_Framework_TestCase') || is_subclass_of($input, 'PHPUnit_Framework_TestSuite');
		else
			return is_subclass_of($input, 'PHPUnit2_Framework_TestCase') || is_subclass_of($input, 'PHPUnit2_Framework_TestSuite');
	}
	
	/**
	 * Filters an array of classes, removes all classes that are not test cases or test suites,
	 * or classes that are declared abstract
	 */
	private function filterTests($input)
	{
		$reflect = new ReflectionClass($input);
		
		return $this->isTestCase($input) && (!$reflect->isAbstract());
	}

	/**
	 * Returns an array of test cases and test suites that are declared
	 * by the files included by the filesets
	 *
	 * @return array an array of PHPUnit(2)_Framework_TestCase or PHPUnit(2)_Framework_TestSuite classes.
	 */
	function elements()
	{
		$filenames = $this->getFilenames();
		
		$declaredClasses = array();		

		foreach ($filenames as $filename)
		{
			$definedClasses = PHPUnitUtil::getDefinedClasses($filename, $this->classpath);
			
			foreach($definedClasses as $definedClass) {
				$this->project->log("(PHPUnit) Adding $definedClass (from $filename) to tests.", Project::MSG_DEBUG);
			}
			
			$declaredClasses = array_merge($declaredClasses, $definedClasses);
		}
		
		$elements = array_filter($declaredClasses, array($this, "filterTests"));

		return $elements;
	}
}
