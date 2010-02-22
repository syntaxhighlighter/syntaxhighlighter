<?php
/* 
 *  $Id: Regexp.php 325 2007-12-20 15:44:58Z hans $
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

/**
 * A factory class for regex functions.
 * @author Hans Lellelid <hans@xmpl.org>
 * @package  phing.util.regexp
 * @version $Revision: 1.5 $
 */
class Regexp {

    /**
     * Matching groups found. 
     * @var array
     */
    private $groups = array();
     
    /**
     * Pattern to match.
     * @var string
     */
    private $pattern;
    
    /**
     * Replacement pattern.
     * @var string
     */
    private $replace;
    
    /**
     * The regex engine -- e.g. 'preg' or 'ereg';
     * @var RegexpEngine
     */
    private $engine;
    
    /**
     * Constructor sets the regex engine to use (preg by default).
     * @param string $_engineType The regex engine to use.
     */
    function __construct($engineType='preg') {        
        if ($engineType == 'preg') {
            include_once 'phing/util/regexp/PregEngine.php';
            $this->engine = new PregEngine();
        } elseif ($engineType == 'ereg') {
            include_once 'phing/util/regexp/EregEngine.php';
            $this->engine = new EregEngine();
        } else {
            throw new BuildException("Invalid engine type for Regexp: " . $engineType);
        }                
    }

    /**
     * Sets pattern to use for matching.
     * @param string $pat The pattern to match on.
     * @return void
     */
    public function setPattern($pat) {
        $this->pattern = (string) $pat;        
    }
    
    
    /**
     * Gets pattern to use for matching.
     * @return string The pattern to match on.
     */
    public function getPattern() {
        return $this->pattern;
    }
    
    /**
     * Sets replacement string.
     * @param string $rep The pattern to replace matches with.
     * @return void
     */
    public function setReplace($rep) {
        $this->replace = (string) $rep;
    }
    
    /**
     * Gets replacement string.
     * @return string The pattern to replace matches with.
     * @return void
     */
    public function getReplace() {
        return $this->replace;
    }
    
    /**
     * Performs match of specified pattern against $subject.
     * @param string $subject The subject, on which to perform matches.
     * @return boolean Whether or not pattern matches subject string passed.
     */
    public function matches($subject) {
        if($this->pattern === null) {            
            throw new Exception("No pattern specified for regexp match().");
        }
        return $this->engine->match($this->pattern, $subject, $this->groups);
    }
    
    /**
     * Performs replacement of specified pattern and replacement strings.
     * @param string $subject Text on which to perform replacement.
     * @return string subject after replacement has been performed.
     */
    public function replace($subject) {
        if ($this->pattern === null || $this->replace === null) {
            throw new Exception("Missing pattern or replacement string regexp replace().");
        }        
        return $this->engine->replace($this->pattern, $this->replace, $subject);
    }
    
    /**
     * Get array of matched groups.
     * @return array Matched groups
     */ 
    function getGroups() {
        return $this->groups;
    }

    /**
     * Get specific matched group. 
     * @param integer $idx
     * @return string specified group or NULL if group is not set.
     */ 
    function getGroup($idx) { 
        if (!isset($this->groups[$idx])) {
            return null;
        }
        return $this->groups[$idx];
    }
    
    /**
     * Sets whether the regexp matching is case insensitive.
     * (default is false -- i.e. case sensisitive)
     * @param boolean $bit
     */ 
    function setIgnoreCase($bit) {
        $this->engine->setIgnoreCase($bit);
    }
    
    /**
     * Gets whether the regexp matching is case insensitive.
     * @return boolean
     */
    function getIgnoreCase() {
        return $this->engine->getIgnoreCase();
    }
} 

