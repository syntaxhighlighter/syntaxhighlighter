<?php
/*
 *  $Id: XsltTask.php 144 2007-02-05 15:19:00Z hans $
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

require_once 'phing/tasks/system/CopyTask.php';
include_once 'phing/system/io/FileReader.php';
include_once 'phing/system/io/FileWriter.php';
include_once 'phing/filters/XsltFilter.php';

/**
 * Implements an XSLT processing filter while copying files.
 *
 * This is a shortcut for calling the <copy> task with the XSLTFilter used
 * in the <filterchains> section.
 * 
 * @author    Andreas Aderhold, andi@binarycloud.com
 * @version   $Revision: 1.8 $
 * @package   phing.tasks.system
 */
class XsltTask extends CopyTask {
    
    /** XSLTFilter object that we use to handle transformation. */
    private $xsltFilter;
    
    /** Parameters to pass to XSLT procesor. */
    private $parameters = array();
    
    /**
     * Setup the filterchains w/ XSLTFilter that we will use while copying the files.
     */
    function init() {
        $xf = new XsltFilter();
        $chain = $this->createFilterChain($this->getProject());
        $chain->addXsltFilter($xf);
        $this->xsltFilter = $xf;        
    }
    
    /**
     * Set any XSLT Param and invoke CopyTask::main()
     * @see CopyTask::main()
     */
    function main() {        
        $this->log("Doing XSLT transformation using stylesheet " . $this->xsltFilter->getStyle(), Project::MSG_VERBOSE);
        $this->xsltFilter->setParams($this->parameters);
        parent::main();
    }
    
    /**
     * Set the stylesheet to use.
     * @param PhingFile $style
     */
    function setStyle(PhingFile $style) {
        $this->xsltFilter->setStyle($style);
    }
    
    /**
     * Support nested <param> tags useing XSLTParam class.
     * @return XSLTParam
     */
    function createParam() {
        $num = array_push($this->parameters, new XSLTParam());
        return $this->parameters[$num-1];
    }
}
