<?php
/**
 * $Id: PHPUnitTestRunner.php 361 2008-03-08 09:36:07Z mrook $
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

require_once 'phing/tasks/ext/coverage/CoverageMerger.php';

require_once 'phing/system/util/Timer.php';

/**
 * Simple Testrunner for PHPUnit2/3 that runs all tests of a testsuite.
 *
 * @author Michiel Rook <michiel.rook@gmail.com>
 * @version $Id: PHPUnitTestRunner.php 361 2008-03-08 09:36:07Z mrook $
 * @package phing.tasks.ext.phpunit
 * @since 2.1.0
 */
class PHPUnitTestRunner
{
	const SUCCESS = 0;
	const FAILURES = 1;
	const ERRORS = 2;
	const INCOMPLETES = 3;
	const SKIPPED = 4;

	private $test = NULL;
	private $suite = NULL;
	private $retCode = 0;
	private $formatters = array();
	
	private $codecoverage = false;
	
	private $project = NULL;

	private $groups = array();
	private $excludeGroups = array();

	function __construct($suite, Project $project, $groups = array(), $excludeGroups = array())
	{
		$this->suite = $suite;
		$this->project = $project;
		$this->groups = $groups;
		$this->excludeGroups = $excludeGroups;
		$this->retCode = self::SUCCESS;
	}
	
	function setCodecoverage($codecoverage)
	{
		$this->codecoverage = $codecoverage;
	}

	function addFormatter($formatter)
	{
		$this->formatters[] = $formatter;
	}

	function run()
	{
		$res = NULL;
		
		if (PHPUnitUtil::$installedVersion == 3)
		{
			require_once 'PHPUnit/Framework/TestSuite.php';			
			$res = new PHPUnit_Framework_TestResult();
		}
		else
		{
			require_once 'PHPUnit2/Framework/TestSuite.php';
			$res = new PHPUnit2_Framework_TestResult();
		}

		if ($this->codecoverage)
		{
			$res->collectCodeCoverageInformation(TRUE);
		}

		foreach ($this->formatters as $formatter)
		{
			$res->addListener($formatter);
		}

		$this->suite->run($res, false, $this->groups, $this->excludeGroups);
		
		if ($this->codecoverage)
		{
			$coverageInformation = $res->getCodeCoverageInformation();
			
			if (PHPUnitUtil::$installedVersion == 3)
			{
				foreach ($coverageInformation as $coverage_info)
				{
					CoverageMerger::merge($this->project, array($coverage_info['files']));
				}
			}
			else
			{
				CoverageMerger::merge($this->project, $coverageInformation);
			}
		}
		
		if ($res->errorCount() != 0)
		{
			$this->retCode = self::ERRORS;
		}
		else if ($res->failureCount() != 0)
		{
			$this->retCode = self::FAILURES;
		}
		else if ($res->notImplementedCount() != 0)
		{
			$this->retCode = self::INCOMPLETES;
		}
		else if (PHPUnitUtil::$installedVersion == 3 && $res->skippedCount() != 0)
		{
			$this->retCode = self::SKIPPED;
		}
	}

	function getRetCode()
	{
		return $this->retCode;
	}
}

