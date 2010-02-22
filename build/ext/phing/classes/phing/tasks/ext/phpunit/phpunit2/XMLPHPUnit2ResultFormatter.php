<?php
/**
 * $Id: XMLPHPUnit2ResultFormatter.php 325 2007-12-20 15:44:58Z hans $
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
require_once 'PHPUnit2/Runner/Version.php';

require_once 'PHPUnit2/Util/Log/XML.php';

require_once 'phing/tasks/ext/phpunit/phpunit2/PHPUnit2ResultFormatter.php';

/**
 * Prints XML output of the test to a specified Writer
 *
 * @author Michiel Rook <michiel.rook@gmail.com>
 * @version $Id: XMLPHPUnit2ResultFormatter.php 325 2007-12-20 15:44:58Z hans $
 * @package phing.tasks.ext.phpunit.phpunit2
 * @since 2.1.0
 */
class XMLPHPUnit2ResultFormatter extends PHPUnit2ResultFormatter
{
	private $logger = NULL;
	
	function __construct()
	{
		$this->logger = new PHPUnit2_Util_Log_XML();
		$this->logger->setWriteDocument(false);
	}
	
	function getExtension()
	{
		return ".xml";
	}
	
	function getPreferredOutfile()
	{
		return "testsuites";
	}
	
	function startTestSuite(PHPUnit2_Framework_TestSuite $suite)
	{
		parent::startTestSuite($suite);
		
		$this->logger->startTestSuite($suite);
	}
	
	function endTestSuite(PHPUnit2_Framework_TestSuite $suite)
	{
		parent::endTestSuite($suite);
		
		$this->logger->endTestSuite($suite);
	}
	
	function startTest(PHPUnit2_Framework_Test $test)
	{
		parent::startTest($test);
		
		$this->logger->startTest($test);
	}

	function endTest(PHPUnit2_Framework_Test $test)
	{
		parent::endTest($test);
		
		$this->logger->endTest($test);
	}
	
	function addError(PHPUnit2_Framework_Test $test, Exception $e)
	{
		parent::addError($test, $e);
		
		$this->logger->addError($test, $e);
	}

	function addFailure(PHPUnit2_Framework_Test $test, PHPUnit2_Framework_AssertionFailedError $t)
	{
		parent::addFailure($test, $t);
		
		$this->logger->addFailure($test, $t);
	}

	function addIncompleteTest(PHPUnit2_Framework_Test $test, Exception $e)
	{
		parent::addIncompleteTest($test, $e);
		
		$this->logger->addIncompleteTest($test, $e);
	}
	
	function endTestRun()
	{
		parent::endTestRun();
		
		if ($this->out)
		{
			$this->out->write($this->logger->getXML());
			$this->out->close();
		}
	}
}
