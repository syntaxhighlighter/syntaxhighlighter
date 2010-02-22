<?php
/*
 *  $Id: ChmodTask.php 242 2007-09-26 15:39:45Z mrook $
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
include_once 'phing/types/FileSet.php';

/**
 * Task that changes the permissions on a file/directory.
 *
 * @author    Manuel Holtgrewe <grin@gmx.net>
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.12 $
 * @package   phing.tasks.system
 */
class ChmodTask extends Task {

    private $file;

    private $mode;

    private $filesets = array();

    private $filesystem;
	
	private $quiet = false;	
	private $failonerror = true;
	private $verbose = true;
    
	/**
	 * This flag means 'note errors to the output, but keep going'
	 * @see setQuiet()
	 */
    function setFailonerror($bool) {
        $this->failonerror = $bool;
    }	

    /**
     * Set quiet mode, which suppresses warnings if chmod() fails.
	 * @see setFailonerror()
     */
    function setQuiet($bool) {
        $this->quiet = $bool;
        if ($this->quiet) {
            $this->failonerror = false;
        }
    }
    
    /**
     * Set verbosity, which if set to false surpresses all but an overview
     * of what happened.
     */
    function setVerbose($bool) {
        $this->verbose = (bool)$bool;
    }
	
    /**
     * Sets a single source file to touch.  If the file does not exist
     * an empty file will be created.
     */
    function setFile(PhingFile $file) {        
        $this->file = $file;
    } 

    function setMode($str) {
        $this->mode = $str;
    }

    /**
     * Nested creator, adds a set of files (nested fileset attribute).
     */
    function createFileSet() {
        $num = array_push($this->filesets, new FileSet());
        return $this->filesets[$num-1];
    }

    /**
     * Execute the touch operation.
     * @return void
     */
    function main() {
        // Check Parameters
        $this->checkParams();       
        $this->chmod();
    }
    
    /**
     * Ensure that correct parameters were passed in.
     * @return void
     */
    private function checkParams() {
    
        if ($this->file === null && empty($this->filesets)) {
            throw new BuildException("Specify at least one source - a file or a fileset.");
        }

        if ($this->mode === null) {
            throw new BuildException("You have to specify an octal mode for chmod.");
        }

        // check for mode to be in the correct format
        if (!preg_match('/^([0-7]){3,4}$/', $this->mode)) {
            throw new BuildException("You have specified an invalid mode.");
        }
     
    }

    /**
     * Does the actual work.
     * @return void
     */
    private function chmod() {
    	
		if (strlen($this->mode) === 4) {
			$mode = octdec($this->mode);
		} else {
			// we need to prepend the 0 before converting
			$mode = octdec("0". $this->mode);
		}
        
        // counters for non-verbose output
        $total_files = 0;
        $total_dirs = 0;
        
        // one file
        if ($this->file !== null) {
            $total_files = 1;
            $this->chmodFile($this->file, $mode);
        }

        // filesets
        foreach($this->filesets as $fs) {
                    
            $ds = $fs->getDirectoryScanner($this->project);
            $fromDir = $fs->getDir($this->project);

            $srcFiles = $ds->getIncludedFiles();
            $srcDirs = $ds->getIncludedDirectories();

            $filecount = count($srcFiles);
            $total_files = $total_files + $filecount;
            for ($j = 0; $j < $filecount; $j++) {
                $this->chmodFile(new PhingFile($fromDir, $srcFiles[$j]), $mode);
            }

            $dircount = count($srcDirs);
            $total_dirs = $total_dirs + $dircount;
            for ($j = 0; $j <  $dircount; $j++) {
                $this->chmodFile(new PhingFile($fromDir, $srcDirs[$j]), $mode);
            }
        }

        if (!$this->verbose) {
            $this->log('Total files changed to ' . vsprintf('%o', $mode) . ': ' . $total_files);
            $this->log('Total directories changed to ' . vsprintf('%o', $mode) . ': ' . $total_dirs);
        }

    }

	/**
	 * Actually change the mode for the file.
	 * @param PhingFile $file
	 * @param int $mode
	 */
    private function chmodFile(PhingFile $file, $mode) {
        if ( !$file->exists() ) {
            throw new BuildException("The file " . $file->__toString() . " does not exist");
        }   
		     
		try {
			$file->setMode($mode);
			if ($this->verbose) {
				$this->log("Changed file mode on '" . $file->__toString() ."' to " . vsprintf("%o", $mode));
			}
		} catch (Exception $e) {
			if($this->failonerror) {
				throw $e;
			} else {
				$this->log($e->getMessage(), $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
			}
		}
    }
	
}


