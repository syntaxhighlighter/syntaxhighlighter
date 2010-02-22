<?php
/**
 * $Id: SimpleTestPlainResultFormatter.php 325 2007-12-20 15:44:58Z hans $
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

require_once 'phing/tasks/ext/simpletest/SimpleTestResultFormatter.php';

/**
 * Prints plain text output of the test to a specified Writer.
 *
 * @author Michiel Rook <michiel.rook@gmail.com>
 * @version $Id: SimpleTestPlainResultFormatter.php 325 2007-12-20 15:44:58Z hans $
 * @package phing.tasks.ext.simpletest
 * @since 2.2.0
 */
class SimpleTestPlainResultFormatter extends SimpleTestResultFormatter
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

	function paintCaseStart($test_name)
	{
		parent::paintCaseStart($test_name);
		
		$this->inner = "";
	}
	
	function paintCaseEnd($test_name)
	{
		parent::paintCaseEnd($test_name);
		
		/* Only count suites where more than one test was run */
		if ($this->getRunCount())
		{
			$sb.= "Testsuite: $test_name\n";
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
	}

	function paintError($message)
	{
		parent::paintError($message);
		
		$this->formatError("ERROR", $message);
	}

	function paintFail($message)
	{
		parent::paintFail($message);
		
		$this->formatError("FAILED", $message);
	}

	private function formatError($type, $message)
	{
		$this->inner.= $this->getTestName() . " " . $type . "\n";
		$this->inner.= $message . "\n";	
	}
}
