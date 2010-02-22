<?php
/*
 *  $Id: PhpEvalTask.php 144 2007-02-05 15:19:00Z hans $
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

/**
 * Executes PHP function or evaluates expression and sets return value to a property.
 *
 *    WARNING:
 *        This task can, of course, be abused with devastating effects.  E.g. do not
 *        modify internal Phing classes unless you know what you are doing.
 *
 * @author   Hans Lellelid <hans@xmpl.org>
 * @version  $Revision: 1.7 $
 * @package  phing.tasks.system
 *
 * @todo Add support for evaluating expressions
 */
class PhpEvalTask extends Task {
        
    protected $expression; // Expression to evaluate
    protected $function; // Function to execute
    protected $class; // Class containing function to execute
    protected $returnProperty; // name of property to set to return value 
    protected $params = array(); // parameters for function calls
    
    /** Main entry point. */
    function main() {
        
        if ($this->function === null && $this->expression === null) {
            throw new BuildException("You must specify a function to execute or PHP expression to evalute.", $this->location);
        }
        
        if ($this->function !== null && $this->expression !== null) {
            throw new BuildException("You can specify function or expression, but not both.", $this->location);
        }
        
        if ($this->expression !== null && !empty($this->params)) {
            throw new BuildException("You cannot use nested <param> tags when evaluationg a PHP expression.", $this->location);
        }
        
        $retval = null;
        if ($this->function !== null) {
            $retval = $this->callFunction();                                    
        } elseif ($this->expression !== null) {
            $retval = $this->evalExpression();
        }
        
        if ($this->returnProperty !== null) {
            $this->project->setProperty($this->returnProperty, $retval);
        }
    }
    
    /**
     * Calls function and returns results.
     * @return mixed
     */
    protected function callFunction() {
                        
        if ($this->class !== null) {
            // import the classname & unqualify it, if necessary
            $this->class = Phing::import($this->class);
                        
            $user_func = array($this->class, $this->function);
            $h_func = $this->class . '::' . $this->function; // human-readable (for log)
        } else {
            $user_func = $this->function;
            $h_func = $user_func; // human-readable (for log)
        }
        
        // put parameters into simple array
        $params = array();
        foreach($this->params as $p) {
            $params[] = $p->getValue();
        }
        
        $this->log("Calling PHP function: " . $h_func . "()");
        foreach($params as $p) {
            $this->log("  param: " . $p, Project::MSG_VERBOSE);
        } 
        
        $return = call_user_func_array($user_func, $params);
        return $return;
    }
    
    /**
     * Evaluates expression and returns resulting value.
     * @return mixed
     */
    protected function evalExpression() {
        $this->log("Evaluating PHP expression: " . $this->expression);
        if (!StringHelper::endsWith(';', trim($this->expression))) {
            $this->expression .= ';';
        }
        $retval = null;
        eval('$retval = ' . $this->expression);
        return $retval;
    }
    
    /** Set function to execute */
    public function setFunction($f) {
       $this->function = $f;
    }

    /** Set [static] class which contains function to execute */
    public function setClass($c) {
       $this->class = $c;
    }
    
    /** Sets property name to set with return value of function or expression.*/
    public function setReturnProperty($r) {
       $this->returnProperty = $r;
    }
    
    /** Set PHP expression to evaluate. */
    public function addText($expression) {
        $this->expression = $expression;
    }

    /** Set PHP expression to evaluate. */
    public function setExpression($expression) {
        $this->expression = $expression;
    }
    
    /** Add a nested <param> tag. */
    public function createParam() {
        $p = new FunctionParam();
        $this->params[] = $p;
        return $p;
    }        
}

/**
 * Supports the <param> nested tag for PhpTask.
 */
class FunctionParam {

    private $val;
    
    public function setValue($v) {
        $this->val = $v;
    }
    
    public function addText($v) {
        $this->val = $v;
    }
    
    public function getValue() {
        return $this->val;
    }
}