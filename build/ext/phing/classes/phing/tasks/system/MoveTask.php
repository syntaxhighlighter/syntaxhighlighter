<?php
/*
 *  $Id: MoveTask.php 325 2007-12-20 15:44:58Z hans $
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

require_once 'phing/tasks/system/CopyTask.php';
include_once 'phing/system/io/PhingFile.php';
include_once 'phing/system/io/IOException.php';

/**
 * Moves a file or directory to a new file or directory.
 * 
 * By default, the destination file is overwritten if it
 * already exists.  When overwrite is turned off, then files
 * are only moved if the source file is newer than the
 * destination file, or when the destination file does not
 * exist.
 *
 * Source files and directories are only deleted when the file or
 * directory has been copied to the destination successfully.
 *
 * @version $Revision: 1.8 $
 * @package phing.tasks.system
 */
class MoveTask extends CopyTask {

    function __construct() {
        parent::__construct();
        $this->forceOverwrite = true;
    }
    
    /**
     * Validates attributes coming in from XML
     *
     * @access  private
     * @return  void
     * @throws  BuildException
     */
    protected function validateAttributes() {    
        if ($this->file !== null && $this->file->isDirectory()) {
			if (($this->destFile !== null && $this->destDir !== null)
				|| ($this->destFile === null && $this->destDir === null)) {
					throw new BuildException("One and only one of tofile and todir must be set.");
			}
            
            if ($this->destFile === null)
            {
				$this->destFile = new PhingFile($this->destDir, $this->file->getName());
			}
            
            if ($this->destDir === null)
            {
				$this->destDir = $this->destFile->getParentFile();
			}
            
			$this->completeDirMap[$this->file->getAbsolutePath()] = $this->destFile->getAbsolutePath();
			
			$this->file = null;
		} else {
			parent::validateAttributes();
		}
    }
    
    protected function doWork() {
		if (count($this->completeDirMap) > 0)
		{
			foreach ($this->completeDirMap as $from => $to)
			{
                $f = new PhingFile($from);
                $d = new PhingFile($to);
                
                $moved = false;
                try { // try to rename                    
                    $this->log("Attempting to rename $from to $to", $this->verbosity);
                    $this->renameFile($f, $d, $this->forceOverwrite);
                    $moved = true;
                } catch (IOException $ioe) {
                    $moved = false;
                    $this->log("Failed to rename $from to $to: " . $ioe->getMessage(), $this->verbosity);
                }
			}
		}
    
        $copyMapSize = count($this->fileCopyMap);
        if ($copyMapSize > 0) {
            // files to move
            $this->log("Moving $copyMapSize files to " . $this->destDir->getAbsolutePath());

            foreach($this->fileCopyMap as $from => $to) {
                if ($from == $to) {
                    $this->log("Skipping self-move of $from", $this->verbosity);
                    continue;
                }

                $moved = false;
                $f = new PhingFile($from);
                $d = new PhingFile($to);
                
                $moved = false;
                try { // try to rename                    
                    $this->log("Attempting to rename $from to $to", $this->verbosity);
                    $this->renameFile($f, $d, $this->forceOverwrite);
                    $moved = true;
                } catch (IOException $ioe) {
                    $moved = false;
                    $this->log("Failed to rename $from to $to: " . $ioe->getMessage(), $this->verbosity);
                }

                if (!$moved) {                    
                    try { // try to move
                        $this->log("Moving $from to $to", $this->verbosity);

                        $this->fileUtils->copyFile($f, $d, $this->forceOverwrite, $this->preserveLMT, $this->filterChains, $this->getProject());                        

                        $f = new PhingFile($fromFile);
                        $f->delete();
                    } catch (IOException $ioe) {
                        $msg = "Failed to move $from to $to: " . $ioe->getMessage();
                        throw new BuildException($msg, $this->location);
                    }
                } // if !moved
            } // foreach fileCopyMap
        } // if copyMapSize

        // handle empty dirs if appropriate
        if ($this->includeEmpty) {
            $e = array_keys($this->dirCopyMap);
            $count = 0;
            foreach ($e as $dir) {
                $d = new PhingFile((string) $dir);
                if (!$d->exists()) {
                    if (!$d->mkdirs()) {
                        $this->log("Unable to create directory " . $d->getAbsolutePath(), Project::MSG_ERR);
                    } else {
                        $count++;
                    }
                }
            }
            if ($count > 0) {
                $this->log("moved $count empty director" . ($count == 1 ? "y" : "ies") . " to " . $this->destDir->getAbsolutePath());
            }
        }

        if (count($this->filesets) > 0) {
            // process filesets
            foreach($this->filesets as $fs) {
                $dir = $fs->getDir($this->project);
                if ($this->okToDelete($dir)) {
                    $this->deleteDir($dir);
                }
            }
        }
    }

    /** Its only ok to delete a dir tree if there are no files in it. */
    private function okToDelete($d) {
        $list = $d->listDir();
        if ($list === null) {
            return false;     // maybe io error?
        }
        
        foreach($list as $s) {
            $f = new PhingFile($d, $s);
            if ($f->isDirectory()) {
                if (!$this->okToDelete($f)) {
                    return false;
                }
            } else {
                // found a file
                return false;
            }
        }
        return true;
    }

    /** Go and delete the directory tree. */
    private function deleteDir($d) {
    
        $list = $d->listDir();
        if ($list === null) {
            return;      // on an io error list() can return null
        }
        
        foreach($list as $fname) {
            $f = new PhingFile($d, $fname);
            if ($f->isDirectory()) {
                $this->deleteDir($f);
            } else {
                throw new BuildException("UNEXPECTED ERROR - The file " . $f->getAbsolutePath() . " should not exist!");
            }
        }

        $this->log("Deleting directory " . $d->getPath(), $this->verbosity);
        try {
            $d->delete();
        } catch (Exception $e) {
            throw new BuildException("Unable to delete directory " . $d->__toString() . ": " . $e->getMessage());
        }
    }

    /**
     * Attempts to rename a file from a source to a destination.
     * If overwrite is set to true, this method overwrites existing file
     * even if the destination file is newer.
     * Otherwise, the source f
     * ile is renamed only if the destination file #
     * is older than it.
     */
    private function renameFile(PhingFile $sourceFile, PhingFile $destFile, $overwrite) {
        $renamed = true;

        // ensure that parent dir of dest file exists!
        $parent = $destFile->getParentFile();
        if ($parent !== null) {
            if (!$parent->exists()) {
                $parent->mkdirs();
            }
        }
        if ($destFile->exists()) {
            try {
                $destFile->delete();
            } catch (Exception $e) {
                throw new BuildException("Unable to remove existing file " . $destFile->__toString() . ": " . $e->getMessage());
            }
        }
        $renamed = $sourceFile->renameTo($destFile);

        return $renamed;
    }
}

