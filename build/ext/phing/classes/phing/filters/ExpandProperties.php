<?php

/*
 *  $Id: ExpandProperties.php 325 2007-12-20 15:44:58Z hans $
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

require_once 'phing/filters/BaseFilterReader.php';
include_once 'phing/filters/ChainableReader.php';

/**
 * Expands Phing Properties, if any, in the data.
 * <p>
 * Example:<br>
 * <pre><expandproperties/></pre>
 * Or:
 * <pre><filterreader classname="phing.filters.ExpandProperties'/></pre>
 *
 * @author    Yannick Lecaillez <yl@seasonfive.com>
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.6 $
 * @see       BaseFilterReader
 * @package   phing.filters
 */
class ExpandProperties extends BaseFilterReader implements ChainableReader {
   
    /**
     * Returns the filtered stream. 
     * The original stream is first read in fully, and the Phing properties are expanded.
     * 
     * @return mixed     the filtered stream, or -1 if the end of the resulting stream has been reached.
     * 
     * @exception IOException if the underlying stream throws an IOException
     * during reading
     */
    function read($len = null) {
                
        $buffer = $this->in->read($len);
        
        if($buffer === -1) {
            return -1;
        }
        
        $project = $this->getProject();
        $buffer = ProjectConfigurator::replaceProperties($project, $buffer, $project->getProperties());
        
        return $buffer;
    }
        
    /**
     * Creates a new ExpandProperties filter using the passed in
     * Reader for instantiation.
     * 
     * @param object A Reader object providing the underlying stream.
     *               Must not be <code>null</code>.
     * 
     * @return object A new filter based on this configuration, but filtering
     *         the specified reader
     */
    function chain(Reader $reader) {
        $newFilter = new ExpandProperties($reader);
        $newFilter->setProject($this->getProject());
        return $newFilter;
    }
}


