<?php
/*
 *  $Id: TaskHandler.php 147 2007-02-06 20:32:22Z hans $
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

include_once 'phing/UnknownElement.php';

/**
 * The task handler class.
 *
 * This class handles the occurance of a <task> tag and it's possible
 * nested tags (datatypes and tasks) that may be unknown off bat and are
 * initialized on the fly.
 *
 * @author      Andreas Aderhold <andi@binarycloud.com>
 * @copyright � 2001,2002 THYRELL. All rights reserved
 * @version   $Revision: 1.10 $
 * @package   phing.parser
 */
class TaskHandler extends AbstractHandler {

    /**
     * Reference to the target object that contains the currently parsed
     * task
     * @var object the target instance
     */
    private $target;

    /**
     * Reference to the target object that represents the currently parsed
     * target. This must not necessarily be a target, hence extra variable.
     * @var object the target instance
     */
    private $container;

    /**
     * Reference to the task object that represents the currently parsed
     * target.
     * @var Task
     */
    private $task;
	
	/**
 	 * Wrapper for the parent element, if any. The wrapper for this
	 * element will be added to this wrapper as a child.
	 * @var RuntimeConfigurable
	 */
	private $parentWrapper;
	
	/**
	 * Wrapper for this element which takes care of actually configuring
	 * the element, if this element is contained within a target.
	 * Otherwise the configuration is performed with the configure method.
	 * @see ProjectHelper::configure(Object,AttributeList,Project)
	 */
    private $wrapper;

    /**
     * The phing project configurator object
     * @var ProjectConfigurator
     */
    private $configurator;

    /**
     * Constructs a new TaskHandler and sets up everything.
     *
     * @param AbstractSAXParser The ExpatParser object
     * @param object $parentHandler The parent handler that invoked this handler
     * @param ProjectConfigurator $configurator
     * @param TaskContainer $container The container object this task is contained in (null for top-level tasks).
	 * @param RuntimeConfigurable $parentWrapper  Wrapper for the parent element, if any.
     * @param Target $target The target object this task is contained in (null for top-level tasks).
     */
    function __construct(AbstractSAXParser $parser, $parentHandler, ProjectConfigurator $configurator, $container = null, $parentWrapper = null, $target = null) {
        
        parent::__construct($parser, $parentHandler);
    
        if (($container !== null) && !($container instanceof TaskContainer)) {
            throw new Exception("Argument expected to be a TaskContainer, got something else");
        }
		if (($parentWrapper !== null) && !($parentWrapper instanceof RuntimeConfigurable)) {
            throw new Exception("Argument expected to be a RuntimeConfigurable, got something else.");
        }
        if (($target !== null) && !($target instanceof Target)) {
            throw new Exception("Argument expected to be a Target, got something else");
        }

		$this->configurator = $configurator;
        $this->container = $container;
		$this->parentWrapper = $parentWrapper;
        $this->target = $target;
    }

    /**
     * Executes initialization actions required to setup the data structures
     * related to the tag.
     * <p>
     * This includes:
     * <ul>
     * <li>creation of the task object</li>
     * <li>calling the setters for attributes</li>
     * <li>adding the task to the container object</li>
     * <li>adding a reference to the task (if id attribute is given)</li>
     * <li>executing the task if the container is the &lt;project&gt;
     * element</li>
     * </ul>
     *
     * @param string $tag The tag that comes in
     * @param array $attrs Attributes the tag carries
     * @throws ExpatParseException if attributes are incomplete or invalid
     */
    function init($tag, $attrs) {
        // shorthands
        try {
            $configurator = $this->configurator;
            $project = $this->configurator->project;
            
            $this->task = $project->createTask($tag);
        } catch (BuildException $be) {
            // swallow here, will be thrown again in
            // UnknownElement->maybeConfigure if the problem persists.
            print("Swallowing exception: ".$be->getMessage() . "\n");
        }

        // the task is not known of bat, try to load it on thy fly
        if ($this->task === null) {
            $this->task = new UnknownElement($tag);
            $this->task->setProject($project);
            $this->task->setTaskType($tag);
            $this->task->setTaskName($tag);
        }

        // add file position information to the task (from parser)
        // should be used in task exceptions to provide details
        $this->task->setLocation($this->parser->getLocation());
        $configurator->configureId($this->task, $attrs);
		
		if ($this->container) {
			$this->container->addTask($this->task);
		}
		
        // Top level tasks don't have associated targets
		// FIXME: if we do like Ant 1.6 and create an implicitTarget in the projectconfigurator object
		// then we don't need to check for null here ... but there's a lot of stuff that will break if we
		// do that at this point.
        if ($this->target !== null) {
            $this->task->setOwningTarget($this->target);
            $this->task->init();
            $this->wrapper = $this->task->getRuntimeConfigurableWrapper();
            $this->wrapper->setAttributes($attrs);
            /*
			Commenting this out as per thread on Premature configurate of ReuntimeConfigurables 
            with Matthias Pigulla: http://phing.tigris.org/servlets/ReadMsg?list=dev&msgNo=251
            
			if ($this->parentWrapper !== null) { // this may not make sense only within this if-block, but it
												// seems to address current use cases adequately
		    	$this->parentWrapper->addChild($this->wrapper);
			}
			*/
        } else {
            $this->task->init();
            $configurator->configure($this->task, $attrs, $project);
        }
    }

    /**
     * Executes the task at once if it's directly beneath the <project> tag.
     */
    protected function finished() {
        if ($this->task !== null && $this->target === null && $this->container === null) {
            try {
                $this->task->perform();
            } catch (Exception $e) {
                $this->task->log($e->getMessage(), Project::MSG_ERR);
                throw $e;
            }
        }
    }

    /**
     * Handles character data.
     *
     * @param string $data The CDATA that comes in
     */
    function characters($data) {
        if ($this->wrapper === null) {
            $configurator = $this->configurator;
            $project = $this->configurator->project;
            try { // try
                $configurator->addText($project, $this->task, $data);
            } catch (BuildException $exc) {
                throw new ExpatParseException($exc->getMessage(), $this->parser->getLocation());
            }
        } else {
            $this->wrapper->addText($data);
        }
    }

    /**
     * Checks for nested tags within the current one. Creates and calls
     * handlers respectively.
     *
     * @param string $name The tag that comes in
     * @param array $attrs Attributes the tag carries
     */
    function startElement($name, $attrs) {
        $project = $this->configurator->project;
        if ($this->task instanceof TaskContainer) {
            //print("TaskHandler::startElement() (TaskContainer) name = $name, attrs = " . implode(",",$attrs) . "\n");
            $th = new TaskHandler($this->parser, $this, $this->configurator, $this->task, $this->wrapper, $this->target);
            $th->init($name, $attrs);
        } else {
            //print("TaskHandler::startElement() name = $name, attrs = " . implode(",",$attrs) . "\n");
            $tmp = new NestedElementHandler($this->parser, $this, $this->configurator, $this->task, $this->wrapper, $this->target);
            $tmp->init($name, $attrs);
        }
    }
}
