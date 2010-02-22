<?php
/*
 *	$Id$
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

require_once 'phing/Task.php';

/**
 * A PHP code sniffer task. Checking the style of one or more PHP source files.
 *
 * @author	Dirk Thomas <dirk.thomas@4wdmedia.de>
 * @package	phing.tasks.ext
 */
class PhpCodeSnifferTask extends Task {

	protected $file;	// the source file (from xml attribute)
	protected $filesets = array(); // all fileset objects assigned to this task

	// parameters for php code sniffer
	protected $standard = 'Generic';
	protected $sniffs = array();
	protected $showWarnings = true;
	protected $verbosity = 0;
	protected $tabWidth = 0;
	protected $allowedFileExtensions = array('php');
	protected $ignorePatterns = false;
	protected $noSubdirectories = false;
	protected $configData = array();

	// parameters to customize output
	protected $showSniffs = false;
	protected $outputFormat = 'default';

	/**
	 * File to be performed syntax check on
	 * @param PhingFile $file
	 */
	public function setFile(PhingFile $file) {
		$this->file = $file;
	}

	/**
	 * Nested creator, creates a FileSet for this task
	 *
	 * @return FileSet The created fileset object
	 */
	function createFileSet() {
		$num = array_push($this->filesets, new FileSet());
		return $this->filesets[$num-1];
	}

	/**
	 * Sets the standard to test for
	 * @param string $standard
	 */
	public function setStandard($standard)
	{
		if (DIRECTORY_SEPARATOR != '/') $standard = str_replace('/', DIRECTORY_SEPARATOR, $standard);
		$this->standard = $standard;
	}

	/**
	 * Sets the sniffs which the standard should be restricted to
	 * @param string $sniffs
	 */
	public function setSniffs($sniffs)
	{
		$token = ' ,;';
		$sniff = strtok($sniffs, $token);
		while ($sniff !== false) {
			$this->sniffs[] = $sniff;
			$sniff = strtok($token);
		}
	}

	/**
	 * Sets the flag if warnings should be shown
	 * @param boolean $show
	 */
	public function setShowWarnings($show)
	{
		$this->showWarnings = StringHelper::booleanValue($show);
	}

	/**
	 * Sets the verbosity level
	 * @param int $level
	 */
	public function setVerbosity($level)
	{
		$this->verbosity = (int)$level;
	}

	/**
	 * Sets the tab width to replace tabs with spaces
	 * @param int $width
	 */
	public function setTabWidth($width)
	{
		$this->tabWidth = (int)$width;
	}

	/**
	 * Sets the allowed file extensions when using directories instead of specific files
	 * @param array $extensions
	 */
	public function setAllowedFileExtensions($extensions)
	{
		$this->allowedFileExtensions = array();
		$token = ' ,;';
		$ext = strtok($extensions, $token);
		while ($ext !== false) {
			$this->allowedFileExtensions[] = $ext;
			$ext = strtok($token);
		}
	}

	/**
	 * Sets the ignore patterns to skip files when using directories instead of specific files
	 * @param array $extensions
	 */
	public function setIgnorePatterns($patterns)
	{
		$this->ignorePatterns = array();
		$token = ' ,;';
		$pattern = strtok($patterns, $token);
		while ($pattern !== false) {
			$this->ignorePatterns[] = $pattern;
			$pattern = strtok($token);
		}
	}

	/**
	 * Sets the flag if subdirectories should be skipped
	 * @param boolean $subdirectories
	 */
	public function setNoSubdirectories($subdirectories)
	{
		$this->noSubdirectories = StringHelper::booleanValue($subdirectories);
	}

	/**
	 * Creates a config parameter for this task
	 *
	 * @return Parameter The created parameter
	 */
	public function createConfig() {
		$num = array_push($this->configData, new Parameter());
		return $this->configData[$num-1];
	}

	/**
	 * Sets the flag if the used sniffs should be listed
	 * @param boolean $show
	 */
	public function setShowSniffs($show)
	{
		$this->showSniffs = StringHelper::booleanValue($show);
	}

	/**
	 * Sets the output format
	 * @param string $format
	 */
	public function setFormat($format)
	{
		$this->outputFormat = $format;
	}

	/**
	 * Executes PHP code sniffer against PhingFile or a FileSet
	 */
	public function main() {
		if(!isset($this->file) and count($this->filesets) == 0) {
			throw new BuildException("Missing either a nested fileset or attribute 'file' set");
		}

		require_once 'PHP/CodeSniffer.php';
		$codeSniffer = new PHP_CodeSniffer($this->verbosity, $this->tabWidth);
		$codeSniffer->setAllowedFileExtensions($this->allowedFileExtensions);
		if (is_array($this->ignorePatterns)) $codeSniffer->setIgnorePatterns($this->ignorePatterns);
		foreach ($this->configData as $configData) {
			$codeSniffer->setConfigData($configData->getName(), $configData->getValue(), true);
		}

		if ($this->file instanceof PhingFile) {
			$codeSniffer->process($this->file->getPath(), $this->standard, $this->sniffs, $this->noSubdirectories);

		} else {
			$fileList = array();
			$project = $this->getProject();
			foreach ($this->filesets as $fs) {
				$ds = $fs->getDirectoryScanner($project);
				$files = $ds->getIncludedFiles();
				$dir = $fs->getDir($this->project)->getPath();
				foreach ($files as $file) {
					$fileList[] = $dir.DIRECTORY_SEPARATOR.$file;
				}
			}
			$codeSniffer->process($fileList, $this->standard, $this->sniffs, $this->noSubdirectories);
		}
		$this->output($codeSniffer);
	}

	/**
	 * Outputs the results
	 * @param PHP_CodeSniffer $codeSniffer
	 */
	protected function output($codeSniffer) {
		if ($this->showSniffs) {
			$sniffs = $codeSniffer->getSniffs();
			$sniffStr = '';
			foreach ($sniffs as $sniff) {
				$sniffStr .= '- ' . $sniff.PHP_EOL;
			}
			$this->log('The list of used sniffs (#' . count($sniffs) . '): ' . PHP_EOL . $sniffStr, Project::MSG_INFO);
		}

		switch ($this->outputFormat) {
			case 'default':
				$this->outputCustomFormat($codeSniffer);
				break;
			case 'xml':
				$codeSniffer->printXMLErrorReport($this->showWarnings);
				break;
			case 'checkstyle':
				$codeSniffer->printCheckstyleErrorReport($this->showWarnings);
				break;
			case 'csv':
				$codeSniffer->printCSVErrorReport($this->showWarnings);
				break;
			case 'report':
				$codeSniffer->printErrorReport($this->showWarnings);
				break;
			case 'summary':
				$codeSniffer->printErrorReportSummary($this->showWarnings);
				break;
			case 'doc':
				$codeSniffer->generateDocs($this->standard, $this->sniffs);
				break;
			default:
				$this->log('Unknown output format "' . $this->outputFormat . '"', Project::MSG_INFO);
				break;
		}
	}

	/**
	 * Outputs the results with a custom format
	 * @param PHP_CodeSniffer $codeSniffer
	 */
	protected function outputCustomFormat($codeSniffer) {
		$report = $codeSniffer->prepareErrorReport($this->showWarnings);

		$files = $report['files'];
		foreach ($files as $file => $attributes) {
			$errors = $attributes['errors'];
			$warnings = $attributes['warnings'];
			$messages = $attributes['messages'];
			if ($errors > 0) {
				$this->log($file . ': ' . $errors . ' error' . ($errors > 1 ? 's' : '') . ' detected', Project::MSG_ERR);
				$this->outputCustomFormatMessages($messages, 'ERROR');
			} else {
				$this->log($file . ': No syntax errors detected', Project::MSG_VERBOSE);
			}
			if ($warnings > 0) {
				$this->log($file . ': ' . $warnings . ' warning' . ($warnings > 1 ? 's' : '') . ' detected', Project::MSG_WARN);
				$this->outputCustomFormatMessages($messages, 'WARNING');
			}
		}

		$totalErrors = $report['totals']['errors'];
		$totalWarnings = $report['totals']['warnings'];
		$this->log(count($files) . ' files where checked', Project::MSG_INFO);
		if ($totalErrors > 0) {
			$this->log($totalErrors . ' error' . ($totalErrors > 1 ? 's' : '') . ' detected', Project::MSG_ERR);
		} else {
			$this->log('No syntax errors detected', Project::MSG_INFO);
		}
		if ($totalWarnings > 0) {
			$this->log($totalWarnings . ' warning' . ($totalWarnings > 1 ? 's' : '') . ' detected', Project::MSG_INFO);
		}
	}

	/**
	 * Outputs the messages of a specific type for one file
	 * @param array $messages
	 * @param string $type
	 */
	protected function outputCustomFormatMessages($messages, $type) {
		foreach ($messages as $line => $messagesPerLine) {
			foreach ($messagesPerLine as $column => $messagesPerColumn) {
				foreach ($messagesPerColumn as $message) {
					$msgType = $message['type'];
					if ($type == $msgType) {
						$logLevel = Project::MSG_INFO;
						if ($msgType == 'ERROR') {
							$logLevel = Project::MSG_ERR;
						} else if ($msgType == 'WARNING') {
							$logLevel = Project::MSG_WARN;
						}
						$text = $message['message'];
						$string = $msgType . ' in line ' . $line . ' column ' . $column . ': ' . $text;
						$this->log($string, $logLevel);
					}
				}
			}
		}
	}

}
