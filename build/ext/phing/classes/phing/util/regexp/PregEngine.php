<?php
/* 
 *  $Id: PregEngine.php 325 2007-12-20 15:44:58Z hans $
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

require_once 'phing/util/regexp/RegexpEngine.php';

/**
 * PREG Regexp Engine.
 * Implements a regexp engine using PHP's preg_match(), preg_match_all(), and preg_replace() functions.
 * 
 * @author hans lellelid, hans@velum.net
 * @package phing.util.regex
 */
class PregEngine implements RegexpEngine {

    /**
     * @var boolean
     */
    private $ignoreCase = false;
        
    /**
     * Sets whether or not regex operation is case sensitive.
     * @param boolean $bit
     * @return void
     */
    function setIgnoreCase($bit) {
        $this->ignoreCase = (boolean) $bit;
    }

    /**
     * Gets whether or not regex operation is case sensitive.
     * @return boolean
     */
    function getIgnoreCase() {
        return $this->ignoreCase;
    }
        
    /**
     * The pattern needs to be converted into PREG style -- which includes adding expression delims & any flags, etc.
     * @param string $pattern
     * @return string prepared pattern.
     */
    private function preparePattern($pattern)
    {
        return '/'.$pattern.'/'.($this->ignoreCase ? 'i' : '');
    }
    
    /**
     * Matches pattern against source string and sets the matches array.
     * @param string $pattern The regex pattern to match.
     * @param string $source The source string.
     * @param array $matches The array in which to store matches.
     * @return boolean Success of matching operation.
     */
    function match($pattern, $source, &$matches) { 
        return preg_match($this->preparePattern($pattern), $source, $matches);
    }

    /**
     * Matches all patterns in source string and sets the matches array.
     * @param string $pattern The regex pattern to match.
     * @param string $source The source string.
     * @param array $matches The array in which to store matches.
     * @return boolean Success of matching operation.
     */        
    function matchAll($pattern, $source, &$matches) {
        return preg_match_all($this->preparePattern($pattern), $source, $matches);
    }

    /**
     * Replaces $pattern with $replace in $source string.
     * References to \1 group matches will be replaced with more preg-friendly
     * $1.
     * @param string $pattern The regex pattern to match.
     * @param string $replace The string with which to replace matches.
     * @param string $source The source string.
     * @return string The replaced source string.
     */        
    function replace($pattern, $replace, $source) {
        // convert \1 -> $1, because we want to use the more generic \1 in the XML
        // but PREG prefers $1 syntax.
        $replace = preg_replace('/\\\(\d+)/', '\$$1', $replace);
        return preg_replace($this->preparePattern($pattern), $replace, $source);
    }

}

