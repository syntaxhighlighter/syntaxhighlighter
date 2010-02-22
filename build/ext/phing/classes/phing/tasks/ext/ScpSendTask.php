<?php
/**
 * $Id: ScpSendTask.php 325 2007-12-20 15:44:58Z hans $
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

/**
 * SCPs a File to a remote server 
 *
 * @author Andrew Eddie <andrew.eddie@jamboworks.com> 
 * @version $Id: ScpSendTask.php 325 2007-12-20 15:44:58Z hans $
 * @package phing.tasks.ext
 * @since 2.3.0
 */
class ScpSendTask extends Task
{
	private $localFile = "";

	private $remoteFile = "";

	private $username = "";

	private $password = "";

	private $host = "";

	private $port = 22;

	private $mode = null;

	private $_connection = null;

	/**
	 * Sets the remote host
	 */
	function setHost($h)
	{
		$this->host = $h;
	}

	/**
	 * Returns the remote host
	 */
	function getHost()
	{
		return $this->host;
	}

	/**
	 * Sets the remote host port
	 */
	function setPort($p)
	{
		$this->port = $p;
	}

	/**
	 * Returns the remote host port
	 */
	function getPort()
	{
		return $this->port;
	}

	/**
	 * Sets the mode value
	 */
	function setMode($value)
	{
		$this->mode = $value;
	}

	/**
	 * Returns the mode value
	 */
	function getMode()
	{
		return $this->mode;
	}

	/**
	 * Sets the username of the user to scp
	 */
	function setUsername($username)
	{
		$this->username = $username;
	}

	/**
	 * Returns the username
	 */
	function getUsername()
	{
		return $this->username;
	}

	/**
	 * Sets the password of the user to scp
	 */
	function setPassword($password)
	{
		$this->password = $password;
	}

	/**
	 * Returns the password
	 */
	function getPassword()
	{
		return $this->password;
	}

	/**
	 * Sets the local path to scp from
	 */
	function setLocalFile($lFile)
	{
		$this->localFile = $lFile;
	}

	/**
	 * Returns the local path to scp from
	 */
	function getLocalFile($lFile)
	{
		return $this->localFile;
	}

	/**
	 * Sets the remote path to scp to
	 */
	function setRemoteFile($rFile)
	{
		$this->remoteFile = $rFile;
	}

	/**
	 * Returns the remote path to scp to
	 */
	function getRemoteFile($rFile)
	{
		return $this->remoteFile;
	}

	/**
	* The init method: Do init steps.
	*/
	public function init()
	{
		if (function_exists('ssh2_connect')) {
			$this->_connection = ssh2_connect($this->host, $this->port);
			ssh2_auth_password($this->_connection, $this->username, $this->password);
		} else {
			print ("ERROR: SSH Extension is not installed");
		}
	}

	/**
	 * The main entry point method.
	 */
	public function main()
	{
		if (function_exists('ssh2_scp_send') && !is_null($this->_connection))
		{
			if (!is_null($this->mode)) {
				ssh2_scp_send($this->_connection, $this->localFile, $this->remoteFile, $this->mode);
			} else {
				ssh2_scp_send($this->_connection, $this->localFile, $this->remoteFile);
			}
		} else {
			print ("ERROR: No SSH Connection Available");
		}
	}
}

