<?php
/**
 * $Id: PlainPHPUnit2ResultFormatter.php 325 2007-12-20 15:44:58Z hans $
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

require_once 'PHPUnit2/Framework/Test.php';
require_once 'PHPUnit2/Util/Filter.php';

require_once 'phing/tasks/ext/phpunit/phpunit2/PHPUnit2ResultFormatter.php';

/**
 * Prints plain text output of the test to a specified Writer.
 *
 * @author Michiel Rook <michiel.rook@gmail.com>
 * @version $Id: PlainPHPUnit2ResultFormatter.php 325 2007-12-20 15:44:58Z hans $
 * @package phing.tasks.ext.phpunit.phpunit2
 * @since 2.1.0
 */
class PlainPHPUnit2ResultFormatter extends PHPUnit2ResultFormatter
{
	private $inner = "";
	
	function getExtension()
	{
		return ".txt";
	}
	
	function getPreferredOutfile()
	{
		return "testresults";
	}

	function startTestSuite(PHPUnit2_Framework_TestSuite $suite)
	{
		parent::startTestSuite($suite);
		
		$this->inner = "";
	}
	
	function endTestSuite(PHPUnit2_Framework_TestSuite $suite)
	{
		parent::endTestSuite($suite);
		
		$sb = "Testsuite: " . $suite->getName() . "\n";
		$sb.= "Tests run: " . $this->getRunCount();
		$sb.= ", Failures: " . $this->getFailureCount();
		$sb.= ", Errors: " . $this->getErrorCount();
		$sb.= ", Time elapsed: " . $this->getElapsedTime();
		$sb.= " sec\n";

		if ($this->out != NULL)
		{
			$this->out->write($sb);
			$this->out->write($this->inner);
		}
	}

	function addError(PHPUnit2_Framework_Test $test, Exception $e)
	{
		parent::addError($test, $e);
		
		$this->formatError("ERROR", $test, $e);
	}

	function addFailure(PHPUnit2_Framework_Test $test, PHPUnit2_Framework_AssertionFailedError $t)
	{
		parent::addFailure($test, $t);
		
		$this->formatError("FAILED", $test, $t);
	}

	function addIncompleteTest(PHPUnit2_Framework_Test $test, Exception $e)
	{
		parent::addIncompleteTest($test, $e);
		
		$this->formatError("INCOMPLETE", $test, $e);
	}

	private function formatError($type, PHPUnit2_Framework_Test $test, Exception $e)
	{
		if ($test != null)
		{
			$this->endTest($test);
		}

		$this->inner.= $test->getName() . " " . $type . "\n";
		$this->inner.= $e->getMessage() . "\n";
		$this->inner.= PHPUnit2_Util_Filter::getFilteredStackTrace($e) . "\n";
	}
	
	function endTestRun()
	{
		parent::endTestRun();
		
		if ($this->out != NULL)
		{
			$this->out->close();
		}
	}
}
