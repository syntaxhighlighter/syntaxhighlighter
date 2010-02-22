<?php

/*
 *  $Id: PropertyTask.php 144 2007-02-05 15:19:00Z hans $
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

include_once 'phing/Task.php';
include_once 'phing/system/util/Properties.php';

/**
 * Task for setting properties in buildfiles.
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Revision$
 * @package   phing.tasks.system
 */
class PropertyTask extends Task {

    /** name of the property */
    protected $name; 
    
    /** value of the property */
    protected $value;
    
    protected $reference;
    protected $env;     // Environment
    protected $file;
    protected $ref;
    protected $prefix;
    protected $fallback;
    
    /** Whether to force overwrite of existing property. */
    protected $override = false;
    
    /** Whether property should be treated as "user" property. */
    protected $userProperty = false;

    /**
     * Sets a the name of current property component
     */
    function setName($name) {
        $this->name = (string) $name;
    }
    
    /** Get property component name. */
    function getName() {
        return $this->name;
    }

    /**
     * Sets a the value of current property component.
     * @param    mixed      Value of name, all scalars allowed
     */
    function setValue($value) {
        $this->value = (string) $value;
    }
	
	/**
	 * Sets value of property to CDATA tag contents.
	 * @param string $values
	 * @since 2.2.0
	 */
	public function addText($value) {
		$this->setValue($value);
	}
    
    /** Get the value of current property component. */
    function getValue() {
        return $this->value;
    }
    
    /** Set a file to use as the source for properties. */
    function setFile($file) {
        if (is_string($file)) {
            $file = new PhingFile($file);
        }
        $this->file = $file;
    }
    
    /** Get the PhingFile that is being used as property source. */
    function getFile() {
        return $this->file;
    }

    function setRefid(Reference $ref) {
        $this->reference = $ref;
    }
    
    function getRefid() {
        return $this->reference;
    }

    /**
     * Prefix to apply to properties loaded using <code>file</code>.
     * A "." is appended to the prefix if not specified.
     * @param string $prefix prefix string
     * @return void
     * @since 2.0
     */
    public function setPrefix($prefix) {
        $this->prefix = $prefix;
        if (!StringHelper::endsWith(".", $prefix)) {
            $this->prefix .= ".";
        }
    }

    /**
     * @return string
     * @since 2.0
     */
    public function getPrefix() {
        return $this->prefix;
    }

    /**
    * the prefix to use when retrieving environment variables.
    * Thus if you specify environment="myenv"
    * you will be able to access OS-specific
    * environment variables via property names "myenv.PATH" or
    * "myenv.TERM".
    * <p>
    * Note that if you supply a property name with a final
    * "." it will not be doubled. ie environment="myenv." will still
    * allow access of environment variables through "myenv.PATH" and
    * "myenv.TERM". This functionality is currently only implemented
    * on select platforms. Feel free to send patches to increase the number of platforms
    * this functionality is supported on ;).<br>
    * Note also that properties are case sensitive, even if the
    * environment variables on your operating system are not, e.g. it
    * will be ${env.Path} not ${env.PATH} on Windows 2000.
    * @param env prefix
    */
    function setEnvironment($env) {
        $this->env = (string) $env;
    }

    function getEnvironment() {
        return $this->env;
    }
    
    /**
     * Set whether this is a user property (ro).
     * This is deprecated in Ant 1.5, but the userProperty attribute
     * of the class is still being set via constructor, so Phing will
     * allow this method to function.
     * @param boolean $v
     */
    function setUserProperty($v) {
        $this->userProperty = (boolean) $v;
    }
    
    function getUserProperty() {
        return $this->userProperty;
    }
    
    function setOverride($v) {
        $this->override = (boolean) $v;
    }
    
    function getOverride() {
        return $this->override;
    }
    
    function toString() {
        return (string) $this->value;
    }

	/**
	 * @param Project $p
	 */
    function setFallback($p) {
        $this->fallback = $p;
    }
    
    function getFallback() {
        return $this->fallback;
    }
    /**
     * set the property in the project to the value.
     * if the task was give a file or env attribute
     * here is where it is loaded
     */
    function main() {
        if ($this->name !== null) {
            if ($this->value === null && $this->ref === null) {
                throw new BuildException("You must specify value or refid with the name attribute", $this->getLocation());
            }
        } else {
            if ($this->file === null && $this->env === null ) {
                throw new BuildException("You must specify file or environment when not using the name attribute", $this->getLocation());
            }
        }

        if ($this->file === null && $this->prefix !== null) {
            throw new BuildException("Prefix is only valid when loading from a file.", $this->getLocation());
        }
        
        if (($this->name !== null) && ($this->value !== null)) {
            $this->addProperty($this->name, $this->value);
        }

        if ($this->file !== null) {
            $this->loadFile($this->file);
        }

        if ( $this->env !== null ) {
            $this->loadEnvironment($this->env);
        }

        if (($this->name !== null) && ($this->ref !== null)) {
            // get the refereced property
            try {
            $this->addProperty($this->name, $this->reference->getReferencedObject($this->project)->toString());
            } catch (BuildException $be) {
                if ($this->fallback !== null) {
                     $this->addProperty($this->name, $this->reference->getReferencedObject($this->fallback)->toString());
                } else {
                    throw $be;
                }
            }
        }
    }
    
    /**
     * load the environment values
     * @param string $prefix prefix to place before them
     */
    protected function loadEnvironment($prefix) {

        $props = new Properties();
        if ( substr($prefix, strlen($prefix)-1) == '.' ) {
            $prefix .= ".";
        }
        $this->log("Loading Environment $prefix", Project::MSG_VERBOSE);
        foreach($_ENV as $key => $value) {
            $props->setProperty($prefix . '.' . $key, $value);
        }
        $this->addProperties($props);
    }

    /**
     * iterate through a set of properties,
     * resolve them then assign them
     */
    protected function addProperties($props) {
        $this->resolveAllProperties($props);
        foreach($props->keys() as $name) {        
            $value = $props->getProperty($name);
            $v = $this->project->replaceProperties($value);            
            if ($this->prefix !== null) {
                $name = $this->prefix . $name;
            }
            $this->addProperty($name, $v);
        }
    }

    /**
     * add a name value pair to the project property set
     * @param string $name name of property
     * @param string $value value to set
     */
    protected function addProperty($name, $value) {
        if ($this->userProperty) {
            if ($this->project->getUserProperty($name) === null || $this->override) {
                $this->project->setInheritedProperty($name, $value);
            } else {
                $this->log("Override ignored for " . $name, Project::MSG_VERBOSE);
            }
        } else {
            if ($this->override) {
                $this->project->setProperty($name, $value);
            } else {
                $this->project->setNewProperty($name, $value);
            }
        }
    }

    /**
     * load properties from a file.
     * @param PhingFile $file
     */
    protected function loadFile(PhingFile $file) {
        $props = new Properties();
        $this->log("Loading ". $file->getAbsolutePath(), Project::MSG_INFO);
        try { // try to load file
            if ($file->exists()) {
                $props->load($file);
                $this->addProperties($props);
            } else {
                $this->log("Unable to find property file: ". $file->getAbsolutePath() ."... skipped", Project::MSG_WARN);
            }
        } catch (IOException $ioe) {
            throw new BuildException("Could not load properties from file.", $ioe);
        }
    }
    
    /**
     * Given a Properties object, this method goes through and resolves
     * any references to properties within the object.
     * 
     * @param Properties $props The collection of Properties that need to be resolved.
     * @return void
     */
    protected function resolveAllProperties(Properties $props) {
        
        $keys = $props->keys();

        while(count($keys)) {

            // There may be a nice regex/callback way to handle this
            // replacement, but at the moment it is pretty complex, and
            // would probably be a lot uglier to work into a preg_replace_callback()
            // system.  The biggest problem is the fact that a resolution may require
            // multiple passes.
            
            $name     = array_shift($keys);
            $value    = $props->getProperty($name);
            $resolved = false;
            
            while(!$resolved) {
            
                $fragments = array();
                $propertyRefs = array();

                // [HL] this was ::parsePropertyString($this->value ...) ... this seems wrong
                self::parsePropertyString($value, $fragments, $propertyRefs);

                $resolved = true;
                if (count($propertyRefs) !== 0) {

                    $sb = "";

                    $i = $fragments;
                    $j = $propertyRefs;
                    while(count($i)) {
                        $fragment = array_shift($i);
                        if ($fragment === null) {
                            $propertyName = array_shift($j);

                            if ($propertyName === $name) {
                                // Should we maybe just log this as an error & move on?
                                // $this->log("Property ".$name." was circularly defined.", Project::MSG_ERR);
                                throw new BuildException("Property ".$name." was circularly defined.");
                            }

                            $fragment = $this->getProject()->getProperty($propertyName);
                            if ($fragment === null) {
                                if ($props->containsKey($propertyName)) {
                                    $fragment = $props->getProperty($propertyName);
                                    $resolved = false; // parse again (could have been replaced w/ another var)
                                } else {
                                    $fragment = "\${".$propertyName."}";
                                }
                            }
                        }
                        $sb .= $fragment;
                    }
                    
                    $this->log("Resolved Property \"$value\" to \"$sb\"", Project::MSG_DEBUG);
                    $value = $sb;                    
                    $props->setProperty($name, $value);
                                 
                } // if (count($propertyRefs))
                
            } // while (!$resolved)
            
        } // while (count($keys)
    }


     /**
     * This method will parse a string containing ${value} style
     * property values into two lists. The first list is a collection
     * of text fragments, while the other is a set of string property names
     * null entries in the first list indicate a property reference from the
     * second list.
     *
     * This is slower than regex, but useful for this class, which has to handle
     * multiple parsing passes for properties.
     *
     * @param string $value The string to be scanned for property references
     * @param array &$fragments The found fragments
     * @param  array &$propertyRefs The found refs
     */
    protected function parsePropertyString($value, &$fragments, &$propertyRefs) {
    
        $prev = 0;
        $pos  = 0;

        while (($pos = strpos($value, '$', $prev)) !== false) {
            
            if ($pos > $prev) {
                array_push($fragments, StringHelper::substring($value, $prev, $pos-1));
            }
            if ($pos === (strlen($value) - 1)) {
                array_push($fragments, '$');
                $prev = $pos + 1;
            } elseif ($value{$pos+1} !== '{' ) {

                // the string positions were changed to value-1 to correct
                // a fatal error coming from function substring()
                array_push($fragments, StringHelper::substring($value, $pos, $pos + 1));
                $prev = $pos + 2;
            } else {
                $endName = strpos($value, '}', $pos);
                if ($endName === false) {
                    throw new BuildException("Syntax error in property: $value");
                }
                $propertyName = StringHelper::substring($value, $pos + 2, $endName-1);
                array_push($fragments, null);
                array_push($propertyRefs, $propertyName);
                $prev = $endName + 1;
            }
        }

        if ($prev < strlen($value)) {
            array_push($fragments, StringHelper::substring($value, $prev));
        }
    }

}
