<?php

/*
 * $Id: IncludePathTask.php 144 2007-02-05 15:19:00Z hans $
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
include_once 'phing/types/Path.php';

/**
 * Adds a normalized path to the PHP include_path.
 * 
 * This provides a way to alter the include_path without editing any global php.ini settings
 * or PHP_CLASSPATH environment variable.
 * 
 * <code>
 *   <includepath classpath="new/path/here"/>
 * </code>
 * 
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.1 $
 * @package   phing.tasks.system
 */
class IncludePathTask extends Task {
   
    /**
     * Classname of task to register.
     * This can be a dot-path -- relative to a location on PHP include_path.
     * E.g. path.to.MyClass ->  path/to/MyClass.php
     * @var string
     */
    private $classname;
    
    /**
     * Path to add to PHP include_path to aid in finding specified class.
     * @var Path
     */
    private $classpath;
    
    /**
     * Refid to already defined classpath
     */
    private $classpathId;
    
    /**
     * Set the classpath to be used when searching for component being defined
     * 
     * @param Path $classpath An Path object containing the classpath.
     */
    public function setClasspath(Path $classpath) {
        if ($this->classpath === null) {
            $this->classpath = $classpath;
        } else {
            $this->classpath->append($classpath);
        }
    }

    /**
     * Create the classpath to be used when searching for component being defined
     */ 
    public function createClasspath() {
        if ($this->classpath === null) {
            $this->classpath = new Path($this->project);
        }
        return $this->classpath->createPath();
    }

    /**
     * Reference to a classpath to use when loading the files.
     */
    public function setClasspathRef(Reference $r) {
        $this->classpathId = $r->getRefId();
        $this->createClasspath()->setRefid($r);
    }

    
    /** Main entry point */
    public function main() {
    
        // Apparently casting to (string) no longer invokes __toString() automatically.
        if (is_object($this->classpath)) {
            $this->classpath = $this->classpath->__toString();
        }
        
        if (empty($this->classpath)) {
            throw new BuildException("Provided classpath was empty.");
        }
        
        $curr_parts = explode(PATH_SEPARATOR, get_include_path());
        $add_parts = explode(PATH_SEPARATOR, $this->classpath);
        $new_parts = array_diff($add_parts, $curr_parts);
        
        if ($new_parts) {
            $this->log("Prepending new include_path components: " . implode(PATH_SEPARATOR, $new_parts), Project::MSG_VERBOSE);
            set_include_path(implode(PATH_SEPARATOR, array_merge($new_parts, $curr_parts)));
        }
        
    }
}
