<?php
/*
 *  $Id: EventObject.php 325 2007-12-20 15:44:58Z hans $
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
 *  @package phing.system.lang
 */
class EventObject {

    /** The object on which the Event initially occurred. */
    protected $source;

    /** Constructs a prototypical Event. */
    function __construct($source) {
        if ($source === null) {
            throw new Exception("Null source");
        }
        $this->source = $source;
    }

    /** The object on which the Event initially occurred. */
    function getSource() {
        return $this->source;
    }

    /** Returns a String representation of this EventObject.*/
    function toString() {
        if (method_exists($this->source, "toString")) {
            return get_class($this)."[source=".$this->source->toString()."]";
        } else {
            return get_class($this)."[source=".get_class($this->source)."]";
        }
    }
}

