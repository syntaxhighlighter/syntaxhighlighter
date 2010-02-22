<?php
/*
 *  $Id: SvnBaseTask.php 329 2007-12-22 17:12:59Z mrook $
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
 
include_once 'phing/Task.php';

/**
 * Base class for Subversion tasks
 *
 * @author Michiel Rook <michiel.rook@gmail.com>
 * @author Andrew Eddie <andrew.eddie@jamboworks.com> 
 * @version $Id: SvnBaseTask.php 329 2007-12-22 17:12:59Z mrook $
 * @package phing.tasks.ext.svn
 * @see VersionControl_SVN
 * @since 2.2.0
 */
abstract class SvnBaseTask extends Task
{
	private $workingCopy = "";
	
	private $repositoryUrl = "";
	
	private $svnPath = "/usr/bin/svn";
	
	private $svn = NULL;
	
	private $mode = "";
	
	private $svnArgs = array();
	
	private $svnSwitches = array();

	private $toDir = "";

	/**
	 * Initialize Task.
 	 * This method includes any necessary SVN libraries and triggers
	 * appropriate error if they cannot be found.  This is not done in header
	 * because we may want this class to be loaded w/o triggering an error.
	 */
	function init() {
		include_once 'VersionControl/SVN.php';
		if (!class_exists('VersionControl_SVN')) {
			throw new Exception("SvnLastRevisionTask depends on PEAR VersionControl_SVN package being installed.");
		}
	}

	/**
	 * Sets the path to the workingcopy
	 */
	function setWorkingCopy($workingCopy)
	{
		$this->workingCopy = $workingCopy;
	}

	/**
	 * Returns the path to the workingcopy
	 */
	function getWorkingCopy()
	{
		return $this->workingCopy;
	}

	/**
	 * Sets the path/URI to the repository
	 */
	function setRepositoryUrl($repositoryUrl)
	{
		$this->repositoryUrl = $repositoryUrl;
	}

	/**
	 * Returns the path/URI to the repository
	 */
	function getRepositoryUrl()
	{
		return $this->repositoryUrl;
	}

	/**
	 * Sets the path to the SVN executable
	 */
	function setSvnPath($svnPath)
	{
		$this->svnPath = $svnPath;
	}

	/**
	 * Returns the path to the SVN executable
	 */
	function getSvnPath()
	{
		return $this->svnPath;
	}

	//
	// Args
	//

	/**
	 * Sets the path to export/checkout to
	 */
	function setToDir($toDir)
	{
		$this->toDir = $toDir;
	}

	/**
	 * Returns the path to export/checkout to
	 */
	function getToDir()
	{
		return $this->toDir;
	}

	//
	// Switches
	//

	/**
	 * Sets the force switch
	 */
	function setForce($value)
	{
		$this->svnSwitches['force'] = $value;
	}

	/**
	 * Returns the force switch
	 */
	function getForce()
	{
		return isset( $this->svnSwitches['force'] ) ? $this->svnSwitches['force'] : '';
	}

	/**
	 * Sets the username of the user to export
	 */
	function setUsername($value)
	{
		$this->svnSwitches['username'] = $value;
	}

	/**
	 * Returns the username
	 */
	function getUsername()
	{
		return isset( $this->svnSwitches['username'] ) ? $this->svnSwitches['username'] : '';
	}

	/**
	 * Sets the password of the user to export
	 */
	function setPassword($value)
	{
		$this->svnSwitches['password'] = $value;
	}

	/**
	 * Returns the password
	 */
	function getPassword()
	{
		return isset( $this->svnSwitches['password'] ) ? $this->svnSwitches['password'] : '';
	}

	/**
	 * Sets the no-auth-cache switch
	 */
	function setNoCache($value)
	{
		$this->svnSwitches['no-auth-cache'] = $value;
	}

	/**
	 * Returns the no-auth-cache switch
	 */
	function getNoCache()
	{
		return isset( $this->svnSwitches['no-auth-cache'] ) ? $this->svnSwitches['no-auth-cache'] : '';
	}
	
	/**
	 * Sets the non-recursive switch
	 */
	function setRecursive($value)
	{
		$this->svnSwitches['non-recursive'] = is_bool($value) ? !$value : TRUE;
	}
	
	/**
	 * Returns the non-recursive switch
	 */
	function getRecursive()
	{
		return isset( $this->svnSwitches['non-recursive'] ) ? $this->svnSwitches['non-recursive'] : '';
	}

	/**
	 * Sets the ignore-externals switch
	 */
	function setIgnoreExternals($value)
	{
		$this->svnSwitches['ignore-externals'] = $value;
	}
	
	/**
	 * Returns the ignore-externals switch
	 */
	function getIgnoreExternals()
	{
		return isset( $this->svnSwitches['ignore-externals'] ) ? $this->svnSwitches['ignore-externals'] : '';
	}
	
	/**
	 * Creates a VersionControl_SVN class based on $mode
	 *
	 * @param string The SVN mode to use (info, export, checkout, ...)
	 * @throws BuildException
	 */
	protected function setup($mode)
	{
		$this->mode = $mode;
		
		// Set up runtime options. Will be passed to all
		// subclasses.
		$options = array('fetchmode' => VERSIONCONTROL_SVN_FETCHMODE_ASSOC, 'svn_path' => $this->getSvnPath());
		
		// Pass array of subcommands we need to factory
		$this->svn = VersionControl_SVN::factory($mode, $options);

		if (!empty($this->repositoryUrl))
		{
			$this->svnArgs = array($this->repositoryUrl);
		}
		else
		if (!empty($this->workingCopy))
		{
			if (is_dir($this->workingCopy))
			{
				if (in_array(".svn", scandir($this->workingCopy)))
				{
					$this->svnArgs = array($this->workingCopy);
				}
				else
				{
					throw new BuildException("'".$this->workingCopy."' doesn't seem to be a working copy");
				}
			}
			else
			if ($mode=='info' )
			{
				if (is_file($this->workingCopy))
				{
					$this->svnArgs = array($this->workingCopy);
				}
				else
				{
					throw new BuildException("'".$this->workingCopy."' is not a directory nor a file");
				}
			}
			else
			{
				throw new BuildException("'".$this->workingCopy."' is not a directory");
			}
		}
	}
	
	/**
	 * Executes the constructed VersionControl_SVN instance
	 *
	 * @param array Additional arguments to pass to SVN.
	 * @param array Switches to pass to SVN.
	 * @return string Output generated by SVN.
	 */
	protected function run($args = array(), $switches = array())
	{
		$svnstack = PEAR_ErrorStack::singleton('VersionControl_SVN');
		
		$tempArgs = $this->svnArgs;
		
		$tempArgs = array_merge($tempArgs, $args);

		$tempSwitches = $this->svnSwitches;
		
		$tempSwitches = array_merge($tempSwitches, $switches);

		if ($output = $this->svn->run($tempArgs, $tempSwitches))
		{
			return $output;
		}
		else
		{
			if (count($errs = $svnstack->getErrors()))
			{
				$err = current($errs);

				throw new BuildException("Failed to run the 'svn " . $this->mode . "' command: " . $err['message']);
			}
		}
	}
}

