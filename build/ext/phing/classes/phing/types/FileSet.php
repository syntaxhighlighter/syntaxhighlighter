<?php

/*
 * $Id: FileSet.php 123 2006-09-14 20:19:08Z mrook $
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

require_once 'phing/types/AbstractFileSet.php';

/**
 * Moved out of MatchingTask to make it a standalone object that could
 * be referenced (by scripts for example).
 *
 * @author Hans Lellelid <hans@xmpl.org> (Phing)
 * @author Arnout J. Kuiper <ajkuiper@wxs.nl> (Ant)
 * @author Stefano Mazzocchi <stefano@apache.org> (Ant)
 * @author Sam Ruby <rubys@us.ibm.com> (Ant)
 * @author Jon S. Stevens <jon@clearink.com> (Ant)
 * @author Stefan Bodewig <stefan.bodewig@epost.de> (Ant)
 * @author Magesh Umasankar (Ant)
 * @package phing.types
 */
class FileSet extends AbstractFileSet {
    
    function __construct($fileset = null) {
        parent::__construct($fileset);
    }

    /**
     * Return a FileSet that has the same basedir and same patternsets
     * as this one.
     */
    public function __clone() {
        if ($this->isReference()) {
            return new FileSet($this->getRef($this->getProject()));
        } else {
            return new FileSet($this);
        }
    }

}
