<?php
/**
 * $Id: SimpleTestResultFormatter.php 325 2007-12-20 15:44:58Z hans $
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

require_once 'simpletest/scorer.php';

require_once 'phing/system/io/Writer.php';

/**
 * This abstract class describes classes that format the results of a SimpleTest testrun.
 *
 * @author Michiel Rook <michiel.rook@gmail.com>
 * @version $Id: SimpleTestResultFormatter.php 325 2007-12-20 15:44:58Z hans $
 * @package phing.tasks.ext.phpunit2
 * @since 2.2.0
 */
abstract class SimpleTestResultFormatter extends SimpleReporter
{
	protected $out = NULL;
	
	protected $project = NULL;
	
	private $timer = NULL;

	private $runCount = 0;
	
	private $failureCount = 0;
	
	private $errorCount = 0;	

	private $currentTest = "";
	
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
	
	function paintMethodStart($test_name)
	{
		parent::paintMethodStart($test_name);
		
		$this->currentTest = $test_name;
	}
	
	function paintMethodEnd($test_name)
	{
		parent::paintMethodEnd($test_name);
		
		$this->runCount++;
	}
	
	function paintCaseStart($test_name)
	{
		parent::paintCaseStart($test_name);
		
		$this->runCount = 0;
		$this->failureCount = 0;
		$this->errorCount = 0;
		
		$this->timer = new Timer();
		$this->timer->start();
	}
		
	function paintCaseEnd($test_name)
	{
		parent::paintCaseEnd($test_name);
		
		$this->timer->stop();
	}

	function paintError($message)
	{
		parent::paintError($message);
		
		$this->errorCount++;
	}

	function paintFail($message)
	{
		parent::paintFail($message);
		
		$this->failureCount++;
	}

	function getRunCount()
	{
		return $this->runCount;
	}
	
	function getFailureCount()
	{
		return $this->failureCount;
	}
	
	function getErrorCount()
	{
		return $this->errorCount;
	}
	
	function getTestName()
	{
		return $this->currentTest;
	}
	
	function getElapsedTime()
	{
		if ($this->timer)
		{
			return $this->timer->getElapsedTime();
		}
		else
		{
			return 0;
		}
	}
}
