<?php
/**
 * $Id: CoverageReportTransformer.php 325 2007-12-20 15:44:58Z hans $
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
require_once 'phing/system/io/PhingFile.php';
require_once 'phing/system/io/FileWriter.php';
require_once 'phing/util/ExtendedFileStream.php';

/**
 * Transform a Phing/Xdebug code coverage xml report.
 * The default transformation generates an html report in framed style.
 *
 * @author Michiel Rook <michiel.rook@gmail.com>
 * @version $Id: CoverageReportTransformer.php 325 2007-12-20 15:44:58Z hans $
 * @package phing.tasks.ext.coverage
 * @since 2.1.0
 */
class CoverageReportTransformer
{
	private $task = NULL;
	private $styleDir = "";
	private $toDir = "";
	private $document = NULL;

	function __construct(Task $task)
	{
		$this->task = $task;
	}

	function setStyleDir($styleDir)
	{
		$this->styleDir = $styleDir;
	}

	function setToDir($toDir)
	{
		$this->toDir = $toDir;
	}

	function setXmlDocument($document)
	{
		$this->document = $document;
	}

	function transform()
	{
        $dir = new PhingFile($this->toDir);

        if (!$dir->exists())
        {
            throw new BuildException("Directory '" . $this->toDir . "' does not exist");
        }

		$xslfile = $this->getStyleSheet();

		$xsl = new DOMDocument();
		$xsl->load($xslfile->getAbsolutePath());

		$proc = new XSLTProcessor();
		$proc->importStyleSheet($xsl);

		ExtendedFileStream::registerStream();

		// no output for the framed report
		// it's all done by extension...
		$proc->setParameter('', 'output.dir', $dir->getAbsolutePath());
		$proc->transformToXML($this->document);
	}

	private function getStyleSheet()
	{
		$xslname = "coverage-frames.xsl";

		if ($this->styleDir)
		{
			$file = new PhingFile($this->styleDir, $xslname);
		}
		else
		{
			$path = Phing::getResourcePath("phing/etc/$xslname");
			
			if ($path === NULL)
			{
				$path = Phing::getResourcePath("etc/$xslname");

				if ($path === NULL)
				{
					throw new BuildException("Could not find $xslname in resource path");
				}
			}
			
			$file = new PhingFile($path);
		}

		if (!$file->exists())
		{
			throw new BuildException("Could not find file " . $file->getPath());
		}

		return $file;
	}
}
