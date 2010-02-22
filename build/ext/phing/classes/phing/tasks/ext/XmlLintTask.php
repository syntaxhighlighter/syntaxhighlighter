<?php
/*
 *  $Id: XmlLintTask.php 325 2007-12-20 15:44:58Z hans $
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
 * A XML lint task. Checking syntax of one or more XML files against an XML Schema using the DOM extension.
 *
 * @author   Knut Urdalen <knut.urdalen@telio.no>
 * @package  phing.tasks.ext
 */
class XmlLintTask extends Task {

  protected $file;  // the source file (from xml attribute)
  protected $schema; // the schema file (from xml attribute)
  protected $filesets = array(); // all fileset objects assigned to this task

  /**
   * File to be performed syntax check on
   *
   * @param PhingFile $file
   */
  public function setFile(PhingFile $file) {
    $this->file = $file;
  }

  /**
   * XML Schema Description file to validate against
   *
   * @param PhingFile $schema
   */
  public function setSchema(PhingFile $schema) {
    $this->schema = $schema;
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
   * Execute lint check against PhingFile or a FileSet
   */
  public function main() {
    if(!isset($this->schema)) {
      throw new BuildException("Missing attribute 'schema'");
    }
    $schema = $this->schema->getPath();
    if(!file_exists($schema)) {
      throw new BuildException("File not found: ".$schema);
    }
    if(!isset($this->file) and count($this->filesets) == 0) {
      throw new BuildException("Missing either a nested fileset or attribute 'file' set");
    }

    set_error_handler(array($this, 'errorHandler'));
    if($this->file instanceof PhingFile) {
      $this->lint($this->file->getPath());
    } else { // process filesets
      $project = $this->getProject();
      foreach($this->filesets as $fs) {
	$ds = $fs->getDirectoryScanner($project);
	$files = $ds->getIncludedFiles();
	$dir = $fs->getDir($this->project)->getPath();
	foreach($files as $file) {
	  $this->lint($dir.DIRECTORY_SEPARATOR.$file);
	}
      }
    }
    restore_error_handler();
  }

  /**
   * Performs validation
   *
   * @param string $file
   * @return void
   */
  protected function lint($file) {
    if(file_exists($file)) {
      if(is_readable($file)) {
	$dom = new DOMDocument();
	$dom->load($file);
	if($dom->schemaValidate($this->schema->getPath())) {
	  $this->log($file.' validated', Project::MSG_INFO);
	} else {
	  $this->log($file.' fails to validate (See messages above)', Project::MSG_ERR);
	}
      } else {
	throw new BuildException('Permission denied: '.$file);
      }
    } else {
      throw new BuildException('File not found: '.$file);
    }
  }

  /**
   * Local error handler to catch validation errors and log them through Phing
   *
   * @param int    $level
   * @param string $message
   * @param string $file
   * @param int    $line
   */
  public function errorHandler($level, $message, $file, $line, $context) {
    $matches = array();
    preg_match('/^.*\(\): (.*)$/', $message, $matches);
    $this->log($matches[1], Project::MSG_ERR);
  }

}

