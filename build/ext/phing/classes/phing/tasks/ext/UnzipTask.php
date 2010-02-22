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

require_once 'phing/tasks/ext/ExtractBaseTask.php';
require_once 'phing/system/io/FileSystem.php';
require_once 'phing/lib/Zip.php';

/**
 * Extracts one or several zip archive using PEAR Archive_Zip (which is presently unreleased
 * and included with Phing).
 *
 * @author    Joakim Bodin <joakim.bodin+phing@gmail.com>
 * @version   $Revision: 1.0 $
 * @package   phing.tasks.ext
 * @since     2.2.0
 */
class UnzipTask extends ExtractBaseTask {
    
    protected function extractArchive(PhingFile $zipfile)
    {
        $extractParams = array('add_path' => $this->todir->getAbsolutePath());
        if(!empty($this->removepath))
        {
            $extractParams['remove_path'] = $this->removepath;
        }
        
        $this->log("Extracting zip: " . $zipfile->__toString() . ' to ' . $this->todir->__toString(), Project::MSG_INFO);
        
    	try {
        	$zip = new Archive_Zip($zipfile->getAbsolutePath());
        	
        	$extractResponse = $zip->extract($extractParams);
        	if(is_array($extractResponse)) {
        	    foreach ($extractResponse as $extractedPath) {
        	    	$this->log('Extracted' . $extractedPath['stored_filename'] . ' to ' . $this->todir->__toString(), Project::MSG_VERBOSE);
        	    }
        	} else if ($extractResponse === 0) {
        	    throw new BuildException('Failed to extract zipfile: ' . $zip->errorInfo(true));
        	}
        } catch (IOException $ioe) {
            $msg = "Could not extract ZIP: " . $ioe->getMessage();
            throw new BuildException($msg, $ioe, $this->getLocation());
        }
    }
    
    protected function listArchiveContent(PhingFile $zipfile)
    {
        $zip = new Archive_Zip($zipfile->getAbsolutePath());
        return $zip->listContent();
    }
}