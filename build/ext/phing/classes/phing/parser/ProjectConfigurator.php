<?php
/*
 * $Id: ProjectConfigurator.php 147 2007-02-06 20:32:22Z hans $
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

include_once 'phing/system/io/BufferedReader.php';
include_once 'phing/system/io/FileReader.php';
include_once 'phing/BuildException.php';
include_once 'phing/system/lang/FileNotFoundException.php';
include_once 'phing/system/io/PhingFile.php';

/**
 * The datatype handler class.
 *
 * This class handles the occurance of registered datatype tags like
 * FileSet
 *
 * @author      Andreas Aderhold <andi@binarycloud.com>
 * @copyright � 2001,2002 THYRELL. All rights reserved
 * @version   $Revision: 1.17 $ $Date: 2007-02-06 15:32:22 -0500 (Tue, 06 Feb 2007) $
 * @access    public
 * @package   phing.parser
 */
class ProjectConfigurator {

    public $project;
    public $locator;
    
    public $buildFile;
    public $buildFileParent;
        
    /**
     * Static call to ProjectConfigurator. Use this to configure a
     * project. Do not use the new operator.
     *
     * @param  object  the Project instance this configurator should use
     * @param  object  the buildfile object the parser should use
     * @access public
     */
    public static function configureProject(Project $project, PhingFile $buildFile) {
        $pc = new ProjectConfigurator($project, $buildFile);
        $pc->parse();
    }

    /**
     * Constructs a new ProjectConfigurator object
     * This constructor is private. Use a static call to
     * <code>configureProject</code> to configure a project.
     *
     * @param  object  the Project instance this configurator should use
     * @param  object  the buildfile object the parser should use
     * @access private
     */
    function __construct(Project $project, PhingFile $buildFile) {
        $this->project = $project;
        $this->buildFile = new PhingFile($buildFile->getAbsolutePath());
        $this->buildFileParent = new PhingFile($this->buildFile->getParent());
    }

    /**
     * Creates the ExpatParser, sets root handler and kick off parsing
     * process.
     *
     * @throws BuildException if there is any kind of execption during
     *         the parsing process
     * @access private
     */
    protected function parse() {
        try {
            $reader = new BufferedReader(new FileReader($this->buildFile));
            $parser = new ExpatParser($reader);
            $parser->parserSetOption(XML_OPTION_CASE_FOLDING,0);
            $parser->setHandler(new RootHandler($parser, $this));
            $this->project->log("parsing buildfile ".$this->buildFile->getName(), Project::MSG_VERBOSE);
            $parser->parse();
            $reader->close();
        } catch (Exception $exc) {
            throw new BuildException("Error reading project file", $exc);
        }
    }

    /**
     * Configures an element and resolves eventually given properties.
     *
     * @param  object  the element to configure
     * @param  array   the element's attributes
     * @param  object  the project this element belongs to
     * @throws Exception if arguments are not valid
     * @throws BuildException if attributes can not be configured
     * @access public
     */
    public static function configure($target, $attrs, Project $project) {               

        if ($target instanceof TaskAdapter) {
            $target = $target->getProxy();
        }
        
		// if the target is an UnknownElement, this means that the tag had not been registered
		// when the enclosing element (task, target, etc.) was configured.  It is possible, however, 
		// that the tag was registered (e.g. using <taskdef>) after the original configuration.
		// ... so, try to load it again:
		if ($target instanceof UnknownElement) {
			$tryTarget = $project->createTask($target->getTaskType());
			if ($tryTarget) {
				$target = $tryTarget;
			}
		}

        $bean = get_class($target);
        $ih = IntrospectionHelper::getHelper($bean);

        foreach ($attrs as $key => $value) {
            if ($key == 'id') {
                continue;
                // throw new BuildException("Id must be set Extermnally");
            }            
            $value = self::replaceProperties($project, $value, $project->getProperties());
            try { // try to set the attribute
                $ih->setAttribute($project, $target, strtolower($key), $value);
            } catch (BuildException $be) {
                // id attribute must be set externally
                if ($key !== "id") {
                    throw $be;
                }
            }
        }
    }

    /**
     * Configures the #CDATA of an element.
     *
     * @param  object  the project this element belongs to
     * @param  object  the element to configure
     * @param  string  the element's #CDATA
     * @access public
     */
    public static function addText($project, $target, $text = null) {
        if ($text === null || strlen(trim($text)) === 0) {
            return;
        }    
        $ih = IntrospectionHelper::getHelper(get_class($target));
        $text = self::replaceProperties($project, $text, $project->getProperties());
        $ih->addText($project, $target, $text);
    }

    /**
     * Stores a configured child element into its parent object
     *
     * @param  object  the project this element belongs to
     * @param  object  the parent element
     * @param  object  the child element
     * @param  string  the XML tagname
     * @access public
     */
    public static function storeChild($project, $parent, $child, $tag) {
        $ih = IntrospectionHelper::getHelper(get_class($parent));
        $ih->storeElement($project, $parent, $child, $tag);
    }

    // The following two properties are a sort of hack
    // to enable a static function to serve as the callback
    // for preg_replace_callback().  Clearly we cannot use object
    // variables, since the replaceProperties() is called statically.
    // This is IMO better than using global variables in the callback.
    
    private static $propReplaceProject;
    private static $propReplaceProperties;
         
    /**
     * Replace ${} style constructions in the given value with the
     * string value of the corresponding data types. This method is
     * static.
     *
     * @param  object  the project that should be used for property look-ups
     * @param  string  the string to be scanned for property references
     * @param  array   proeprty keys
     * @return string  the replaced string or <code>null</code> if the string
     *                 itself was null
     */
    public static function replaceProperties(Project $project, $value, $keys) {
        
        if ($value === null) {
            return null;
        }
        
        // These are a "hack" to support static callback for preg_replace_callback()
        
        // make sure these get initialized every time        
        self::$propReplaceProperties = $keys;
        self::$propReplaceProject = $project;
        
        // Because we're not doing anything special (like multiple passes),
        // regex is the simplest / fastest.  PropertyTask, though, uses
        // the old parsePropertyString() method, since it has more stringent
        // requirements.
        
        $sb = preg_replace_callback('/\$\{([^}]+)\}/', array('ProjectConfigurator', 'replacePropertyCallback'), $value);
        return $sb;        
    }
    
    /**
     * Private [static] function for use by preg_replace_callback to replace a single param.
     * This method makes use of a static variable to hold the 
     */
    private static function replacePropertyCallback($matches)
    {
        $propertyName = $matches[1];
        if (!isset(self::$propReplaceProperties[$propertyName])) {
                    self::$propReplaceProject->log('Property ${'.$propertyName.'} has not been set.', Project::MSG_VERBOSE);
                    return $matches[0];
        } else {
			self::$propReplaceProject->log('Property ${'.$propertyName.'} => ' . self::$propReplaceProperties[$propertyName], Project::MSG_DEBUG);
		}
        return self::$propReplaceProperties[$propertyName];
    }           

    /**
     * Scan Attributes for the id attribute and maybe add a reference to
     * project.
     *
     * @param object the element's object
     * @param array  the element's attributes
     */
    public function configureId($target, $attr) {
        if (isset($attr['id']) && $attr['id'] !== null) {
            $this->project->addReference($attr['id'], $target);
        }
    }
}
