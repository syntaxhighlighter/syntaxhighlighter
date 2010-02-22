<?php
/*
 *  $Id: Mapper.php 325 2007-12-20 15:44:58Z hans $
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

include_once 'phing/types/DataType.php';
include_once 'phing/types/Path.php';

/**
 * Filename Mapper maps source file name(s) to target file name(s).
 * 
 * Built-in mappers can be accessed by specifying they "type" attribute:
 * <code>
 * <mapper type="glob" from="*.php" to="*.php.bak"/>
 * </code>
 * Custom mappers can be specified by providing a dot-path to a include_path-relative
 * class:
 * <code>
 * <mapper classname="myapp.mappers.DevToProdMapper" from="*.php" to="*.php"/>
 * <!-- maps all PHP files from development server to production server, for example -->
 * </code>
 *
 * @author Hans Lellelid <hans@xmpl.org>
 * @package phing.types
 */
class Mapper extends DataType {

    protected $type;    
    protected $classname;
    protected $from;
    protected $to;
    protected $classpath;
    protected $classpathId;

    
    function __construct(Project $project) {
        $this->project = $project;
    }
    
    /**
     * Set the classpath to be used when searching for component being defined
     * 
     * @param Path $classpath An Path object containing the classpath.
     */
    public function setClasspath(Path $classpath) {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
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
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        if ($this->classpath === null) {
            $this->classpath = new Path($this->project);
        }
        return $this->classpath->createPath();
    }

    /**
     * Reference to a classpath to use when loading the files.
     */
    public function setClasspathRef(Reference $r) {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->classpathId = $r->getRefId();
        $this->createClasspath()->setRefid($r);
    }

    /** Set the type of FileNameMapper to use. */
    function setType($type) {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->type = $type;
    }

    /** Set the class name of the FileNameMapper to use. */
    function setClassname($classname) {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->classname = $classname;
    }

    /**
     * Set the argument to FileNameMapper.setFrom
     */
    function setFrom($from) {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->from = $from;
    }

    /**
     * Set the argument to FileNameMapper.setTo
     */
    function setTo($to) {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->to = $to;
    }

    /**
     * Make this Mapper instance a reference to another Mapper.
     *
     * You must not set any other attribute if you make it a reference.
     */
    function setRefid($r) {
        if ($this->type !== null || $this->from !== null || $this->to !== null) {
            throw DataType::tooManyAttributes();
        }
        parent::setRefid($r);
    }

    /** Factory, returns inmplementation of file name mapper as new instance */
    function getImplementation() {
        if ($this->isReference()) {
            $tmp = $this->getRef();
            return $tmp->getImplementation();
        }

        if ($this->type === null && $this->classname === null) {
            throw new BuildException("either type or classname attribute must be set for <mapper>");
        }
        
        if ($this->type !== null) {
            switch($this->type) {
            case 'identity':
                $this->classname = 'phing.mappers.IdentityMapper';
                break;
            case 'flatten':
                $this->classname = 'phing.mappers.FlattenMapper';
                break;
            case 'glob':
                $this->classname = 'phing.mappers.GlobMapper';
                break;
            case 'regexp':
            case 'regex':
                $this->classname = 'phing.mappers.RegexpMapper';            
                break;
            case 'merge':
                $this->classname = 'phing.mappers.MergeMapper';                
                break;
            default:
                throw new BuildException("Mapper type {$this->type} not known");
                break;
            }
        }

        // get the implementing class
        $cls = Phing::import($this->classname, $this->classpath);
        
        $m = new $cls;
        $m->setFrom($this->from);
        $m->setTo($this->to);
        
        return $m;
    }

    /** Performs the check for circular references and returns the referenced Mapper. */
    private function getRef() {
        if (!$this->checked) {
            $stk = array();
            $stk[] = $this;
            $this->dieOnCircularReference($stk, $this->project);            
        }

        $o = $this->ref->getReferencedObject($this->project);
        if (!($o instanceof Mapper)) {
            $msg = $this->ref->getRefId()." doesn't denote a mapper";
            throw new BuildException($msg);
        } else {
            return $o;
        }
    }
}


