<?php
/*
 *  $Id$
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
  * A Javascript lint task. Checks syntax of Javascript files.
  * Javascript lint (http://www.javascriptlint.com) must be in the system path.
  * This class is based on Knut Urdalen's PhpLintTask.
  *
  * @author Stefan Priebsch <stefan.priebsch@e-novative.de>
  */
  class JslLintTask extends Task
  {
    protected $file;  // the source file (from xml attribute)
    protected $filesets = array(); // all fileset objects assigned to this task

    protected $showWarnings = true;
    protected $haltOnFailure = false;
    protected $hasErrors = false;
    private $badFiles = array();

    /**
     * Sets the flag if warnings should be shown
     * @param boolean $show
     */
    public function setShowWarnings($show) {
      $this->showWarnings = StringHelper::booleanValue($show);
    }

    /**
     * The haltonfailure property
     * @param boolean $aValue
     */
    public function setHaltOnFailure($aValue) {
      $this->haltOnFailure = $aValue;
    }
  
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
     * Execute lint check against PhingFile or a FileSet
     */
    public function main() {
      if(!isset($this->file) and count($this->filesets) == 0) {
        throw new BuildException("Missing either a nested fileset or attribute 'file' set");
      }
  
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
  
      if ($this->haltOnFailure && $this->hasErrors) throw new BuildException('Syntax error(s) in JS files:' .implode(', ',$this->badFiles));
    }
  
    /**
     * Performs the actual syntax check
     *
     * @param string $file
     * @return void
     */
    protected function lint($file)
    {
      exec('jsl', $output);
      if (!preg_match('/JavaScript\sLint/', implode('', $output))) throw new BuildException('Javascript Lint not found');
    
      $command = 'jsl -output-format file:__FILE__;line:__LINE__;message:__ERROR__ -process ';

      if(file_exists($file))
      {
        if(is_readable($file))
        {
          $messages = array();
          exec($command.'"'.$file.'"', $messages);

          $summary = $messages[sizeof($messages) - 1];

          preg_match('/(\d+)\serror/', $summary, $matches);
          $errorCount = $matches[1];
          
          preg_match('/(\d+)\swarning/', $summary, $matches);
          $warningCount = $matches[1];

          $errors = array();
          $warnings = array();
          if ($errorCount > 0 || $warningCount > 0) {
            $last = false;
            foreach ($messages as $message) {
              $matches = array();
              if (preg_match('/^(\.*)\^$/', $message)) {
                $column = strlen($message);
                if ($last == 'error') {
                  $errors[count($errors) - 1]['column'] = $column;
                } else if ($last == 'warning') {
                  $warnings[count($warnings) - 1]['column'] = $column;
                }
                $last = false;
              }
              if (!preg_match('/^file:(.+);line:(\d+);message:(.+)$/', $message, $matches)) continue;
              $msg = $matches[3];
              $data = array('filename' => $matches[1], 'line' => $matches[2], 'message' => $msg);
              if (preg_match('/^.*error:.+$/i', $msg)) {
                $errors[] = $data;
                $last = 'error';
              } else if (preg_match('/^.*warning:.+$/i', $msg)) {
                $warnings[] = $data;
                $last = 'warning';
              }
            }
          }

          if($this->showWarnings && $warningCount > 0)
          {
            $this->log($file . ': ' . $warningCount . ' warnings detected', Project::MSG_WARN);
            foreach ($warnings as $warning) {
              $this->log('- line ' . $warning['line'] . (isset($warning['column']) ? ' column ' . $warning['column'] : '') . ': ' . $warning['message'], Project::MSG_WARN);
            }
          }
            
          if($errorCount > 0)
          {
            $this->log($file . ': ' . $errorCount . ' errors detected', Project::MSG_ERR);
            foreach ($errors as $error) {
              $this->log('- line ' . $error['line'] . (isset($error['column']) ? ' column ' . $error['column'] : '') . ': ' . $error['message'], Project::MSG_ERR);
            }
            $this->badFiles[] = $file;
            $this->hasErrors = true;
          } else if (!$this->showWarnings || $warningCount == 0) {
            $this->log($file . ': No syntax errors detected', Project::MSG_INFO);
          }
        } else {
          throw new BuildException('Permission denied: '.$file);
        }
      } else {
        throw new BuildException('File not found: '.$file);
      }
    }
  }


