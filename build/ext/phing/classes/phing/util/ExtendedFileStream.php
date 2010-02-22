<?php

	include_once 'phing/system/io/PhingFile.php';

	/**
	 * $Id: ExtendedFileStream.php 325 2007-12-20 15:44:58Z hans $
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
	 * Extended file stream wrapper class which auto-creates directories
	 *
	 * @author Michiel Rook <michiel.rook@gmail.com>
	 * @version $Id: ExtendedFileStream.php 325 2007-12-20 15:44:58Z hans $
	 * @package phing.util
	 */
	class ExtendedFileStream
	{
		private $fp = NULL;
		
		static function registerStream()
		{
			if (!in_array("efile", stream_get_wrappers()))
			{
				stream_wrapper_register("efile", "ExtendedFileStream");
			}
		}
		
		private function createDirectories($path)
		{
			$f = new PhingFile($path);
			if (!$f->exists()) {
				$f->mkdirs();
			}
		}
		
		function stream_open($path, $mode, $options, &$opened_path)
		{
			/** Small fix for Windows */
			if ($path[8] == DIRECTORY_SEPARATOR)
			{
				$filepath = substr($path, 7);
			}
			else
			{
				$filepath = substr($path, 8);
			}
			
			$this->createDirectories(dirname($filepath));
			
			$this->fp = fopen($filepath, $mode);
			
			return true;
		}
		
		function stream_close()
		{
			fclose($this->fp);
			$this->fp = NULL;
		}
		
		function stream_read($count)
		{
			return fread($this->fp, $count);
		}
		
		function stream_write($data)
		{
			return fwrite($this->fp, $data);
		}
		
		function stream_eof()
		{
			return feof($this->fp);
		}
		
		function stream_tell()
		{
			return ftell($this->fp);
		}
		
		function stream_seek($offset, $whence)
		{
			return fseek($this->fp, $offset, $whence);
		}
		
		function stream_flush()
		{
			return fflush($this->fp);
		}
		
		function stream_stat()
		{
			return fstat($this->fp);
		}
		
		function unlink($path)
		{
			return FALSE;
		}
		
		function rename($path_from, $path_to)
		{
			return FALSE;
		}
		
		function mkdir($path, $mode, $options)
		{
			return FALSE;
		}
		
		function rmdir($path, $options)
		{
			return FALSE;
		}		
	};

