<?php

/*
 *  $Id: XincludeFilter.php,v 1.16 2005/12/07 20:05:01 hlellelid Exp $
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

include_once 'phing/filters/BaseParamFilterReader.php';
include_once 'phing/filters/ChainableReader.php';

/**
 * Applies Xinclude parsing to incoming text.
 * 
 * Uses PHP DOM XML support
 * 
 * @author    Bill Karwin <bill@karwin.com>
 * @version   $Revision: 1.16 $
 * @see       FilterReader
 * @package   phing.filters
 */
class XincludeFilter extends BaseParamFilterReader implements ChainableReader {

    private $basedir = null;

    public function setBasedir(PhingFile $dir)
    {
        $this->basedir = $dir;
    }

    public function getBasedir()
    {
        return $this->basedir;
    }

    /**
     * Reads stream, applies XSLT and returns resulting stream.
     * @return string transformed buffer.
     * @throws BuildException - if XSLT support missing, if error in xslt processing
     */
    function read($len = null) {
        
        if (!class_exists('DomDocument')) {
            throw new BuildException("Could not find the DomDocument class. Make sure PHP has been compiled/configured to support DOM XML.");
        }
        
        if ($this->processed === true) {
            return -1; // EOF
        }
        
        // Read XML
        $_xml = null;
        while ( ($data = $this->in->read($len)) !== -1 )
            $_xml .= $data;

        if ($_xml === null ) { // EOF?
            return -1;
        }

        if (empty($_xml)) {
            $this->log("XML file is empty!", Project::MSG_WARN);
            return ''; 
        }
       
        $this->log("Transforming XML " . $this->in->getResource() . " using Xinclude ", Project::MSG_VERBOSE);
        
        $out = '';
        try {
            $out = $this->process($_xml);
            $this->processed = true;
        } catch (IOException $e) {            
            throw new BuildException($e);
        }

        return $out;
    }

    /**
     * Try to process the Xinclude transformation
     *
     * @param   string  XML to process.
     *
     * @throws BuildException   On errors
     */
    protected function process($xml) {    
                
        if ($this->basedir) {
            $cwd = getcwd();
            chdir($this->basedir);
        }

        $xmlDom = new DomDocument();
        $xmlDom->loadXML($xml);
        
        $xmlDom->xinclude();

        if ($this->basedir) {
            chdir($cwd);
        }

        return $xmlDom->saveXML();
    }    

    /**
     * Creates a new XincludeFilter using the passed in
     * Reader for instantiation.
     *
     * @param Reader A Reader object providing the underlying stream.
     *               Must not be <code>null</code>.
     *
     * @return Reader A new filter based on this configuration, but filtering
     *         the specified reader
     */
    function chain(Reader $reader) {
        $newFilter = new XincludeFilter($reader);
        $newFilter->setProject($this->getProject());
        $newFilter->setBasedir($this->getBasedir());
        return $newFilter;
    }

}


