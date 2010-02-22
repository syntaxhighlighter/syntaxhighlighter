<?php
/* 
 *  $Id: GlobMapper.php 123 2006-09-14 20:19:08Z mrook $
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

include_once 'phing/mappers/FileNameMapper.php';

/**
 * description here
 *
 * @author   Andreas Aderhold, andi@binarycloud.com
 * @version  $Revision: 1.10 $
 * @package   phing.mappers
 */
class GlobMapper implements FileNameMapper {

    /**
     * Part of &quot;from&quot; pattern before the *.
     */
    private $fromPrefix = null;

    /**
     * Part of &quot;from&quot; pattern after the *.
     */
    private $fromPostfix = null;

    /**
     * Length of the prefix (&quot;from&quot; pattern).
     */
    private $prefixLength;

    /**
     * Length of the postfix (&quot;from&quot; pattern).
     */
    private $postfixLength;

    /**
     * Part of &quot;to&quot; pattern before the *.
     */
    private $toPrefix = null;

    /**
     * Part of &quot;to&quot; pattern after the *.
     */
    private $toPostfix = null;


    function main($_sourceFileName) {
        if (($this->fromPrefix === null)
            || !StringHelper::startsWith($this->fromPrefix, $_sourceFileName)
            || !StringHelper::endsWith($this->fromPostfix, $_sourceFileName)) {
            return null;
        }
        $varpart = $this->_extractVariablePart($_sourceFileName);
        $substitution = $this->toPrefix.$varpart.$this->toPostfix;
        return array($substitution);
    }



   function setFrom($from) {
        $index = strrpos($from, '*');

        if ($index === false) {
            $this->fromPrefix = $from;
            $this->fromPostfix = "";
        } else {
            $this->fromPrefix  = substr($from, 0, $index);
            $this->fromPostfix = substr($from, $index+1);
        }
        $this->prefixLength  = strlen($this->fromPrefix);
        $this->postfixLength = strlen($this->fromPostfix);
    }

    /**
     * Sets the &quot;to&quot; pattern. Required.
     */
    function setTo($to) {
        $index = strrpos($to, '*');
        if ($index === false) {
            $this->toPrefix = $to;
            $this->toPostfix = "";
        } else {
            $this->toPrefix  = substr($to, 0, $index);
            $this->toPostfix = substr($to, $index+1);
        }
    }

    private function _extractVariablePart($_name) {
        // ergh, i really hate php's string functions .... all but natural
        $start = ($this->prefixLength === 0) ? 0 : $this->prefixLength;
        $end   = ($this->postfixLength === 0) ? strlen($_name) : strlen($_name) - $this->postfixLength;
        $len   = $end-$start;
        return substr($_name, $start, $len);
    }

}
