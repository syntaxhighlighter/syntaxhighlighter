<?php
/*
 *  $Id: RuntimeConfigurable.php 123 2006-09-14 20:19:08Z mrook $
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
 *  Wrapper class that holds the attributes of a Task (or elements
 *  nested below that level) and takes care of configuring that element
 *  at runtime.
 *
 *  <strong>SMART-UP INLINE DOCS</strong>
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.6 $
 * @package   phing
 */
class RuntimeConfigurable {

    private $elementTag = null;
    private $children = array();
    private $wrappedObject = null;
    private $attributes = array();
    private $characters = "";


    /** @param proxy The element to wrap. */
    function __construct($proxy, $elementTag) {
        $this->wrappedObject = $proxy;
        $this->elementTag = $elementTag;
    }

    function setProxy($proxy) {
        $this->wrappedObject = $proxy;
    }

    /** Set's the attributes for the wrapped element. */
    function setAttributes($attributes) {
        $this->attributes = $attributes;
    }

    /** Returns the AttributeList of the wrapped element. */
    function getAttributes() {
        return $this->attributes;
    }

    /** Adds child elements to the wrapped element. */
    function addChild(RuntimeConfigurable $child) {
        $this->children[] = $child;
    }

    /** Returns the child with index */
    function getChild($index) {
        return $this->children[(int)$index];
    }

    /** Add characters from #PCDATA areas to the wrapped element. */
    function addText($data) {
        $this->characters .= (string) $data;
    }

    function getElementTag() {
        return $this->elementTag;
    }


    /** Configure the wrapped element and all children. */
    function maybeConfigure(Project $project) {
        $id = null;

        // DataType configured in ProjectConfigurator
        //        if ( is_a($this->wrappedObject, "DataType") )
        //            return;

        if ($this->attributes || $this->characters) {
            ProjectConfigurator::configure($this->wrappedObject, $this->attributes, $project);

            if (isset($this->attributes["id"])) {
                $id = $this->attributes["id"];
            }

            $this->attributes = null;

            if ($this->characters) {
                ProjectConfigurator::addText($project, $this->wrappedObject, (string) $this->characters);
                $this->characters="";
            }
            if ($id !== null) {
                $project->addReference($id, $this->wrappedObject);
            }
        }

        if ( is_array($this->children) && !empty($this->children) ) {
            // Configure all child of this object ...
            foreach($this->children as $child) {
                $child->maybeConfigure($project);
                ProjectConfigurator::storeChild($project, $this->wrappedObject, $child->wrappedObject, strtolower($child->getElementTag()));
            }
        }
    }
}

