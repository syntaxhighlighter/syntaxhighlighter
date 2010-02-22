<?php
/**
 * $Id: PHPUnit2ResultFormatter.php 142 2007-02-04 14:06:00Z mrook $
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

require_once 'PHPUnit/Framework/TestListener.php';

require_once 'phing/system/io/Writer.php';

/**
 * This abstract class describes classes that format the results of a PHPUnit2 testrun.
 *
 * @author Michiel Rook <michiel.rook@gmail.com>
 * @version $Id: PHPUnit2ResultFormatter.php 142 2007-02-04 14:06:00Z mrook $
 * @package phing.tasks.ext.phpunit
 * @since 2.1.0
 */
abstract class PHPUnit3ResultFormatter implements PHPUnit_Framework_TestListener
{
	protected $out = NULL;
	
	protected $project = NULL;
	
	private $timers = false;
	
	private $runCounts = false;
	
	private $failureCounts = false;
	
	private $errorCounts = false;
	
	private $incompleteCounts = false;
	
	private $skipCounts = false;
	
	/**
	 * Sets the writer the formatter is supposed to write its results to.
   	 */
	function setOutput(Writer $out)
	{
		$this->out = $out;	
	}

	/**
	 * Returns the extension used for this formatter
	 *
	 * @return string the extension
	 */
	function getExtension()
	{
		return "";
	}

	/**
	 * Sets the project
	 *
	 * @param Project the project
	 */
	function setProject(Project $project)
	{
		$this->project = $project;
	}
	
	function getPreferredOutfile()
	{
		return "";
	}
	
	function startTestRun()
	{
		$this->timers = array($this->getMicrotime());
		$this->runCounts = array(0);
		$this->failureCounts = array(0);
		$this->errorCounts = array(0);
		$this->incompleteCounts = array(0);
		$this->skipCounts = array(0);
	}
	
	function endTestRun()
	{
	}
	
	function startTestSuite(PHPUnit_Framework_TestSuite $suite)
	{
		$this->timers[] = $this->getMicrotime();
		$this->runCounts[] = 0;
		$this->failureCounts[] = 0;
		$this->errorCounts[] = 0;
		$this->incompleteCounts[] = 0;
		$this->skipCounts[] = 0;
	}
	
	function endTestSuite(PHPUnit_Framework_TestSuite $suite)
	{
		$lastRunCount = array_pop($this->runCounts);
		$this->runCounts[count($this->runCounts) - 1] += $lastRunCount;
		
		$lastFailureCount = array_pop($this->failureCounts);
		$this->failureCounts[count($this->failureCounts) - 1] += $lastFailureCount;
		
		$lastErrorCount = array_pop($this->errorCounts);
		$this->errorCounts[count($this->errorCounts) - 1] += $lastErrorCount;
		
		$lastIncompleteCount = array_pop($this->incompleteCounts);
		$this->incompleteCounts[count($this->incompleteCounts) - 1] += $lastIncompleteCount;
		
		$lastSkipCount = array_pop($this->skipCounts);
		$this->skipCounts[count($this->skipCounts) - 1] += $lastSkipCount;
		
		array_pop($this->timers);
	}

	function startTest(PHPUnit_Framework_Test $test)
	{
		$this->runCounts[count($this->runCounts) - 1]++;
	}

	function endTest(PHPUnit_Framework_Test $test, $time)
	{
	}

	function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
	{
		$this->errorCounts[count($this->errorCounts) - 1]++;
	}

	function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
	{
		$this->failureCounts[count($this->failureCounts) - 1]++;
	}

	function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
	{
		$this->incompleteCounts[count($this->incompleteCounts) - 1]++;
	}

	function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
	{
		$this->skipCounts[count($this->skipCounts) - 1]++;
	}
	
	function getRunCount()
	{
		return end($this->runCounts);
	}
	
	function getFailureCount()
	{
		return end($this->failureCounts);
	}
	
	function getErrorCount()
	{
		return end($this->errorCounts);
	}
	
	function getIncompleteCount()
	{
		return end($this->incompleteCounts);
	}
	
	function getSkippedCount()
	{
		return end($this->skipCounts);
	}
	
	function getElapsedTime()
	{
		if (end($this->timers))
		{
			return $this->getMicrotime() - end($this->timers);
		}
		else
		{
			return 0;
		}
	}

	private  function getMicrotime() {
		list($usec, $sec) = explode(' ', microtime());
		return (float)$usec + (float)$sec;
	}
}

