<?php
/*
 *  $Id: CopyTask.php 235 2007-09-05 18:42:02Z hans $
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
include_once 'phing/system/io/PhingFile.php';
include_once 'phing/util/FileUtils.php';
include_once 'phing/util/SourceFileScanner.php';
include_once 'phing/mappers/IdentityMapper.php';
include_once 'phing/mappers/FlattenMapper.php';

/**
 * A phing copy task.  Copies a file or directory to a new file
 * or directory.  Files are only copied if the source file is newer
 * than the destination file, or when the destination file does not
 * exist. It is possible to explictly overwrite existing files.
 *
 * @author   Andreas Aderhold, andi@binarycloud.com
 * @version  $Revision: 1.16 $ $Date: 2007-09-05 14:42:02 -0400 (Wed, 05 Sep 2007) $
 * @package  phing.tasks.system
 */
class CopyTask extends Task {
    
    protected $file          = null;   // the source file (from xml attribute)
    protected $destFile      = null;   // the destiantion file (from xml attribute)
    protected $destDir       = null;   // the destination dir (from xml attribute)
    protected $overwrite     = false;  // overwrite destination (from xml attribute)
    protected $preserveLMT   = true;   // sync timestamps (from xml attribute)
    protected $includeEmpty  = true;   // include empty dirs? (from XML)
    protected $flatten       = false;  // apply the FlattenMapper right way (from XML)
    protected $mapperElement = null;

    protected $fileCopyMap   = array(); // asoc array containing mapped file names
    protected $dirCopyMap    = array(); // asoc array containing mapped file names
    protected $completeDirMap= array(); // asoc array containing complete dir names
    protected $fileUtils     = null;    // a instance of fileutils
    protected $filesets      = array(); // all fileset objects assigned to this task
    protected $filterChains  = array(); // all filterchains objects assigned to this task

    protected $verbosity     = Project::MSG_VERBOSE;

    /**
     * Sets up this object internal stuff. i.e. the Fileutils instance
     *
     * @return object   The CopyTask instnace
     * @access public
     */
    function __construct() {
        $this->fileUtils = new FileUtils();
    }

    /**
     * Set the overwrite flag. IntrospectionHelper takes care of
     * booleans in set* methods so we can assume that the right
     * value (boolean primitive) is coming in here.
     *
     * @param  boolean  Overwrite the destination file(s) if it/they already exist
     * @return void
     * @access public
     */
    function setOverwrite($bool) {
        $this->overwrite = (boolean) $bool;
    }

    /**
     * Used to force listing of all names of copied files.
     * @param boolean $verbosity
     */
    function setVerbose($verbosity) {
        if ($verbosity) {
            $this->verbosity = Project::MSG_INFO;
        } else {
            $this->verbosity = Project::MSG_VERBOSE;
        }
    }
    
    /**
     * Set the preserve timestmap flag. IntrospectionHelper takes care of
     * booleans in set* methods so we can assume that the right
     * value (boolean primitive) is coming in here.
     *
     * @param  boolean  Preserve the timestamp on the destination file
     * @return void
     * @access public
     */
    function setTstamp($bool) {
        $this->preserveLMT = (boolean) $bool;
    }


    /**
     * Set the include empty dirs flag. IntrospectionHelper takes care of
     * booleans in set* methods so we can assume that the right
     * value (boolean primitive) is coming in here.
     *
     * @param  boolean  Flag if empty dirs should be cpoied too
     * @return void
     * @access public
     */
    function setIncludeEmptyDirs($bool) {
        $this->includeEmpty = (boolean) $bool;
    }


    /**
     * Set the file. We have to manually take care of the
     * type that is coming due to limited type support in php
     * in and convert it manually if neccessary.
     *
     * @param  string/object  The source file. Either a string or an PhingFile object
     * @return void
     * @access public
     */
    function setFile(PhingFile $file) {        
        $this->file = $file;
    }


    /**
     * Set the toFile. We have to manually take care of the
     * type that is coming due to limited type support in php
     * in and convert it manually if neccessary.
     *
     * @param  string/object  The dest file. Either a string or an PhingFile object
     * @return void
     * @access public
     */
    function setTofile(PhingFile $file) {       
        $this->destFile = $file;
    }


    /**
     * Set the toDir. We have to manually take care of the
     * type that is coming due to limited type support in php
     * in and convert it manually if neccessary.
     *
     * @param  string/object  The directory, either a string or an PhingFile object
     * @return void
     * @access public
     */
    function setTodir(PhingFile $dir) {        
        $this->destDir = $dir;
    }

    /**
     * Nested creator, creates a FileSet for this task
     *
     * @access  public
     * @return  object  The created fileset object
     */
    function createFileSet() {
        $num = array_push($this->filesets, new FileSet());
        return $this->filesets[$num-1];
    }

    /**
     * Creates a filterchain
     *
     * @access public
     * @return  object  The created filterchain object
     */
    function createFilterChain() {
        $num = array_push($this->filterChains, new FilterChain($this->project));
        return $this->filterChains[$num-1];
    }

    /**
     * Nested creator, creates one Mapper for this task
     *
     * @access  public
     * @return  object  The created Mapper type object
     * @throws  BuildException
     */
    function createMapper() {
        if ($this->mapperElement !== null) {
            throw new BuildException("Cannot define more than one mapper", $this->location);
        }
        $this->mapperElement = new Mapper($this->project);
        return $this->mapperElement;
    }

    /**
     * The main entry point where everything gets in motion.
     *
     * @access  public
     * @return  true on success
     * @throws  BuildException
     */
    function main() {
    
        $this->validateAttributes();

        if ($this->file !== null) {
            if ($this->file->exists()) {
                if ($this->destFile === null) {
                    $this->destFile = new PhingFile($this->destDir, (string) $this->file->getName());
                }
                if ($this->overwrite === true || ($this->file->lastModified() > $this->destFile->lastModified())) {
                    $this->fileCopyMap[$this->file->getAbsolutePath()] = $this->destFile->getAbsolutePath();
                } else {
                    $this->log($this->file->getName()." omitted, is up to date");
                }
            } else {
                // terminate build
                throw new BuildException("Could not find file " . $this->file->__toString() . " to copy.");
            }
        }

        $project = $this->getProject();

        // process filesets
        foreach($this->filesets as $fs) {
            $ds = $fs->getDirectoryScanner($project);
            $fromDir  = $fs->getDir($project);
            $srcFiles = $ds->getIncludedFiles();
            $srcDirs  = $ds->getIncludedDirectories();
            
            if (!$this->flatten && $this->mapperElement === null)
            {
				$this->completeDirMap[$fromDir->getAbsolutePath()] = $this->destDir->getAbsolutePath();
			}
            
            $this->_scan($fromDir, $this->destDir, $srcFiles, $srcDirs);
        }

        // go and copy the stuff
        $this->doWork();

        if ($this->destFile !== null) {
            $this->destDir = null;
        }
    }

    /**
     * Validates attributes coming in from XML
     *
     * @access  private
     * @return  void
     * @throws  BuildException
     */
    protected function validateAttributes() {
    
        if ($this->file === null && count($this->filesets) === 0) {
            throw new BuildException("CopyTask. Specify at least one source - a file or a fileset.");
        }

        if ($this->destFile !== null && $this->destDir !== null) {
            throw new BuildException("Only one of destfile and destdir may be set.");
        }

        if ($this->destFile === null && $this->destDir === null) {
            throw new BuildException("One of destfile or destdir must be set.");
        }

        if ($this->file !== null && $this->file->exists() && $this->file->isDirectory()) {
            throw new BuildException("Use a fileset to copy directories.");
        }

        if ($this->destFile !== null && count($this->filesets) > 0) {
            throw new BuildException("Cannot concatenate multple files into a single file.");
        }

        if ($this->destFile !== null) {
            $this->destDir = new PhingFile($this->destFile->getParent());
        }
    }

    /**
     * Compares source files to destination files to see if they
     * should be copied.
     *
     * @access  private
     * @return  void
     */
    private function _scan(&$fromDir, &$toDir, &$files, &$dirs) {
        /* mappers should be generic, so we get the mappers here and
        pass them on to builMap. This method is not redundan like it seems */
        $mapper = null;
        if ($this->mapperElement !== null) {
            $mapper = $this->mapperElement->getImplementation();
        } else if ($this->flatten) {
            $mapper = new FlattenMapper();
        } else {
            $mapper = new IdentityMapper();
        }
        $this->buildMap($fromDir, $toDir, $files, $mapper, $this->fileCopyMap);
        $this->buildMap($fromDir, $toDir, $dirs, $mapper, $this->dirCopyMap);
    }

    /**
     * Builds a map of filenames (from->to) that should be copied
     *
     * @access  private
     * @return  void
     */
    private function buildMap(&$fromDir, &$toDir, &$names, &$mapper, &$map) {
        $toCopy = null;
        if ($this->overwrite) {
            $v = array();
            foreach($names as $name) {
                $result = $mapper->main($name);
                if ($result !== null) {
                    $v[] = $name;
                }
            }
            $toCopy = $v;
        } else {
            $ds = new SourceFileScanner($this);
            $toCopy = $ds->restrict($names, $fromDir, $toDir, $mapper);
        }

        for ($i=0,$_i=count($toCopy); $i < $_i; $i++) {
            $src  = new PhingFile($fromDir, $toCopy[$i]);
            $mapped = $mapper->main($toCopy[$i]);
            $dest = new PhingFile($toDir, $mapped[0]);
            $map[$src->getAbsolutePath()] = $dest->getAbsolutePath();
        }
    }


    /**
     * Actually copies the files
     *
     * @access  private
     * @return  void
     * @throws  BuildException
     */
    protected function doWork() {
		
		// These "slots" allow filters to retrieve information about the currently-being-process files		
		$fromSlot = $this->getRegisterSlot("currentFromFile");
		$fromBasenameSlot = $this->getRegisterSlot("currentFromFile.basename");	

		$toSlot = $this->getRegisterSlot("currentToFile");
		$toBasenameSlot = $this->getRegisterSlot("currentToFile.basename");	
		
        $mapSize = count($this->fileCopyMap);
        $total = $mapSize;
        if ($mapSize > 0) {
            $this->log("Copying ".$mapSize." file".(($mapSize) === 1 ? '' : 's')." to ". $this->destDir->getAbsolutePath());
            // walks the map and actually copies the files
            $count=0;
            foreach($this->fileCopyMap as $from => $to) {
                if ($from === $to) {
                    $this->log("Skipping self-copy of " . $from, $this->verbosity);
                    $total--;
                    continue;
                }
                $this->log("From ".$from." to ".$to, $this->verbosity);
                try { // try to copy file
				
					$fromFile = new PhingFile($from);
					$toFile = new PhingFile($to);
					
                    $fromSlot->setValue($fromFile->getPath());
					$fromBasenameSlot->setValue($fromFile->getName());

					$toSlot->setValue($toFile->getPath());
					$toBasenameSlot->setValue($toFile->getName());
					
                    $this->fileUtils->copyFile($fromFile, $toFile, $this->overwrite, $this->preserveLMT, $this->filterChains, $this->getProject());
			
                    $count++;
                } catch (IOException $ioe) {
                    $this->log("Failed to copy " . $from . " to " . $to . ": " . $ioe->getMessage(), Project::MSG_ERR);
                }
            }
        }

        // handle empty dirs if appropriate
        if ($this->includeEmpty) {
            $destdirs = array_values($this->dirCopyMap);
            $count = 0;
            foreach ($destdirs as $destdir) {
                $d = new PhingFile((string) $destdir);
                if (!$d->exists()) {
                    if (!$d->mkdirs()) {
                        $this->log("Unable to create directory " . $d->__toString(), Project::MSG_ERR);
                    } else {
                        $count++;
                    }
                }
            }
            if ($count > 0) {
                $this->log("Copied ".$count." empty director" . ($count == 1 ? "y" : "ies") . " to " . $this->destDir->getAbsolutePath());
            }
        }
    }

}
