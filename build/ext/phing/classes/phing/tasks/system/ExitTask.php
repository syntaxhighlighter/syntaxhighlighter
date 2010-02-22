<?php
/*
 *  $Id: ExitTask.php 43 2006-03-10 14:31:51Z mrook $
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
 * Exits the active build, giving an additional message
 * if available.
 *
 * @author    Hans Lellelid <hans@xmpl.org> (Phing)
 * @author    Nico Seessle <nico@seessle.de> (Ant)
 * @version   $Revision: 1.7 $
 * @package   phing.tasks.system
 */
class ExitTask extends Task { 

    private $message;
    private $ifCondition;
    private $unlessCondition;

    /**
     * A message giving further information on why the build exited.
     *
     * @param string $value message to output
     */
    public function setMsg($value) {
        $this->setMessage($value);
    }

    /**
     * A message giving further information on why the build exited.
     *
     * @param value message to output
     */
    public function setMessage($value) {
        $this->message = $value;
    }

    /**
     * Only fail if a property of the given name exists in the current project.
     * @param c property name
     */
    public function setIf($c) {
        $this->ifCondition = $c;
    }

    /**
     * Only fail if a property of the given name does not
     * exist in the current project.
     * @param c property name
     */
    public function setUnless($c) {
        $this->unlessCondition = $c;
    }

    /**
     * @throws BuildException
     */
    public function main()  {
        if ($this->testIfCondition() && $this->testUnlessCondition()) {
            if ($this->message !== null) { 
                throw new BuildException($this->message);
            } else {
                throw new BuildException("No message");
            }
        }
    }

    /**
     * Set a multiline message.
     */
    public function addText($msg) {
        if ($this->message === null) {
            $this->message = "";
        }
        $this->message .= $this->project->replaceProperties($msg);
    }

    /**
     * @return boolean
     */
    private function testIfCondition() {
        if ($this->ifCondition === null || $this->ifCondition === "") {
            return true;
        }
        
        return $this->project->getProperty($this->ifCondition) !== null;
    }
    
    /**
     * @return boolean
     */
    private function testUnlessCondition() {
        if ($this->unlessCondition === null || $this->unlessCondition ===  "") {
            return true;
        }
        return $this->project->getProperty($this->unlessCondition) === null;
    }

}
