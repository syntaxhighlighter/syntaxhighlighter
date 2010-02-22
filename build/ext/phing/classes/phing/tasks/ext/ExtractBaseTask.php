<?php
/*
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

/**
 * Base class for extracting tasks such as Unzip and Untar.
 *
 * @author    Joakim Bodin <joakim.bodin+phing@gmail.com>
 * @version   $Revision: 1.0 $
 * @package   phing.tasks.ext
 * @since     2.2.0
 */
abstract class ExtractBaseTask extends MatchingTask {
    /**
     * @var PhingFile $file
     */
    protected $file;
    /**
     * @var PhingFile $todir
     */
    protected $todir;
    protected $removepath;
    protected $filesets = array(); // all fileset objects assigned to this task

    /**
     * Add a new fileset.
     * @return FileSet
     */
    public function createFileSet() {
        $this->fileset = new FileSet();
        $this->filesets[] = $this->fileset;
        return $this->fileset;
    }

    /**
     * Set the name of the zip file to extract.
     * @param PhingFile $file zip file to extract
     */
    public function setFile(PhingFile $file) {
        $this->file = $file;
    }

    /**
     * This is the base directory to look in for things to zip.
     * @param PhingFile $baseDir
     */
    public function setToDir(PhingFile $todir) {
        $this->todir = $todir;
    }
    
    public function setRemovePath($removepath)
    {
    	$this->removepath = $removepath;
    }

    /**
     * do the work
     * @throws BuildException
     */
    public function main() {
    
        $this->validateAttributes();
        
        $filesToExtract = array();
        if ($this->file !== null) {
            if(!$this->isDestinationUpToDate($this->file)) {
                $filesToExtract[] = $this->file;
            } else {
            	$this->log('Nothing to do: ' . $this->todir->getAbsolutePath() . ' is up to date for ' .  $this->file->getCanonicalPath(), Project::MSG_INFO);
            }
        }
        
        foreach($this->filesets as $compressedArchiveFileset) {
            $compressedArchiveDirScanner = $compressedArchiveFileset->getDirectoryScanner($this->project);
            $compressedArchiveFiles = $compressedArchiveDirScanner->getIncludedFiles();
            $compressedArchiveDir = $compressedArchiveFileset->getDir($this->project);
            
            foreach ($compressedArchiveFiles as $compressedArchiveFilePath) {
                $compressedArchiveFile = new PhingFile($compressedArchiveDir, $compressedArchiveFilePath);
                if($compressedArchiveFile->isDirectory())
                {
                    throw new BuildException($compressedArchiveFile->getAbsolutePath() . ' compressed archive cannot be a directory.');
                }
                
            	if(!$this->isDestinationUpToDate($compressedArchiveFile)) {
            	   $filesToExtract[] = $compressedArchiveFile;
            	} else {
            		$this->log('Nothing to do: ' . $this->todir->getAbsolutePath() . ' is up to date for ' .  $compressedArchiveFile->getCanonicalPath(), Project::MSG_INFO);
            	}
            }
        }
        
        foreach ($filesToExtract as $compressedArchiveFile) {
            $this->extractArchive($compressedArchiveFile);
        }
    }
    
    abstract protected function extractArchive(PhingFile $compressedArchiveFile);
    
    /**
     * @param array $files array of filenames
     * @param PhingFile $dir
     * @return boolean
     */
    protected function isDestinationUpToDate(PhingFile $compressedArchiveFile) {
        if (!$compressedArchiveFile->exists()) {
        	throw new BuildException("Could not find file " . $compressedArchiveFile->__toString() . " to extract.");
        }
        
        $compressedArchiveContent = $this->listArchiveContent($compressedArchiveFile);
        if(is_array($compressedArchiveContent)) {
            
            $fileSystem = FileSystem::getFileSystem();
            foreach ($compressedArchiveContent as $compressArchivePathInfo) {
                $compressArchiveFilename = $compressArchivePathInfo['filename'];
                if(!empty($this->removepath) && strlen($compressArchiveFilename) >= strlen($this->removepath))
                {
                    $compressArchiveFilename = preg_replace('/^' . $this->removepath . '/','', $compressArchiveFilename);
                }
                $compressArchivePath = new PhingFile($this->todir, $compressArchiveFilename);
                
                if(!$compressArchivePath->exists() ||
                    $fileSystem->compareMTimes($compressedArchiveFile->getCanonicalPath(), $compressArchivePath->getCanonicalPath()) == 1) {
                    return false;
                }
            }
            
        }
        
        return true;
    }
    
    abstract protected function listArchiveContent(PhingFile $compressedArchiveFile);
    
    /**
     * Validates attributes coming in from XML
     *
     * @access  private
     * @return  void
     * @throws  BuildException
     */
    protected function validateAttributes() {
    
        if ($this->file === null && count($this->filesets) === 0) {
            throw new BuildException("Specify at least one source compressed archive - a file or a fileset.");
        }

        if ($this->todir === null) {
            throw new BuildException("todir must be set.");
        }
        
        if ($this->todir !== null && $this->todir->exists() && !$this->todir->isDirectory()) {
            throw new BuildException("todir must be a directory.");
        }

        if ($this->file !== null && $this->file->exists() && $this->file->isDirectory()) {
            throw new BuildException("Compressed archive file cannot be a directory.");
        }
        
        if ($this->file !== null && !$this->file->exists()) {
        	throw new BuildException("Could not find compressed archive file " . $this->file->__toString() . " to extract.");
        }
    }
    
}