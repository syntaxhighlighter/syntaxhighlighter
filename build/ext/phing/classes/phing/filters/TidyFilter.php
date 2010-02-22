<?php
/*
 *  $Id: TidyFilter.php 123 2006-09-14 20:19:08Z mrook $  
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
 * This filter uses the bundled-with-PHP Tidy extension to filter input.
 * 
 * <p>
 * Example:<br/>
 * <pre>
 * <tidyfilter encoding="utf8">
 *   <config name="indent" value="true"/>
 *   <config name="output-xhtml" value="true"/>
 * </tidyfilter>
 * </pre>
 * 
 * @author Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.2 $ $Date: 2006-09-14 16:19:08 -0400 (Thu, 14 Sep 2006) $
 * @package   phing.filters
 */
class TidyFilter extends BaseParamFilterReader implements ChainableReader {
   	
	/** @var string Encoding of resulting document. */
	private $encoding = 'utf8';
   
    /** @var array Parameter[] */
	private $configParameters = array();
     
	/**
	 * Set the encoding for resulting (X)HTML document.
	 * @param string $v
	 */
	public function setEncoding($v) {
		$this->encoding = $v;
	}
	
	/**
	 * Sets the config params.
	 * @param array Parameter[]
	 * @see chain()
	 */
	public function setConfigParameters($params)
	{
		$this->configParameters = $params;
	}
	
	/**
	 * Adds a <config> element (which is a Parameter).
	 * @return Parameter
	 */
	public function createConfig() {
		$num = array_push($this->configParameters, new Parameter());
        return $this->configParameters[$num-1];
	}
	
	/**
	 * Converts the Parameter objects being used to store configuration into a simle assoc array.
	 * @return array
	 */
	private function getDistilledConfig() {
		$config = array();
		foreach($this->configParameters as $p) {
			$config[$p->getName()] = $p->getValue();
		}
		return $config;
	}
	
    /**
     * Reads input and returns Tidy-filtered output.
     * 
     * @return the resulting stream, or -1 if the end of the resulting stream has been reached
     * 
     * @throws IOException if the underlying stream throws an IOException
     *                        during reading     
     */
    function read($len = null) {
    	
		if (!class_exists('Tidy')) {
			throw new BuildException("You must enable the 'tidy' extension in your PHP configuration in order to use the Tidy filter.");
		}
		
		if ( !$this->getInitialized() ) {
            $this->_initialize();
            $this->setInitialized(true);
        }
		
        $buffer = $this->in->read($len);
        if($buffer === -1) {
            return -1;
        }
		
		$config = $this->getDistilledConfig();
		
		$tidy = new Tidy();
		$tidy->parseString($buffer, $config, $this->encoding);
		$tidy->cleanRepair();

		return tidy_get_output($tidy);
		
    }


    /**
     * Creates a new TidyFilter using the passed in Reader for instantiation.
     * 
     * @param reader A Reader object providing the underlying stream.
     *               Must not be <code>null</code>.
     * 
     * @return a new filter based on this configuration, but filtering
     *         the specified reader
     */
    public function chain(Reader $reader) {
        $newFilter = new TidyFilter($reader);
		$newFilter->setConfigParameters($this->configParameters);
		$newFilter->setEncoding($this->encoding);
        $newFilter->setProject($this->getProject());
        return $newFilter;
    }
	
	/**
     * Initializes any parameters (e.g. config options).
     * This method is only called when this filter is used through a <filterreader> tag in build file.
     */
    private function _initialize() {
        $params = $this->getParameters();
		if ($params) {
			foreach($params as $param) {
				if ($param->getType() == "config") {
					$this->configParameters[] = $param;
				} else {
					
					if ($param->getName() == "encoding") {
					    $this->setEncoding($param->getValue());
					}
					
				}
				
			}
		}
    }

}
