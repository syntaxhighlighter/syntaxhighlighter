<?php
/*
 *  $Id: FileUtils.php 325 2007-12-20 15:44:58Z hans $
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

include_once 'phing/system/lang/Character.php';
include_once 'phing/util/StringHelper.php';
include_once 'phing/system/io/BufferedReader.php';
include_once 'phing/system/io/BufferedWriter.php';
include_once 'phing/filters/util/ChainReaderHelper.php';
include_once 'phing/system/io/PhingFile.php';

/**
 * File utility class.
 * - handles os independent stuff etc
 * - mapper stuff
 * - filter stuff
 *
 * @package  phing.util
 * @version  $Revision: 1.10 $
 */
class FileUtils {
        
    /**
     * Returns a new Reader with filterchains applied.  If filterchains are empty,
     * simply returns passed reader.
     * 
     * @param Reader $in Reader to modify (if appropriate).
     * @param array &$filterChains filter chains to apply.
     * @param Project $project
     * @return Reader Assembled Reader (w/ filter chains).
     */
    public static function getChainedReader(Reader $in, &$filterChains, Project $project) {
        if (!empty($filterChains)) {
            $crh = new ChainReaderHelper();
            $crh->setBufferSize(65536); // 64k buffer, but isn't being used (yet?)
            $crh->setPrimaryReader($in);
            $crh->setFilterChains($filterChains);
            $crh->setProject($project);
            $rdr = $crh->getAssembledReader();
            return $rdr;
        } else {
            return $in;
        }
    }
    
    /**
     * Copies a file using filter chains.
     * 
     * @param PhingFile $sourceFile
     * @param PhingFile $destFile
     * @param boolean $overwrite
     * @param boolean $preserveLastModified
     * @param array $filterChains 
     * @param Project $project
     * @return void
     */
    function copyFile(PhingFile $sourceFile, PhingFile $destFile, $overwrite = false, $preserveLastModified = true, &$filterChains = null, Project $project) {
       
        if ($overwrite || !$destFile->exists() || $destFile->lastModified() < $sourceFile->lastModified()) {
            if ($destFile->exists() && $destFile->isFile()) {
                $destFile->delete();
            }

            // ensure that parent dir of dest file exists!
            $parent = $destFile->getParentFile();
            if ($parent !== null && !$parent->exists()) {
                $parent->mkdirs();
            }

            if ((is_array($filterChains)) && (!empty($filterChains))) {
                
                $in = self::getChainedReader(new BufferedReader(new FileReader($sourceFile)), $filterChains, $project);
                $out = new BufferedWriter(new FileWriter($destFile));                
                
                // New read() methods returns a big buffer.                
                while(-1 !== ($buffer = $in->read())) { // -1 indicates EOF
                    $out->write($buffer);
                }
                
                if ( $in !== null )
                    $in->close();
                if ( $out !== null )
                    $out->close();
            } else {
                // simple copy (no filtering)
                $sourceFile->copyTo($destFile);
            }

            if ($preserveLastModified) {
                $destFile->setLastModified($sourceFile->lastModified());
            }

        }
    }

    /**
     * Interpret the filename as a file relative to the given file -
     * unless the filename already represents an absolute filename.
     *
     * @param  $file the "reference" file for relative paths. This
     *         instance must be an absolute file and must not contain
     *         ./ or ../ sequences (same for \ instead of /).
     * @param  $filename a file name
     *
     * @return PhingFile A PhingFile object pointing to an absolute file that doesn't contain ./ or ../ sequences
     *         and uses the correct separator for the current platform.
     */
    function resolveFile($file, $filename) {
        // remove this and use the static class constant File::seperator
        // as soon as ZE2 is ready
        $fs = FileSystem::getFileSystem();

        $filename = str_replace('/', $fs->getSeparator(), str_replace('\\', $fs->getSeparator(), $filename));

        // deal with absolute files
        if (StringHelper::startsWith($fs->getSeparator(), $filename) ||
                (strlen($filename) >= 2 && Character::isLetter($filename{0}) && $filename{1} === ':')) {
            return new PhingFile($this->normalize($filename));
        }

        if (strlen($filename) >= 2 && Character::isLetter($filename{0}) && $filename{1} === ':') {
            return new PhingFile($this->normalize($filename));
        }

        $helpFile = new PhingFile($file->getAbsolutePath());

        $tok = strtok($filename, $fs->getSeparator());
        while ($tok !== false) {
            $part = $tok;
            if ($part === '..') {
                $parentFile = $helpFile->getParent();
                if ($parentFile === null) {
                    $msg = "The file or path you specified ($filename) is invalid relative to ".$file->getPath();
                    throw new IOException($msg);
                }
                $helpFile = new PhingFile($parentFile);
            } else if ($part === '.') {
                // Do nothing here
            } else {
                $helpFile = new PhingFile($helpFile, $part);
            }
            $tok = strtok($fs->getSeparator());
        }
        return new PhingFile($helpFile->getAbsolutePath());
    }

    /**
     * Normalize the given absolute path.
     *
     * This includes:
     *   - Uppercase the drive letter if there is one.
     *   - Remove redundant slashes after the drive spec.
     *   - resolve all ./, .\, ../ and ..\ sequences.
     *   - DOS style paths that start with a drive letter will have
     *     \ as the separator.
     * @param string $path Path to normalize.
     * @return string
     */
    function normalize($path) {
    
        $path = (string) $path;
        $orig = $path;

        $path = str_replace('/', DIRECTORY_SEPARATOR, str_replace('\\', DIRECTORY_SEPARATOR, $path));

        // make sure we are dealing with an absolute path
        if (!StringHelper::startsWith(DIRECTORY_SEPARATOR, $path)
                && !(strlen($path) >= 2 && Character::isLetter($path{0}) && $path{1} === ':')) {
            throw new IOException("$path is not an absolute path");
        }

        $dosWithDrive = false;
        $root = null;

        // Eliminate consecutive slashes after the drive spec

        if (strlen($path) >= 2 && Character::isLetter($path{0}) && $path{1} === ':') {
            $dosWithDrive = true;

            $ca = str_replace('/', '\\', $path);
            $ca = StringHelper::toCharArray($ca);

            $path = strtoupper($ca[0]).':';
            
            for ($i=2, $_i=count($ca); $i < $_i; $i++) {
                if (($ca[$i] !== '\\') ||
                        ($ca[$i] === '\\' && $ca[$i - 1] !== '\\')
                   ) {
                    $path .= $ca[$i];
                }
            }
         
            $path = str_replace('\\', DIRECTORY_SEPARATOR, $path);

            if (strlen($path) == 2) {
                $root = $path;
                $path = "";
            } else {
                $root = substr($path, 0, 3);
                $path = substr($path, 3);
            }

        } else {
            if (strlen($path) == 1) {
                $root = DIRECTORY_SEPARATOR;
                $path = "";
            } else if ($path{1} == DIRECTORY_SEPARATOR) {
                // UNC drive
                $root = DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR;
                $path = substr($path, 2);
            }
            else {
                $root = DIRECTORY_SEPARATOR;
                $path = substr($path, 1);
            }
        }

        $s = array();
        array_push($s, $root);
        $tok = strtok($path, DIRECTORY_SEPARATOR);
        while ($tok !== false) {            
            $thisToken = $tok;
            if ("." === $thisToken) {
                $tok = strtok(DIRECTORY_SEPARATOR);
                continue;
            } elseif (".." === $thisToken) {
                if (count($s) < 2) {
                    // using '..' in path that is too short
                    throw new IOException("Cannot resolve path: $orig");
                } else {
                    array_pop($s);
                }
            } else { // plain component
                array_push($s, $thisToken);
            }
            $tok = strtok(DIRECTORY_SEPARATOR);
        }

        $sb = "";
        for ($i=0,$_i=count($s); $i < $_i; $i++) {
            if ($i > 1) {
                // not before the filesystem root and not after it, since root
                // already contains one
                $sb .= DIRECTORY_SEPARATOR;
            }
            $sb .= (string) $s[$i];
        }


        $path = (string) $sb;
        if ($dosWithDrive === true) {
            $path = str_replace('/', '\\', $path);
        }
        return $path;
    }
    
    /**
     * @return boolean Whether contents of two files is the same.
     */
    public function contentEquals(PhingFile $file1, PhingFile $file2) {
        
        if (!($file1->exists() || $file2->exists())) {
            return false;
        }

        if (!($file1->canRead() || $file2->canRead())) {
            return false;
        }
        
        $c1 = file_get_contents($file1->getAbsolutePath());
        $c2 = file_get_contents($file2->getAbsolutePath());
        
        return trim($c1) == trim($c2);    
    }
    
}

