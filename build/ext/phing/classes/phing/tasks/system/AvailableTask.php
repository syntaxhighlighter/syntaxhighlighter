<?php
/*
 *  $Id: AvailableTask.php 333 2007-12-28 21:30:09Z hans $
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
include_once 'phing/tasks/system/condition/ConditionBase.php';

/**
 *  <available> task.
 *
 *  Note: implements condition interface (see condition/Condition.php)
 *
 *  @author    Andreas Aderhold <andi@binarycloud.com>
 *  @copyright � 2001,2002 THYRELL. All rights reserved
 *  @version   $Revision: 1.11 $
 *  @package   phing.tasks.system
 */
class AvailableTask extends Task {

	/** Property to check for. */
	private $property;

	/** Value property should be set to. */
	private $value = "true";

	/** Resource to check for */
	private $resource;

	private $type = null;
	private $filepath = null;

	function setProperty($property) {
		$this->property = (string) $property;
	}

	function setValue($value) {
		$this->value = (string) $value;
	}

	function setFile(PhingFile $file) {
		$this->file = $file;
	}

	function setResource($resource) {
		$this->resource = (string) $resource;
	}

	function setType($type) {
		$this->type = (string) strtolower($type);
	}

	function main() {
		if ($this->property === null) {
			throw new BuildException("property attribute is required", $this->location);
		}
		if ($this->evaluate()) {
			$this->project->setProperty($this->property, $this->value);
		}
	}

	function evaluate() {
		if ($this->file === null && $this->resource === null) {
			throw new BuildException("At least one of (file|resource) is required", $this->location);
		}

		if ($this->type !== null && ($this->type !== "file" && $this->type !== "dir")) {
			throw new BuildException("Type must be one of either dir or file", $this->location);
		}

		if (($this->file !== null) && !$this->_checkFile()) {
			$this->log("Unable to find " . $this->file->__toString() . " to set property " . $this->property, Project::MSG_VERBOSE);
			return false;
		}

		if (($this->resource !== null) && !$this->_checkResource($this->resource)) {
			$this->log("Unable to load resource " . $this->resource . " to set property " . $this->property, Project::MSG_VERBOSE);
			return false;
		}

		return true;
	}

	// this is prepared for the path type
	private function _checkFile() {
		if ($this->filepath === null) {
			return $this->_checkFile1($this->file);
		} else {
			$paths = $this->filepath->listDir();
			for($i=0,$pcnt=count($paths); $i < $pcnt; $i++) {
				$this->log("Searching " . $paths[$i], Project::MSG_VERBOSE);
				$tmp = new PhingFile($paths[$i], $this->file->getName());
				if($tmp->isFile()) {
					return true;
				}
			}
		}
		return false;
	}

	private function _checkFile1(PhingFile $file) {
		if ($this->type !== null) {
			if ($this->type === "dir") {
				return $file->isDirectory();
			} else if ($this->type === "file") {
				return $file->isFile();
			}
		}
		return $file->exists();
	}
	
	private function _checkResource($resource) {
		if (null != ($resourcePath = Phing::getResourcePath($resource))) {
			return $this->_checkFile1(new PhingFile($resourcePath));
		} else {
			return false;
		}
	}
}
