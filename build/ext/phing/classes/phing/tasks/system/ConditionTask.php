<?php
/*
 *  $Id: ConditionTask.php 43 2006-03-10 14:31:51Z mrook $  
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

require_once 'phing/tasks/system/condition/ConditionBase.php';

/**
 *  <condition> task as a generalization of <available>
 *
 *  <p>This task supports boolean logic as well as pluggable conditions
 *  to decide, whether a property should be set.</p>
 *
 *  <p>This task does not extend Task to take advantage of
 *  ConditionBase.</p>
 *
 *  @author    Andreas Aderhold <andi@binarycloud.com>
 *  @copyright © 2001,2002 THYRELL. All rights reserved
 *  @version   $Revision: 1.7 $ $Date: 2006-03-10 09:31:51 -0500 (Fri, 10 Mar 2006) $
 *  @access    public
 *  @package   phing.tasks.system
 */
class ConditionTask extends ConditionBase {

    private $property;
    private $value = "true";

    /**
     * The name of the property to set. Required.
     */
    function setProperty($p) {
        $this->property = $p;
    }

    /**
     * The value for the property to set. Defaults to "true".
     */
    function setValue($v) {
        $this->value = $v;
    }

    /**
     * See whether our nested condition holds and set the property.
     */
    function main() {

        if ($this->countConditions() > 1) {
            throw new BuildException("You must not nest more than one condition into <condition>");
        }
        if ($this->countConditions() < 1) {
            throw new BuildException("You must nest a condition into <condition>");
        }
        $cs = $this->getIterator();        
        if ($cs->current()->evaluate()) {
            $this->project->setProperty($this->property, $this->value);
        }
    }
}
