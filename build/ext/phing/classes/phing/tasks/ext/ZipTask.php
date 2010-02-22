<?php
/*
 *  $Id: ZipTask.php 240 2007-09-25 20:43:37Z mrook $
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

require_once 'phing/tasks/system/MatchingTask.php';
include_once 'phing/util/SourceFileScanner.php';
include_once 'phing/mappers/MergeMapper.php';
include_once 'phing/util/StringHelper.php';
include_once 'phing/lib/Zip.php';

/**
 * Creates a zip archive using PEAR Archive_Zip (which is presently unreleased
 * and included with Phing).
 *
 * @author    Michiel Rook <michiel.rook@gmail.com>
 * @version   $Revision: 1.2 $
 * @package   phing.tasks.ext
 * @since     2.1.0
 */
class ZipTask extends MatchingTask {
    
	/**
	 * @var PhingFile
	 */
    private $zipFile;
    
    /**
     * @var PhingFile
     */
    private $baseDir;
	
    /**
     * Whether to include empty dirs in the archive.
     */
    private $includeEmpty = true;
    
    private $filesets = array();
    private $fileSetFiles = array();

    /**
     * Add a new fileset.
     * @return FileSet
     */
    public function createFileSet() {
        $this->fileset = new ZipFileSet();
        $this->filesets[] = $this->fileset;
        return $this->fileset;
    }

    /**
     * Set is the name/location of where to create the zip file.
     * @param PhingFile $destFile The output of the zip
     */
    public function setDestFile(PhingFile $destFile) {
        $this->zipFile = $destFile;
    }

    /**
     * This is the base directory to look in for things to zip.
     * @param PhingFile $baseDir
     */
    public function setBasedir(PhingFile $baseDir) {
        $this->baseDir = $baseDir;
    }

    /**
     * Set the include empty dirs flag.
     * @param  boolean  Flag if empty dirs should be tarred too
     * @return void
     * @access public
     */
    function setIncludeEmptyDirs($bool) {
        $this->includeEmpty = (boolean) $bool;
    }
    
    /**
     * do the work
     * @throws BuildException
     */
    public function main() {
    
        if ($this->zipFile === null) {
            throw new BuildException("zipfile attribute must be set!", $this->getLocation());
        }

        if ($this->zipFile->exists() && $this->zipFile->isDirectory()) {
            throw new BuildException("zipfile is a directory!", $this->getLocation());
        }

        if ($this->zipFile->exists() && !$this->zipFile->canWrite()) {
            throw new BuildException("Can not write to the specified zipfile!", $this->getLocation());
        }

        // shouldn't need to clone, since the entries in filesets
        // themselves won't be modified -- only elements will be added
        $savedFileSets = $this->filesets;
        
        try {
            if ($this->baseDir !== null) {
                if (!$this->baseDir->exists()) {
                    throw new BuildException("basedir does not exist!", $this->getLocation());
                }
                
                if (empty($this->filesets))
                {
	                // add the main fileset to the list of filesets to process.
	                $mainFileSet = new ZipFileSet($this->fileset);
	                $mainFileSet->setDir($this->baseDir);
	                $this->filesets[] = $mainFileSet;
                }
            }

            if (empty($this->filesets)) {
                throw new BuildException("You must supply either a basedir "
                                         . "attribute or some nested filesets.",
                                         $this->getLocation());
            }                        
            
            // check if zip is out of date with respect to each
            // fileset
            $upToDate = true;
            foreach($this->filesets as $fs) {
            	$files = $fs->getFiles($this->project, $this->includeEmpty);
                if (!$this->archiveIsUpToDate($files, $fs->getDir($this->project))) {
                    $upToDate = false;
                }
                for ($i=0, $fcount=count($files); $i < $fcount; $i++) {
                    if ($this->zipFile->equals(new PhingFile($fs->getDir($this->project), $files[$i]))) {
                        throw new BuildException("A zip file cannot include itself", $this->getLocation());
                    }
                }
            }
            
            if ($upToDate) {
                $this->log("Nothing to do: " . $this->zipFile->__toString() . " is up to date.", Project::MSG_INFO);
                return;
            }

            $this->log("Building zip: " . $this->zipFile->__toString(), Project::MSG_INFO);
            
            $zip = new Archive_Zip($this->zipFile->getAbsolutePath());
            
            foreach($this->filesets as $fs) {
            	
            	$files = $fs->getFiles($this->project, $this->includeEmpty);
            	
                $fsBasedir = (null != $this->baseDir) ? $this->baseDir :
									$fs->getDir($this->project);
                
                $filesToZip = array();
                for ($i=0, $fcount=count($files); $i < $fcount; $i++) {
                    $f = new PhingFile($fsBasedir, $files[$i]);
                    $filesToZip[] = $f->getAbsolutePath();
                    $this->log("Adding " . $f->getPath() . " to archive.", Project::MSG_VERBOSE);                        
                }
                $zip->add($filesToZip, array('remove_path' => $fsBasedir->getCanonicalPath()));
            }
                         
                
        } catch (IOException $ioe) {
                $msg = "Problem creating ZIP: " . $ioe->getMessage();
                $this->filesets = $savedFileSets;
                throw new BuildException($msg, $ioe, $this->getLocation());
        }
        
        $this->filesets = $savedFileSets;
    }
           
    /**
     * @param array $files array of filenames
     * @param PhingFile $dir
     * @return boolean
     */
    protected function archiveIsUpToDate($files, $dir) {
        $sfs = new SourceFileScanner($this);
        $mm = new MergeMapper();
        $mm->setTo($this->zipFile->getAbsolutePath());
        return count($sfs->restrict($files, $dir, null, $mm)) == 0;
    }
   
}




/**
 * This is a FileSet with the to specify permissions.
 * 
 * Permissions are currently not implemented by PEAR Archive_Tar,
 * but hopefully they will be in the future.
 * 
 */
class ZipFileSet extends FileSet {

    private $files = null;

    /**
     *  Get a list of files and directories specified in the fileset.
     *  @return array a list of file and directory names, relative to
     *    the baseDir for the project.
     */
    public function getFiles(Project $p, $includeEmpty = true) {
    
        if ($this->files === null) {
        
            $ds = $this->getDirectoryScanner($p);
            $this->files = $ds->getIncludedFiles();
            
            if ($includeEmpty) {
            
	            // first any empty directories that will not be implicitly added by any of the files
				$implicitDirs = array();
				foreach($this->files as $file) {
					$implicitDirs[] = dirname($file);
				} 
				
				$incDirs = $ds->getIncludedDirectories();
				
				// we'll need to add to that list of implicit dirs any directories
				// that contain other *directories* (and not files), since otherwise
				// we get duplicate directories in the resulting tar
				foreach($incDirs as $dir) {
					foreach($incDirs as $dircheck) {
						if (!empty($dir) && $dir == dirname($dircheck)) {
							$implicitDirs[] = $dir;
						}
					}
				}
				
				$implicitDirs = array_unique($implicitDirs);
				
				// Now add any empty dirs (dirs not covered by the implicit dirs)
				// to the files array. 
				
				foreach($incDirs as $dir) { // we cannot simply use array_diff() since we want to disregard empty/. dirs
					if ($dir != "" && $dir != "." && !in_array($dir, $implicitDirs)) {
						// it's an empty dir, so we'll add it.
						$this->files[] = $dir;
					}
				}
			} // if $includeEmpty
			
        } // if ($this->files===null)
        
        return $this->files;
    }

}
