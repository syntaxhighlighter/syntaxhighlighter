<?php
/*
 *  $Id: NestedElementHandler.php 123 2006-09-14 20:19:08Z mrook $
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

include_once 'phing/IntrospectionHelper.php';
include_once 'phing/TaskContainer.php';

/**
 * The nested element handler class.
 *
 * This class handles the occurance of runtime registered tags like
 * datatypes (fileset, patternset, etc) and it's possible nested tags. It
 * introspects the implementation of the class and sets up the data structures.
 *
 * @author      Andreas Aderhold <andi@binarycloud.com>
 * @copyright © 2001,2002 THYRELL. All rights reserved
 * @version   $Revision: 1.10 $ $Date: 2006-09-14 16:19:08 -0400 (Thu, 14 Sep 2006) $
 * @access    public
 * @package   phing.parser
 */

class NestedElementHandler extends AbstractHandler {

    /**
     * Reference to the parent object that represents the parent tag
     * of this nested element
     * @var object
     */
    private $parent;

    /**
     * Reference to the child object that represents the child tag
     * of this nested element
     * @var object
     */
    private $child;

    /**
     *  Reference to the parent wrapper object
     *  @var object
     */
    private $parentWrapper;

    /**
     *  Reference to the child wrapper object
     *  @var object
     */
    private $childWrapper;

    /**
     *  Reference to the related target object
     *  @var object the target instance
     */
    private $target;

    /**
     *  Constructs a new NestedElement handler and sets up everything.
     *
     *  @param  object  the ExpatParser object
     *  @param  object  the parent handler that invoked this handler
     *  @param  object  the ProjectConfigurator object
     *  @param  object  the parent object this element is contained in
     *  @param  object  the parent wrapper object
     *  @param  object  the target object this task is contained in
     *  @access public
     */
    function __construct($parser, $parentHandler, $configurator, $parent, $parentWrapper, $target) {
        parent::__construct($parser, $parentHandler);
        $this->configurator = $configurator;
        if ($parent instanceof TaskAdapter) {
            $this->parent = $parent->getProxy();
        } else {
            $this->parent = $parent;
        }
        $this->parentWrapper = $parentWrapper;
        $this->target = $target;        
    }

    /**
     * Executes initialization actions required to setup the data structures
     * related to the tag.
     * <p>
     * This includes:
     * <ul>
     * <li>creation of the nested element</li>
     * <li>calling the setters for attributes</li>
     * <li>adding the element to the container object</li>
     * <li>adding a reference to the element (if id attribute is given)</li>
         * </ul>
     *
     * @param  string  the tag that comes in
     * @param  array   attributes the tag carries
     * @throws ExpatParseException if the setup process fails
     * @access public
     */
    function init($propType, $attrs) {
        $configurator = $this->configurator;
        $project = $this->configurator->project;

        // introspect the parent class that is custom
        $parentClass = get_class($this->parent);
        $ih = IntrospectionHelper::getHelper($parentClass);
        try {
            if ($this->parent instanceof UnknownElement) {
                $this->child = new UnknownElement(strtolower($propType));
                $this->parent->addChild($this->child);
            } else {                
                $this->child = $ih->createElement($project, $this->parent, strtolower($propType));
            }
            
            $configurator->configureId($this->child, $attrs);
            
            if ($this->parentWrapper !== null) {
                $this->childWrapper = new RuntimeConfigurable($this->child, $propType);
                $this->childWrapper->setAttributes($attrs);
                $this->parentWrapper->addChild($this->childWrapper);
            } else {
                $configurator->configure($this->child, $attrs, $project);
                $ih->storeElement($project, $this->parent, $this->child, strtolower($propType));
            }
        } catch (BuildException $exc) {
            throw new ExpatParseException("Error initializing nested element <$propType>", $exc, $this->parser->getLocation());
        }
    }

    /**
     * Handles character data.
     *
     * @param  string  the CDATA that comes in
     * @throws ExpatParseException if the CDATA could not be set-up properly
     * @access public
     */
    function characters($data) {

        $configurator = $this->configurator;        
        $project = $this->configurator->project;

        if ($this->parentWrapper === null) {
            try {                
                $configurator->addText($project, $this->child, $data);
            } catch (BuildException $exc) {
                throw new ExpatParseException($exc->getMessage(), $this->parser->getLocation());
            }
        } else {                    
            $this->childWrapper->addText($data);
        }
    }

    /**
     * Checks for nested tags within the current one. Creates and calls
     * handlers respectively.
     *
     * @param  string  the tag that comes in
     * @param  array   attributes the tag carries
     * @access public
     */
    function startElement($name, $attrs) {
        //print(get_class($this) . " name = $name, attrs = " . implode(",",$attrs) . "\n");
		if ($this->child instanceof TaskContainer) {
                // taskcontainer nested element can contain other tasks - no other
                // nested elements possible
			$tc = new TaskHandler($this->parser, $this, $this->configurator, $this->child, $this->childWrapper, $this->target);
			$tc->init($name, $attrs);
		} else {
			$neh = new NestedElementHandler($this->parser, $this, $this->configurator, $this->child, $this->childWrapper, $this->target);
        	$neh->init($name, $attrs);
		}
    }
}
