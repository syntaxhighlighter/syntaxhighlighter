<?php
/**
 * $Id: PHPUnitUtil.php 325 2007-12-20 15:44:58Z hans $
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
 * Various utility functions
 *
 * @author Michiel Rook <michiel.rook@gmail.com>
 * @version $Id: PHPUnitUtil.php 325 2007-12-20 15:44:58Z hans $
 * @package phing.tasks.ext.phpunit
 * @since 2.1.0
 */
class PHPUnitUtil
{
	/**
	 * Installed PHPUnit major version
	 */
	public static $installedVersion = 2;
	
	/**
	 * Installed PHPUnit minor version
	 */
	public static $installedMinorVersion = 0;
	
	protected static $definedClasses = array();
	
	/**
	 * Returns the package of a class as defined in the docblock of the class using @package
	 *
	 * @param string the name of the class
	 * @return string the name of the package
	 */
	static function getPackageName($classname)
	{
		$reflect = new ReflectionClass($classname);

		if (preg_match('/@package[\s]+([\.\w]+)/', $reflect->getDocComment(), $matches))
		{
			return $matches[1];
		}
		else
		{
			return "default";
		}
	}
	
	/**
	 * Derives the classname from a filename.
	 * Assumes that there is only one class defined in that particular file, and that
	 * the naming follows the dot-path (Java) notation scheme.
	 *
	 * @param string the filename
	 * @return string the name fo the class
	 */
	static function getClassFromFileName($filename)
	{
		$filename = basename($filename);
		
		$rpos = strrpos($filename, '.');
		
		if ($rpos != -1)
		{
			$filename = substr($filename, 0, $rpos);
		}
		
		return $filename;
	}

	/**
	 * @param string the filename
	 * @param Path optional classpath
	 * @return array list of classes defined in the file
	 */
	static function getDefinedClasses($filename, $classpath = NULL)
	{
		$filename = realpath($filename);
		
		if (!file_exists($filename))
		{
			throw new Exception("File '" . $filename . "' does not exist");
		}
		
		if (isset(self::$definedClasses[$filename]))
		{
			return self::$definedClasses[$filename];
		}
		
		Phing::__import($filename, $classpath);

		$declaredClasses = get_declared_classes();
		
		foreach ($declaredClasses as $classname)
		{
			$reflect = new ReflectionClass($classname);
			
			self::$definedClasses[$reflect->getFilename()][] = $classname;
			
			if (is_array(self::$definedClasses[$reflect->getFilename()]))
			{			
				self::$definedClasses[$reflect->getFilename()] = array_unique(self::$definedClasses[$reflect->getFilename()]);
			}
		}
		
		if (isset(self::$definedClasses[$filename]))
		{
			return self::$definedClasses[$filename];
		}
		else
		{
			return array();
		}
	}
}

