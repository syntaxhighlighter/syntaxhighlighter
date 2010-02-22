<?php

/*
 *  $Id: XsltFilter.php 380 2008-07-29 16:13:18Z mrook $
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
 * Applies XSL stylesheet to incoming text.
 * 
 * Uses PHP XSLT support (libxslt).
 * 
 * @author    Hans Lellelid <hans@velum.net>
 * @author    Yannick Lecaillez <yl@seasonfive.com>
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @version   $Revision: 1.16 $
 * @see       FilterReader
 * @package   phing.filters
 */
class XsltFilter extends BaseParamFilterReader implements ChainableReader {

    /**
     * Path to XSL stylesheet.
     * @var string
     */
    private $xslFile   = null;

    /**
     * Whether XML file has been transformed.
     * @var boolean
     */
    private $processed = false;
    
    /**
     * XSLT Params.
     * @var array
     */
    private $xsltParams = array();    
    
    /**
     * Whether to use loadHTML() to parse the input XML file.
     */
    private $html = false;
    
    /**
     * Create new XSLT Param object, to handle the <param/> nested element.
     * @return XSLTParam
     */
    function createParam() {
        $num = array_push($this->xsltParams, new XSLTParam());
        return $this->xsltParams[$num-1];
    }
    
    /**
     * Sets the XSLT params for this class.
     * This is used to "clone" this class, in the chain() method.
     * @param array $params
     */
    function setParams($params) {
        $this->xsltParams = $params;
    }
    
    /**
     * Returns the XSLT params set for this class.
     * This is used to "clone" this class, in the chain() method.
     * @return array
     */
    function getParams() {
        return $this->xsltParams;
    }
        
    /**
     * Set the XSLT stylesheet.
     * @param mixed $file PhingFile object or path.
     */
    function setStyle(PhingFile $file) {
        $this->xslFile = $file;
    }

    /**
     * Whether to use HTML parser for the XML.
     * This is supported in libxml2 -- Yay!
     * @return boolean
     */
    function getHtml() {
        return $this->html;
    }
    
    /**
     * Whether to use HTML parser for XML.
     * @param boolean $b
     */
    function setHtml($b) {        
        $this->html = (boolean) $b;
    }
    
    /**
     * Get the path to XSLT stylesheet.
     * @return mixed XSLT stylesheet path.
     */
    function getStyle() {
        return $this->xslFile;
    }
    
    /**
     * Reads stream, applies XSLT and returns resulting stream.
     * @return string transformed buffer.
     * @throws BuildException - if XSLT support missing, if error in xslt processing
     */
    function read($len = null) {
        
        if (!class_exists('XSLTProcessor')) {
            throw new BuildException("Could not find the XSLTProcessor class. Make sure PHP has been compiled/configured to support XSLT.");
        }
        
        if ($this->processed === true) {
            return -1; // EOF
        }
        
        if ( !$this->getInitialized() ) {
            $this->_initialize();
            $this->setInitialized(true);
        }

        // Read XML
        $_xml = null;
        while ( ($data = $this->in->read($len)) !== -1 )
            $_xml .= $data;

        if ($_xml === null ) { // EOF?
            return -1;
        }

        if(empty($_xml)) {
            $this->log("XML file is empty!", Project::MSG_WARN);
            return ''; // return empty string, don't attempt to apply XSLT
        }
       
        // Read XSLT
        $_xsl = null;
        $xslFr = new FileReader($this->xslFile);
        $xslFr->readInto($_xsl);
        
        $this->log("Tranforming XML " . $this->in->getResource() . " using style " . $this->xslFile->getPath(), Project::MSG_VERBOSE);
        
        $out = '';
        try {
            $out = $this->process($_xml, $_xsl);
            $this->processed = true;
        } catch (IOException $e) {            
            throw new BuildException($e);
        }

        return $out;
    }

    // {{{ method _ProcessXsltTransformation($xml, $xslt) throws BuildException
    /**
     * Try to process the XSLT transformation
     *
     * @param   string  XML to process.
     * @param   string  XSLT sheet to use for the processing.
     *
     * @throws BuildException   On XSLT errors
     */
    protected function process($xml, $xsl) {    
                
        $processor = new XSLTProcessor();
        
        $xmlDom = new DOMDocument();
        $xslDom = new DOMDocument();        
        
        if ($this->html) {            
            $xmlDom->loadHTML($xml);
        } else {
            $xmlDom->loadXML($xml);
        }
        
        $xslDom->loadxml($xsl);
        
        $processor->importStylesheet($xslDom);

        // ignoring param "type" attrib, because
        // we're only supporting direct XSL params right now
        foreach($this->xsltParams as $param) {
            $this->log("Setting XSLT param: " . $param->getName() . "=>" . $param->getExpression(), Project::MSG_DEBUG);
            $processor->setParameter(null, $param->getName(), $param->getExpression());
        }
        
        $errorlevel = error_reporting();
        error_reporting($errorlevel & ~E_WARNING);
        @$result = $processor->transformToXML($xmlDom);
        error_reporting($errorlevel);
        
        if ( !$result ) {
            //$errno = xslt_errno($processor);
            //$err   = xslt_error($processor);    
            throw new BuildException("XSLT Error");            
        } else {
            return $result;
        }
    }    

    /**
     * Creates a new XsltFilter using the passed in
     * Reader for instantiation.
     *
     * @param Reader A Reader object providing the underlying stream.
     *               Must not be <code>null</code>.
     *
     * @return Reader A new filter based on this configuration, but filtering
     *         the specified reader
     */
    function chain(Reader $reader) {
        $newFilter = new XsltFilter($reader);
        $newFilter->setProject($this->getProject());
        $newFilter->setStyle($this->getStyle());
        $newFilter->setInitialized(true);
        $newFilter->setParams($this->getParams());
        $newFilter->setHtml($this->getHtml());
        return $newFilter;
    }

    /**
     * Parses the parameters to get stylesheet path.
     */
    private function _initialize() {        
        $params = $this->getParameters();
        if ( $params !== null ) {
            for($i = 0, $_i=count($params) ; $i < $_i; $i++) {
                if ( $params[$i]->getType() === null ) {
                    if ($params[$i]->getName() === "style") {
                        $this->setStyle($params[$i]->getValue());
                    }
                } elseif ($params[$i]->getType() == "param") {
                    $xp = new XSLTParam();
                    $xp->setName($params[$i]->getName());
                    $xp->setExpression($params[$i]->getValue());
                    $this->xsltParams[] = $xp;
                }
            }
        }
    }

}


/**
 * Class that holds an XSLT parameter.
 */
class XSLTParam {
    
    private $name;
    
    private $expr;    
    
    /**
     * Sets param name.
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }
    
    /**
     * Get param name.
     * @return string
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * Sets expression value (alias to the setExpression()) method. 
     *
     * @param string $v
     * @see setExpression()
     */
    public function setValue($v)
    {
    	$this->setExpression($v);
    }
    
	/**
     * Gets expression value (alias to the getExpression()) method. 
     *
     * @param string $v
     * @see getExpression()
     */
    public function getValue()
    {
    	return $this->getExpression();
    }
    
    /**
     * Sets expression value.
     * @param string $expr
     */
    public function setExpression($expr) {
        $this->expr = $expr;
    }
    
    /**
     * Sets expression to dynamic register slot.
     * @param RegisterSlot $expr
     */
    public function setListeningExpression(RegisterSlot $expr) {
        $this->expr = $expr;    
    }
    
    /**
     * Returns expression value -- performs lookup if expr is registerslot.
     * @return string
     */
    public function getExpression() {
        if ($this->expr instanceof RegisterSlot) {
            return $this->expr->getValue();
        } else {
            return $this->expr;
        }
    }        
}

