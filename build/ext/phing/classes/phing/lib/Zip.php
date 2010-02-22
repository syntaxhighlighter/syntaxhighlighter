<?php
/* vim: set ts=4 sw=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Vincent Blavet <vincent@blavet.net>                          |
// +----------------------------------------------------------------------+
//
// $Id: Zip.php 325 2007-12-20 15:44:58Z hans $

  // ----- Constants
  define( 'ARCHIVE_ZIP_READ_BLOCK_SIZE', 2048 );

  // ----- File list separator
  define( 'ARCHIVE_ZIP_SEPARATOR', ',' );

  // ----- Optional static temporary directory
  //       By default temporary files are generated in the script current
  //       path.
  //       If defined :
  //       - MUST BE terminated by a '/'.
  //       - MUST be a valid, already created directory
  //       Samples :
  // define( 'ARCHIVE_ZIP_TEMPORARY_DIR', '/temp/' );
  // define( 'ARCHIVE_ZIP_TEMPORARY_DIR', 'C:/Temp/' );
  define( 'ARCHIVE_ZIP_TEMPORARY_DIR', '' );

  // ----- Error codes
  define( 'ARCHIVE_ZIP_ERR_NO_ERROR', 0 );
  define( 'ARCHIVE_ZIP_ERR_WRITE_OPEN_FAIL', -1 );
  define( 'ARCHIVE_ZIP_ERR_READ_OPEN_FAIL', -2 );
  define( 'ARCHIVE_ZIP_ERR_INVALID_PARAMETER', -3 );
  define( 'ARCHIVE_ZIP_ERR_MISSING_FILE', -4 );
  define( 'ARCHIVE_ZIP_ERR_FILENAME_TOO_LONG', -5 );
  define( 'ARCHIVE_ZIP_ERR_INVALID_ZIP', -6 );
  define( 'ARCHIVE_ZIP_ERR_BAD_EXTRACTED_FILE', -7 );
  define( 'ARCHIVE_ZIP_ERR_DIR_CREATE_FAIL', -8 );
  define( 'ARCHIVE_ZIP_ERR_BAD_EXTENSION', -9 );
  define( 'ARCHIVE_ZIP_ERR_BAD_FORMAT', -10 );
  define( 'ARCHIVE_ZIP_ERR_DELETE_FILE_FAIL', -11 );
  define( 'ARCHIVE_ZIP_ERR_RENAME_FILE_FAIL', -12 );
  define( 'ARCHIVE_ZIP_ERR_BAD_CHECKSUM', -13 );
  define( 'ARCHIVE_ZIP_ERR_INVALID_ARCHIVE_ZIP', -14 );
  define( 'ARCHIVE_ZIP_ERR_MISSING_OPTION_VALUE', -15 );
  define( 'ARCHIVE_ZIP_ERR_INVALID_PARAM_VALUE', -16 );

  // ----- Warning codes
  define( 'ARCHIVE_ZIP_WARN_NO_WARNING', 0 );
  define( 'ARCHIVE_ZIP_WARN_FILE_EXIST', 1 );

  // ----- Methods parameters
  define( 'ARCHIVE_ZIP_PARAM_PATH', 'path' );
  define( 'ARCHIVE_ZIP_PARAM_ADD_PATH', 'add_path' );
  define( 'ARCHIVE_ZIP_PARAM_REMOVE_PATH', 'remove_path' );
  define( 'ARCHIVE_ZIP_PARAM_REMOVE_ALL_PATH', 'remove_all_path' );
  define( 'ARCHIVE_ZIP_PARAM_SET_CHMOD', 'set_chmod' );
  define( 'ARCHIVE_ZIP_PARAM_EXTRACT_AS_STRING', 'extract_as_string' );
  define( 'ARCHIVE_ZIP_PARAM_NO_COMPRESSION', 'no_compression' );
  define( 'ARCHIVE_ZIP_PARAM_BY_NAME', 'by_name' );
  define( 'ARCHIVE_ZIP_PARAM_BY_INDEX', 'by_index' );
  define( 'ARCHIVE_ZIP_PARAM_BY_EREG', 'by_ereg' );
  define( 'ARCHIVE_ZIP_PARAM_BY_PREG', 'by_preg' );

  define( 'ARCHIVE_ZIP_PARAM_PRE_EXTRACT', 'callback_pre_extract' );
  define( 'ARCHIVE_ZIP_PARAM_POST_EXTRACT', 'callback_post_extract' );
  define( 'ARCHIVE_ZIP_PARAM_PRE_ADD', 'callback_pre_add' );
  define( 'ARCHIVE_ZIP_PARAM_POST_ADD', 'callback_post_add' );



/**
* Class for manipulating zip archive files
*
* A class which provided common methods to manipulate ZIP formatted
* archive files.
* It provides creation, extraction, deletion and add features.
*
* @author   Vincent Blavet <vincent@blavet.net>
* @version  $Revision: 1.3 $
* @package  phing.lib
*/
class Archive_Zip
{
    /**
    * The filename of the zip archive.
    *
    * @var string Name of the Zip file
    */
    var $_zipname='';

    /**
    * File descriptor of the opened Zip file.
    *
    * @var int Internal zip file descriptor
    */
    var $_zip_fd=0;

    /**
    * @var int last error code
    */
    var $_error_code=1;

    /**
    * @var string Last error description
    */
    var $_error_string='';

    // {{{ constructor
    /**
    * Archive_Zip Class constructor. This flavour of the constructor only
    * declare a new Archive_Zip object, identifying it by the name of the
    * zip file.
    *
    * @param    string  $p_zipname  The name of the zip archive to create
    * @access public
    */
    function __construct($p_zipname)
    {
      if (!extension_loaded('zlib')) {
          throw new Exception("The extension 'zlib' couldn't be found.\n".
              "Please make sure your version of PHP was built ".
              "with 'zlib' support.");
      }

      // ----- Set the attributes
      $this->_zipname = $p_zipname;
      $this->_zip_fd = 0;
    }
    // }}}

    // {{{ create()
    /**
    * This method creates a Zip Archive with the filename set with
	* the constructor.
	* The files and directories indicated in $p_filelist
    * are added in the archive.
	* When a directory is in the list, the directory and its content is added
    * in the archive.
    * The methods takes a variable list of parameters in $p_params.
    * The supported parameters for this method are :
    *   'add_path' : Add a path to the archived files.
    *   'remove_path' : Remove the specified 'root' path of the archived files.
    *   'remove_all_path' : Remove all the path of the archived files.
    *   'no_compression' : The archived files will not be compressed.
    *
    * @access public
    * @param  mixed  $p_filelist  The list of the files or folders to add.
    *                             It can be a string with filenames separated
    *                             by a comma, or an array of filenames.
    * @param  mixed  $p_params  An array of variable parameters and values.
    * @return mixed An array of file description on success,
	*               an error code on error
    */
    function create($p_filelist, $p_params=0)
    {
        $this->_errorReset();

        // ----- Set default values
        if ($p_params === 0) {
    	    $p_params = array();
        }
        if ($this->_check_parameters($p_params,
	                                 array('no_compression' => false,
	                                       'add_path' => "",
	                                       'remove_path' => "",
	                                       'remove_all_path' => false)) != 1) {
		    return 0;
	    }

        // ----- Look if the $p_filelist is really an array
        $p_result_list = array();
        if (is_array($p_filelist)) {
            $v_result = $this->_create($p_filelist, $p_result_list, $p_params);
        }

        // ----- Look if the $p_filelist is a string
        else if (is_string($p_filelist)) {
            // ----- Create a list with the elements from the string
            $v_list = explode(ARCHIVE_ZIP_SEPARATOR, $p_filelist);

            $v_result = $this->_create($v_list, $p_result_list, $p_params);
        }

        // ----- Invalid variable
        else {
            $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_PARAMETER,
	                         'Invalid variable type p_filelist');
            $v_result = ARCHIVE_ZIP_ERR_INVALID_PARAMETER;
        }

        if ($v_result != 1) {
            return 0;
        }

        return $p_result_list;
    }
    // }}}

    // {{{ add()
    /**
    * This method add files or directory in an existing Zip Archive.
    * If the Zip Archive does not exist it is created.
	* The files and directories to add are indicated in $p_filelist.
	* When a directory is in the list, the directory and its content is added
    * in the archive.
    * The methods takes a variable list of parameters in $p_params.
    * The supported parameters for this method are :
    *   'add_path' : Add a path to the archived files.
    *   'remove_path' : Remove the specified 'root' path of the archived files.
    *   'remove_all_path' : Remove all the path of the archived files.
    *   'no_compression' : The archived files will not be compressed.
    *   'callback_pre_add' : A callback function that will be called before
    *                        each entry archiving.
    *   'callback_post_add' : A callback function that will be called after
    *                         each entry archiving.
    *
    * @access public
    * @param    mixed  $p_filelist  The list of the files or folders to add.
    *                               It can be a string with filenames separated
    *                               by a comma, or an array of filenames.
    * @param    mixed  $p_params  An array of variable parameters and values.
    * @return mixed An array of file description on success,
	*               0 on an unrecoverable failure, an error code is logged.
    */
    function add($p_filelist, $p_params=0)
    {
        $this->_errorReset();

        // ----- Set default values
        if ($p_params === 0) {
        	$p_params = array();
        }
        if ($this->_check_parameters($p_params,
	                                 array ('no_compression' => false,
	                                        'add_path' => '',
	                                        'remove_path' => '',
	                                        'remove_all_path' => false,
						    	     		'callback_pre_add' => '',
							    		    'callback_post_add' => '')) != 1) {
		    return 0;
	    }

        // ----- Look if the $p_filelist is really an array
        $p_result_list = array();
        if (is_array($p_filelist)) {
            // ----- Call the create fct
            $v_result = $this->_add($p_filelist, $p_result_list, $p_params);
        }

        // ----- Look if the $p_filelist is a string
        else if (is_string($p_filelist)) {
            // ----- Create a list with the elements from the string
            $v_list = explode(ARCHIVE_ZIP_SEPARATOR, $p_filelist);

            // ----- Call the create fct
            $v_result = $this->_add($v_list, $p_result_list, $p_params);
        }

        // ----- Invalid variable
        else {
            $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_PARAMETER,
	                         "add() : Invalid variable type p_filelist");
            $v_result = ARCHIVE_ZIP_ERR_INVALID_PARAMETER;
        }

        if ($v_result != 1) {
            return 0;
        }

        // ----- Return the result list
        return $p_result_list;
    }
    // }}}

    // {{{ listContent()
    /**
    * This method gives the names and properties of the files and directories
	* which are present in the zip archive.
    * The properties of each entries in the list are :
    *   filename : Name of the file.
	*              For create() or add() it's the filename given by the user.
	*              For an extract() it's the filename of the extracted file.
    *   stored_filename : Name of the file / directory stored in the archive.
    *   size : Size of the stored file.
    *   compressed_size : Size of the file's data compressed in the archive
    *                     (without the zip headers overhead)
    *   mtime : Last known modification date of the file (UNIX timestamp)
    *   comment : Comment associated with the file
    *   folder : true | false (indicates if the entry is a folder)
    *   index : index of the file in the archive (-1 when not available)
    *   status : status of the action on the entry (depending of the action) :
    *            Values are :
    *              ok : OK !
    *              filtered : the file/dir was not extracted (filtered by user)
    *              already_a_directory : the file can't be extracted because a
    *                                    directory with the same name already
	*                                    exists
    *              write_protected : the file can't be extracted because a file
    *                                with the same name already exists and is
    *                                write protected
    *              newer_exist : the file was not extracted because a newer
	*                            file already exists
    *              path_creation_fail : the file is not extracted because the
	*                                   folder does not exists and can't be
	*                                   created
    *              write_error : the file was not extracted because there was a
    *                            error while writing the file
    *              read_error : the file was not extracted because there was a
	*                           error while reading the file
    *              invalid_header : the file was not extracted because of an
	*                               archive format error (bad file header)
    * Note that each time a method can continue operating when there
    * is an error on a single file, the error is only logged in the file status.
    *
    * @access public
    * @return mixed An array of file description on success,
	*               0 on an unrecoverable failure, an error code is logged.
    */
    function listContent()
    {
        $this->_errorReset();

        // ----- Check archive
        if (!$this->_checkFormat()) {
            return(0);
        }

        $v_list = array();
        if ($this->_list($v_list) != 1) {
            unset($v_list);
            return(0);
        }

        return $v_list;
    }
    // }}}

    // {{{ extract()
    /**
    * This method extract the files and folders which are in the zip archive.
    * It can extract all the archive or a part of the archive by using filter
    * feature (extract by name, by index, by ereg, by preg). The extraction
    * can occur in the current path or an other path.
    * All the advanced features are activated by the use of variable
	* parameters.
	* The return value is an array of entry descriptions which gives
	* information on extracted files (See listContent()).
	* The method may return a success value (an array) even if some files
	* are not correctly extracted (see the file status in listContent()).
    * The supported variable parameters for this method are :
    *   'add_path' : Path where the files and directories are to be extracted
    *   'remove_path' : First part ('root' part) of the memorized path
    *                   (if similar) to remove while extracting.
    *   'remove_all_path' : Remove all the memorized path while extracting.
    *   'extract_as_string' :
    *   'set_chmod' : After the extraction of the file the indicated mode
    *                 will be set.
    *   'by_name' : It can be a string with file/dir names separated by ',',
    *               or an array of file/dir names to extract from the archive.
    *   'by_index' : A string with range of indexes separated by ',',
    *                (sample "1,3-5,12").
    *   'by_ereg' : A regular expression (ereg) that must match the extracted
    *               filename.
    *   'by_preg' : A regular expression (preg) that must match the extracted
    *               filename.
    *   'callback_pre_extract' : A callback function that will be called before
    *                            each entry extraction.
    *   'callback_post_extract' : A callback function that will be called after
    *                            each entry extraction.
    *
    * @access public
    * @param    mixed  $p_params  An array of variable parameters and values.
    * @return mixed An array of file description on success,
	*               0 on an unrecoverable failure, an error code is logged.
    */
    function extract($p_params=0)
    {

        $this->_errorReset();

        // ----- Check archive
        if (!$this->_checkFormat()) {
            return(0);
        }

        // ----- Set default values
        if ($p_params === 0) {
        	$p_params = array();
        }
        if ($this->_check_parameters($p_params,
	                                 array ('extract_as_string' => false,
	                                        'add_path' => '',
	                                        'remove_path' => '',
	                                        'remove_all_path' => false,
					    		     		'callback_pre_extract' => '',
						    			    'callback_post_extract' => '',
							    		    'set_chmod' => 0,
								    	    'by_name' => '',
									        'by_index' => '',
									        'by_ereg' => '',
									        'by_preg' => '') ) != 1) {
	    	return 0;
	    }

        // ----- Call the extracting fct
        $v_list = array();
        if ($this->_extractByRule($v_list, $p_params) != 1) {
            unset($v_list);
            return(0);
        }

        return $v_list;
    }
    // }}}


    // {{{ delete()
    /**
    * This methods delete archive entries in the zip archive.
    * Notice that at least one filtering rule (set by the variable parameter
    * list) must be set.
    * Also notice that if you delete a folder entry, only the folder entry
    * is deleted, not all the files bellonging to this folder.
    * The supported variable parameters for this method are :
    *   'by_name' : It can be a string with file/dir names separated by ',',
    *               or an array of file/dir names to delete from the archive.
    *   'by_index' : A string with range of indexes separated by ',',
    *                (sample "1,3-5,12").
    *   'by_ereg' : A regular expression (ereg) that must match the extracted
    *               filename.
    *   'by_preg' : A regular expression (preg) that must match the extracted
    *               filename.
    *
    * @access public
    * @param    mixed  $p_params  An array of variable parameters and values.
    * @return mixed An array of file description on success,
	*               0 on an unrecoverable failure, an error code is logged.
    */
    function delete($p_params)
    {
        $this->_errorReset();

        // ----- Check archive
        if (!$this->_checkFormat()) {
            return(0);
        }

        // ----- Set default values
        if ($this->_check_parameters($p_params,
	                                 array ('by_name' => '',
									        'by_index' => '',
									        'by_ereg' => '',
									        'by_preg' => '') ) != 1) {
	    	return 0;
    	}

        // ----- Check that at least one rule is set
        if (   ($p_params['by_name'] == '')
            && ($p_params['by_index'] == '')
            && ($p_params['by_ereg'] == '')
            && ($p_params['by_preg'] == '')) {
            $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_PARAMETER,
			                 'At least one filtering rule must'
							 .' be set as parameter');
            return 0;
        }

        // ----- Call the delete fct
        $v_list = array();
        if ($this->_deleteByRule($v_list, $p_params) != 1) {
            unset($v_list);
            return(0);
        }

        return $v_list;
    }
    // }}}

    // {{{ properties()
    /**
    * This method gives the global properties of the archive.
    *  The properties are :
    *    nb : Number of files in the archive
    *    comment : Comment associated with the archive file
    *    status : not_exist, ok
    *
    * @access public
    * @param    mixed  $p_params  {Description}
    * @return mixed An array with the global properties or 0 on error.
    */
    function properties()
    {
        $this->_errorReset();

        // ----- Check archive
        if (!$this->_checkFormat()) {
            return(0);
        }

        // ----- Default properties
        $v_prop = array();
        $v_prop['comment'] = '';
        $v_prop['nb'] = 0;
        $v_prop['status'] = 'not_exist';

        // ----- Look if file exists
        if (@is_file($this->_zipname)) {
            // ----- Open the zip file
            if (($this->_zip_fd = @fopen($this->_zipname, 'rb')) == 0) {
                $this->_errorLog(ARCHIVE_ZIP_ERR_READ_OPEN_FAIL,
				                 'Unable to open archive \''.$this->_zipname
								 .'\' in binary read mode');
                return 0;
            }

            // ----- Read the central directory informations
            $v_central_dir = array();
            if (($v_result = $this->_readEndCentralDir($v_central_dir)) != 1) {
                return 0;
            }

            $this->_closeFd();

            // ----- Set the user attributes
            $v_prop['comment'] = $v_central_dir['comment'];
            $v_prop['nb'] = $v_central_dir['entries'];
            $v_prop['status'] = 'ok';
        }

        return $v_prop;
    }
    // }}}


    // {{{ duplicate()
    /**
    * This method creates an archive by copying the content of an other one.
	* If the archive already exist, it is replaced by the new one without
	* any warning.
    *
    * @access public
    * @param  mixed  $p_archive  It can be a valid Archive_Zip object or
	*                            the filename of a valid zip archive.
    * @return integer 1 on success, 0 on failure.
    */
    function duplicate($p_archive)
    {
        $this->_errorReset();

        // ----- Look if the $p_archive is a Archive_Zip object
        if (   (is_object($p_archive))
		    && (strtolower(get_class($p_archive)) == 'archive_zip')) {
            $v_result = $this->_duplicate($p_archive->_zipname);
        }

        // ----- Look if the $p_archive is a string (so a filename)
        else if (is_string($p_archive)) {
            // ----- Check that $p_archive is a valid zip file
            // TBC : Should also check the archive format
            if (!is_file($p_archive)) {
                $this->_errorLog(ARCHIVE_ZIP_ERR_MISSING_FILE,
				                 "No file with filename '".$p_archive."'");
                $v_result = ARCHIVE_ZIP_ERR_MISSING_FILE;
            }
            else {
                $v_result = $this->_duplicate($p_archive);
            }
        }

        // ----- Invalid variable
        else {
            $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_PARAMETER,
			                 "Invalid variable type p_archive_to_add");
            $v_result = ARCHIVE_ZIP_ERR_INVALID_PARAMETER;
        }

        return $v_result;
    }
    // }}}

    // {{{ merge()
    /**
    *  This method merge a valid zip archive at the end of the
	*  archive identified by the Archive_Zip object.
    *  If the archive ($this) does not exist, the merge becomes a duplicate.
    *  If the archive to add does not exist, the merge is a success.
    *
    * @access public
    * @param mixed $p_archive_to_add  It can be a valid Archive_Zip object or
	*                                 the filename of a valid zip archive.
    * @return integer 1 on success, 0 on failure.
    */
    function merge($p_archive_to_add)
    {
        $v_result = 1;
        $this->_errorReset();

        // ----- Check archive
        if (!$this->_checkFormat()) {
            return(0);
        }

        // ----- Look if the $p_archive_to_add is a Archive_Zip object
        if (   (is_object($p_archive_to_add))
		    && (strtolower(get_class($p_archive_to_add)) == 'archive_zip')) {
            $v_result = $this->_merge($p_archive_to_add);
        }

        // ----- Look if the $p_archive_to_add is a string (so a filename)
        else if (is_string($p_archive_to_add)) {
            // ----- Create a temporary archive
            $v_object_archive = new Archive_Zip($p_archive_to_add);

            // ----- Merge the archive
            $v_result = $this->_merge($v_object_archive);
        }

        // ----- Invalid variable
        else {
            $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_PARAMETER,
			                 "Invalid variable type p_archive_to_add");
            $v_result = ARCHIVE_ZIP_ERR_INVALID_PARAMETER;
        }

        return $v_result;
    }
    // }}}

    // {{{ errorCode()
    /**
    * Method that gives the lastest error code.
    *
    * @access public
    * @return integer The error code value.
    */
    function errorCode()
    {
        return($this->_error_code);
    }
    // }}}

    // {{{ errorName()
    /**
    * This method gives the latest error code name.
    *
    * @access public
    * @param  boolean $p_with_code  If true, gives the name and the int value.
    * @return string The error name.
    */
    function errorName($p_with_code=false)
    {
        $v_const_list = get_defined_constants();
  	
      	// ----- Extract error constants from all const.
        for (reset($v_const_list);
		     list($v_key, $v_value) = each($v_const_list);) {
     	    if (substr($v_key, 0, strlen('ARCHIVE_ZIP_ERR_'))
			    =='ARCHIVE_ZIP_ERR_') {
    		    $v_error_list[$v_key] = $v_value;
    	    }
        }
    
        // ----- Search the name form the code value
        $v_key=array_search($this->_error_code, $v_error_list, true);
  	    if ($v_key!=false) {
            $v_value = $v_key;
  	    }
  	    else {
            $v_value = 'NoName';
  	    }
  	
        if ($p_with_code) {
            return($v_value.' ('.$this->_error_code.')');
        }
        else {
          return($v_value);
        }
    }
    // }}}

    // {{{ errorInfo()
    /**
    * This method returns the description associated with the latest error.
    *
    * @access public
    * @param  boolean $p_full If set to true gives the description with the
    *                         error code, the name and the description.
    *                         If set to false gives only the description
    *                         and the error code.
    * @return string The error description.
    */
    function errorInfo($p_full=false)
    {
        if ($p_full) {
            return($this->errorName(true)." : ".$this->_error_string);
        }
        else {
            return($this->_error_string." [code ".$this->_error_code."]");
        }
    }
    // }}}


// -----------------------------------------------------------------------------
// ***** UNDER THIS LINE ARE DEFINED PRIVATE INTERNAL FUNCTIONS *****
// *****                                                        *****
// *****       THESES FUNCTIONS MUST NOT BE USED DIRECTLY       *****
// -----------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : _checkFormat()
  // Description :
  //   This method check that the archive exists and is a valid zip archive.
  //   Several level of check exists. (futur)
  // Parameters :
  //   $p_level : Level of check. Default 0.
  //              0 : Check the first bytes (magic codes) (default value))
  //              1 : 0 + Check the central directory (futur)
  //              2 : 1 + Check each file header (futur)
  // Return Values :
  //   true on success,
  //   false on error, the error code is set.
  // ---------------------------------------------------------------------------
  /**
  * Archive_Zip::_checkFormat()
  *
  * { Description }
  *
  * @param integer $p_level
  */
  function _checkFormat($p_level=0)
  {
    $v_result = true;

    // ----- Reset the error handler
    $this->_errorReset();

    // ----- Look if the file exits
    if (!is_file($this->_zipname)) {
      // ----- Error log
      $this->_errorLog(ARCHIVE_ZIP_ERR_MISSING_FILE,
	                   "Missing archive file '".$this->_zipname."'");
      return(false);
    }

    // ----- Check that the file is readeable
    if (!is_readable($this->_zipname)) {
      // ----- Error log
      $this->_errorLog(ARCHIVE_ZIP_ERR_READ_OPEN_FAIL,
	                   "Unable to read archive '".$this->_zipname."'");
      return(false);
    }

    // ----- Check the magic code
    // TBC

    // ----- Check the central header
    // TBC

    // ----- Check each file header
    // TBC

    // ----- Return
    return $v_result;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : _create()
  // Description :
  // Parameters :
  // Return Values :
  // ---------------------------------------------------------------------------
  /**
  * Archive_Zip::_create()
  *
  * { Description }
  *
  */
  function _create($p_list, &$p_result_list, &$p_params)
  {
    $v_result=1;
    $v_list_detail = array();

	$p_add_dir = $p_params['add_path'];
	$p_remove_dir = $p_params['remove_path'];
	$p_remove_all_dir = $p_params['remove_all_path'];

    // ----- Open the file in write mode
    if (($v_result = $this->_openFd('wb')) != 1)
    {
      // ----- Return
      return $v_result;
    }

    // ----- Add the list of files
    $v_result = $this->_addList($p_list, $p_result_list, $p_add_dir, $p_remove_dir, $p_remove_all_dir, $p_params);

    // ----- Close
    $this->_closeFd();

    // ----- Return
    return $v_result;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : _add()
  // Description :
  // Parameters :
  // Return Values :
  // ---------------------------------------------------------------------------
  /**
  * Archive_Zip::_add()
  *
  * { Description }
  *
  */
  function _add($p_list, &$p_result_list, &$p_params)
  {
    $v_result=1;
    $v_list_detail = array();

	$p_add_dir = $p_params['add_path'];
	$p_remove_dir = $p_params['remove_path'];
	$p_remove_all_dir = $p_params['remove_all_path'];

    // ----- Look if the archive exists or is empty and need to be created
    if ((!is_file($this->_zipname)) || (filesize($this->_zipname) == 0)) {
      $v_result = $this->_create($p_list, $p_result_list, $p_params);
      return $v_result;
    }

    // ----- Open the zip file
    if (($v_result=$this->_openFd('rb')) != 1) {
      return $v_result;
    }

    // ----- Read the central directory informations
    $v_central_dir = array();
    if (($v_result = $this->_readEndCentralDir($v_central_dir)) != 1)
    {
      $this->_closeFd();
      return $v_result;
    }

    // ----- Go to beginning of File
    @rewind($this->_zip_fd);

    // ----- Creates a temporay file
    $v_zip_temp_name = ARCHIVE_ZIP_TEMPORARY_DIR.uniqid('archive_zip-').'.tmp';

    // ----- Open the temporary file in write mode
    if (($v_zip_temp_fd = @fopen($v_zip_temp_name, 'wb')) == 0)
    {
      $this->_closeFd();

      $this->_errorLog(ARCHIVE_ZIP_ERR_READ_OPEN_FAIL,
	                   'Unable to open temporary file \''
					   .$v_zip_temp_name.'\' in binary write mode');
      return Archive_Zip::errorCode();
    }

    // ----- Copy the files from the archive to the temporary file
    // TBC : Here I should better append the file and go back to erase the
	// central dir
    $v_size = $v_central_dir['offset'];
    while ($v_size != 0)
    {
      $v_read_size = ($v_size < ARCHIVE_ZIP_READ_BLOCK_SIZE
	                  ? $v_size : ARCHIVE_ZIP_READ_BLOCK_SIZE);
      $v_buffer = fread($this->_zip_fd, $v_read_size);
      @fwrite($v_zip_temp_fd, $v_buffer, $v_read_size);
      $v_size -= $v_read_size;
    }

    // ----- Swap the file descriptor
    // Here is a trick : I swap the temporary fd with the zip fd, in order to 
    // use the following methods on the temporary fil and not the real archive
    $v_swap = $this->_zip_fd;
    $this->_zip_fd = $v_zip_temp_fd;
    $v_zip_temp_fd = $v_swap;

    // ----- Add the files
    $v_header_list = array();
    if (($v_result = $this->_addFileList($p_list, $v_header_list,
	                                     $p_add_dir, $p_remove_dir,
										 $p_remove_all_dir, $p_params)) != 1)
    {
      fclose($v_zip_temp_fd);
      $this->_closeFd();
      @unlink($v_zip_temp_name);

      // ----- Return
      return $v_result;
    }

    // ----- Store the offset of the central dir
    $v_offset = @ftell($this->_zip_fd);

    // ----- Copy the block of file headers from the old archive
    $v_size = $v_central_dir['size'];
    while ($v_size != 0)
    {
      $v_read_size = ($v_size < ARCHIVE_ZIP_READ_BLOCK_SIZE
	                  ? $v_size : ARCHIVE_ZIP_READ_BLOCK_SIZE);
      $v_buffer = @fread($v_zip_temp_fd, $v_read_size);
      @fwrite($this->_zip_fd, $v_buffer, $v_read_size);
      $v_size -= $v_read_size;
    }

    // ----- Create the Central Dir files header
    for ($i=0, $v_count=0; $i<sizeof($v_header_list); $i++)
    {
      // ----- Create the file header
      if ($v_header_list[$i]['status'] == 'ok') {
        if (($v_result=$this->_writeCentralFileHeader($v_header_list[$i]))!=1) {
          fclose($v_zip_temp_fd);
          $this->_closeFd();
          @unlink($v_zip_temp_name);

          // ----- Return
          return $v_result;
        }
        $v_count++;
      }

      // ----- Transform the header to a 'usable' info
      $this->_convertHeader2FileInfo($v_header_list[$i], $p_result_list[$i]);
    }

    // ----- Zip file comment
    $v_comment = '';

    // ----- Calculate the size of the central header
    $v_size = @ftell($this->_zip_fd)-$v_offset;

    // ----- Create the central dir footer
    if (($v_result = $this->_writeCentralHeader($v_count
	                                              +$v_central_dir['entries'],
	                                            $v_size, $v_offset,
												$v_comment)) != 1) {
      // ----- Reset the file list
      unset($v_header_list);

      // ----- Return
      return $v_result;
    }

    // ----- Swap back the file descriptor
    $v_swap = $this->_zip_fd;
    $this->_zip_fd = $v_zip_temp_fd;
    $v_zip_temp_fd = $v_swap;

    // ----- Close
    $this->_closeFd();

    // ----- Close the temporary file
    @fclose($v_zip_temp_fd);

    // ----- Delete the zip file
    // TBC : I should test the result ...
    @unlink($this->_zipname);

    // ----- Rename the temporary file
    // TBC : I should test the result ...
    //@rename($v_zip_temp_name, $this->_zipname);
    $this->_tool_Rename($v_zip_temp_name, $this->_zipname);

    // ----- Return
    return $v_result;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : _openFd()
  // Description :
  // Parameters :
  // ---------------------------------------------------------------------------
  /**
  * Archive_Zip::_openFd()
  *
  * { Description }
  *
  */
  function _openFd($p_mode)
  {
    $v_result=1;

    // ----- Look if already open
    if ($this->_zip_fd != 0)
    {
      $this->_errorLog(ARCHIVE_ZIP_ERR_READ_OPEN_FAIL,
	                   'Zip file \''.$this->_zipname.'\' already open');
      return Archive_Zip::errorCode();
    }

    // ----- Open the zip file
    if (($this->_zip_fd = @fopen($this->_zipname, $p_mode)) == 0)
    {
      $this->_errorLog(ARCHIVE_ZIP_ERR_READ_OPEN_FAIL,
	                   'Unable to open archive \''.$this->_zipname
					   .'\' in '.$p_mode.' mode');
      return Archive_Zip::errorCode();
    }

    // ----- Return
    return $v_result;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : _closeFd()
  // Description :
  // Parameters :
  // ---------------------------------------------------------------------------
  /**
  * Archive_Zip::_closeFd()
  *
  * { Description }
  *
  */
  function _closeFd()
  {
    $v_result=1;

    if ($this->_zip_fd != 0)
      @fclose($this->_zip_fd);
    $this->_zip_fd = 0;

    // ----- Return
    return $v_result;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : _addList()
  // Description :
  //   $p_add_dir and $p_remove_dir will give the ability to memorize a path which is
  //   different from the real path of the file. This is usefull if you want to have PclTar
  //   running in any directory, and memorize relative path from an other directory.
  // Parameters :
  //   $p_list : An array containing the file or directory names to add in the tar
  //   $p_result_list : list of added files with their properties (specially the status field)
  //   $p_add_dir : Path to add in the filename path archived
  //   $p_remove_dir : Path to remove in the filename path archived
  // Return Values :
  // ---------------------------------------------------------------------------
  /**
  * Archive_Zip::_addList()
  *
  * { Description }
  *
  */
  function _addList($p_list, &$p_result_list,
                    $p_add_dir, $p_remove_dir, $p_remove_all_dir, &$p_params)
  {
    $v_result=1;

    // ----- Add the files
    $v_header_list = array();
    if (($v_result = $this->_addFileList($p_list, $v_header_list,
	                                     $p_add_dir, $p_remove_dir,
										 $p_remove_all_dir, $p_params)) != 1) {
      return $v_result;
    }

    // ----- Store the offset of the central dir
    $v_offset = @ftell($this->_zip_fd);

    // ----- Create the Central Dir files header
    for ($i=0,$v_count=0; $i<sizeof($v_header_list); $i++)
    {
      // ----- Create the file header
      if ($v_header_list[$i]['status'] == 'ok') {
        if (($v_result = $this->_writeCentralFileHeader($v_header_list[$i])) != 1) {
          return $v_result;
        }
        $v_count++;
      }

      // ----- Transform the header to a 'usable' info
      $this->_convertHeader2FileInfo($v_header_list[$i], $p_result_list[$i]);
    }

    // ----- Zip file comment
    $v_comment = '';

    // ----- Calculate the size of the central header
    $v_size = @ftell($this->_zip_fd)-$v_offset;

    // ----- Create the central dir footer
    if (($v_result = $this->_writeCentralHeader($v_count, $v_size, $v_offset,
	                                            $v_comment)) != 1)
    {
      // ----- Reset the file list
      unset($v_header_list);

      // ----- Return
      return $v_result;
    }

    // ----- Return
    return $v_result;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : _addFileList()
  // Description :
  //   $p_add_dir and $p_remove_dir will give the ability to memorize a path which is
  //   different from the real path of the file. This is usefull if you want to
  //   run the lib in any directory, and memorize relative path from an other directory.
  // Parameters :
  //   $p_list : An array containing the file or directory names to add in the tar
  //   $p_result_list : list of added files with their properties (specially the status field)
  //   $p_add_dir : Path to add in the filename path archived
  //   $p_remove_dir : Path to remove in the filename path archived
  // Return Values :
  // ---------------------------------------------------------------------------
  /**
  * Archive_Zip::_addFileList()
  *
  * { Description }
  *
  */
  function _addFileList($p_list, &$p_result_list,
                        $p_add_dir, $p_remove_dir, $p_remove_all_dir,
						&$p_params)
  {
    $v_result=1;
    $v_header = array();

    // ----- Recuperate the current number of elt in list
    $v_nb = sizeof($p_result_list);

    // ----- Loop on the files
    for ($j=0; ($j<count($p_list)) && ($v_result==1); $j++)
    {
      // ----- Recuperate the filename
      $p_filename = $this->_tool_TranslateWinPath($p_list[$j], false);

      // ----- Skip empty file names
      if ($p_filename == "")
      {
        continue;
      }

      // ----- Check the filename
      if (!file_exists($p_filename))
      {
        $this->_errorLog(ARCHIVE_ZIP_ERR_MISSING_FILE,
		                 "File '$p_filename' does not exists");
        return Archive_Zip::errorCode();
      }

      // ----- Look if it is a file or a dir with no all pathnre move
      if ((is_file($p_filename)) || ((is_dir($p_filename)) && !$p_remove_all_dir)) {
        // ----- Add the file
        if (($v_result = $this->_addFile($p_filename, $v_header, $p_add_dir, $p_remove_dir, $p_remove_all_dir, $p_params)) != 1)
        {
          // ----- Return status
          return $v_result;
        }

        // ----- Store the file infos
        $p_result_list[$v_nb++] = $v_header;
      }

      // ----- Look for directory
      if (is_dir($p_filename))
      {

        // ----- Look for path
        if ($p_filename != ".")
          $v_path = $p_filename."/";
        else
          $v_path = "";

        // ----- Read the directory for files and sub-directories
        $p_hdir = opendir($p_filename);
        $p_hitem = readdir($p_hdir); // '.' directory
        $p_hitem = readdir($p_hdir); // '..' directory
        while ($p_hitem = readdir($p_hdir))
        {

          // ----- Look for a file
          if (is_file($v_path.$p_hitem))
          {

            // ----- Add the file
            if (($v_result = $this->_addFile($v_path.$p_hitem, $v_header, $p_add_dir, $p_remove_dir, $p_remove_all_dir, $p_params)) != 1)
            {
              // ----- Return status
              return $v_result;
            }

            // ----- Store the file infos
            $p_result_list[$v_nb++] = $v_header;
          }

          // ----- Recursive call to _addFileList()
          else
          {

            // ----- Need an array as parameter
            $p_temp_list[0] = $v_path.$p_hitem;
            $v_result = $this->_addFileList($p_temp_list, $p_result_list, $p_add_dir, $p_remove_dir, $p_remove_all_dir, $p_params);

            // ----- Update the number of elements of the list
            $v_nb = sizeof($p_result_list);
          }
        }

        // ----- Free memory for the recursive loop
        unset($p_temp_list);
        unset($p_hdir);
        unset($p_hitem);
      }
    }

    return $v_result;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : _addFile()
  // Description :
  // Parameters :
  // Return Values :
  // ---------------------------------------------------------------------------
  /**
  * Archive_Zip::_addFile()
  *
  * { Description }
  *
  */
  function _addFile($p_filename, &$p_header, $p_add_dir, $p_remove_dir, $p_remove_all_dir, &$p_params)
  {
    $v_result=1;

    if ($p_filename == "")
    {
      // ----- Error log
      $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_PARAMETER, "Invalid file list parameter (invalid or empty list)");

      // ----- Return
      return Archive_Zip::errorCode();
    }

    // ----- Calculate the stored filename
    $v_stored_filename = $p_filename;

    // ----- Look for all path to remove
    if ($p_remove_all_dir) {
      $v_stored_filename = basename($p_filename);
    }
    // ----- Look for partial path remove
    else if ($p_remove_dir != "")
    {
      $p_remove_dir = $this->_tool_TranslateWinPath($p_remove_dir, false);
    	
      if (substr($p_remove_dir, -1) != '/')
        $p_remove_dir .= "/";

      if ((substr($p_filename, 0, 2) == "./") || (substr($p_remove_dir, 0, 2) == "./"))
      {
        if ((substr($p_filename, 0, 2) == "./") && (substr($p_remove_dir, 0, 2) != "./"))
          $p_remove_dir = "./".$p_remove_dir;
        if ((substr($p_filename, 0, 2) != "./") && (substr($p_remove_dir, 0, 2) == "./"))
          $p_remove_dir = substr($p_remove_dir, 2);
      }

      $v_compare = $this->_tool_PathInclusion($p_remove_dir, $p_filename);
      if ($v_compare > 0)
//      if (substr($p_filename, 0, strlen($p_remove_dir)) == $p_remove_dir)
      {

        if ($v_compare == 2) {
          $v_stored_filename = "";
        }
        else {
          $v_stored_filename = substr($p_filename, strlen($p_remove_dir));
        }
      }
    }
    // ----- Look for path to add
    if ($p_add_dir != "")
    {
      if (substr($p_add_dir, -1) == "/")
        $v_stored_filename = $p_add_dir.$v_stored_filename;
      else
        $v_stored_filename = $p_add_dir."/".$v_stored_filename;
    }

    // ----- Filename (reduce the path of stored name)
    $v_stored_filename = $this->_tool_PathReduction($v_stored_filename);


    /* filename length moved after call-back in release 1.3
    // ----- Check the path length
    if (strlen($v_stored_filename) > 0xFF)
    {
      // ----- Error log
      $this->_errorLog(-5, "Stored file name is too long (max. 255) : '$v_stored_filename'");

      // ----- Return
      return Archive_Zip::errorCode();
    }
    */

    // ----- Set the file properties
    clearstatcache();
    $p_header['version'] = 20;
    $p_header['version_extracted'] = 10;
    $p_header['flag'] = 0;
    $p_header['compression'] = 0;
    $p_header['mtime'] = filemtime($p_filename);
    $p_header['crc'] = 0;
    $p_header['compressed_size'] = 0;
    $p_header['size'] = filesize($p_filename);
    $p_header['filename_len'] = strlen($p_filename);
    $p_header['extra_len'] = 0;
    $p_header['comment_len'] = 0;
    $p_header['disk'] = 0;
    $p_header['internal'] = 0;
    $p_header['external'] = (is_file($p_filename)?0xFE49FFE0:0x41FF0010);
    $p_header['offset'] = 0;
    $p_header['filename'] = $p_filename;
    $p_header['stored_filename'] = $v_stored_filename;
    $p_header['extra'] = '';
    $p_header['comment'] = '';
    $p_header['status'] = 'ok';
    $p_header['index'] = -1;

    // ----- Look for pre-add callback
    if (   (isset($p_params[ARCHIVE_ZIP_PARAM_PRE_ADD]))
	    && ($p_params[ARCHIVE_ZIP_PARAM_PRE_ADD] != '')) {

      // ----- Generate a local information
      $v_local_header = array();
      $this->_convertHeader2FileInfo($p_header, $v_local_header);

      // ----- Call the callback
      // Here I do not use call_user_func() because I need to send a reference to the
      // header.
      eval('$v_result = '.$p_params[ARCHIVE_ZIP_PARAM_PRE_ADD].'(ARCHIVE_ZIP_PARAM_PRE_ADD, $v_local_header);');
      if ($v_result == 0) {
        // ----- Change the file status
        $p_header['status'] = "skipped";
        $v_result = 1;
      }

      // ----- Update the informations
      // Only some fields can be modified
      if ($p_header['stored_filename'] != $v_local_header['stored_filename']) {
        $p_header['stored_filename'] = $this->_tool_PathReduction($v_local_header['stored_filename']);
      }
    }

    // ----- Look for empty stored filename
    if ($p_header['stored_filename'] == "") {
      $p_header['status'] = "filtered";
    }

    // ----- Check the path length
    if (strlen($p_header['stored_filename']) > 0xFF) {
      $p_header['status'] = 'filename_too_long';
    }

    // ----- Look if no error, or file not skipped
    if ($p_header['status'] == 'ok') {

      // ----- Look for a file
      if (is_file($p_filename))
      {
        // ----- Open the source file
        if (($v_file = @fopen($p_filename, "rb")) == 0) {
          $this->_errorLog(ARCHIVE_ZIP_ERR_READ_OPEN_FAIL, "Unable to open file '$p_filename' in binary read mode");
          return Archive_Zip::errorCode();
        }
        
        if ($p_params['no_compression']) {
          // ----- Read the file content
          $v_content_compressed = @fread($v_file, $p_header['size']);

          // ----- Calculate the CRC
          $p_header['crc'] = crc32($v_content_compressed);
        }
        else {
          // ----- Read the file content
          $v_content = @fread($v_file, $p_header['size']);

          // ----- Calculate the CRC
          $p_header['crc'] = crc32($v_content);

          // ----- Compress the file
          $v_content_compressed = gzdeflate($v_content);
        }

        // ----- Set header parameters
        $p_header['compressed_size'] = strlen($v_content_compressed);
        $p_header['compression'] = 8;

        // ----- Call the header generation
        if (($v_result = $this->_writeFileHeader($p_header)) != 1) {
          @fclose($v_file);
          return $v_result;
        }

        // ----- Write the compressed content
        $v_binary_data = pack('a'.$p_header['compressed_size'], $v_content_compressed);
        @fwrite($this->_zip_fd, $v_binary_data, $p_header['compressed_size']);

        // ----- Close the file
        @fclose($v_file);
      }

      // ----- Look for a directory
      else
      {
        // ----- Set the file properties
        $p_header['filename'] .= '/';
        $p_header['filename_len']++;
        $p_header['size'] = 0;
        $p_header['external'] = 0x41FF0010;   // Value for a folder : to be checked

        // ----- Call the header generation
        if (($v_result = $this->_writeFileHeader($p_header)) != 1)
        {
          return $v_result;
        }
      }
    }

    // ----- Look for pre-add callback
    if (   (isset($p_params[ARCHIVE_ZIP_PARAM_POST_ADD]))
	    && ($p_params[ARCHIVE_ZIP_PARAM_POST_ADD] != '')) {

      // ----- Generate a local information
      $v_local_header = array();
      $this->_convertHeader2FileInfo($p_header, $v_local_header);

      // ----- Call the callback
      // Here I do not use call_user_func() because I need to send a reference to the
      // header.
      eval('$v_result = '.$p_params[ARCHIVE_ZIP_PARAM_POST_ADD].'(ARCHIVE_ZIP_PARAM_POST_ADD, $v_local_header);');
      if ($v_result == 0) {
        // ----- Ignored
        $v_result = 1;
      }

      // ----- Update the informations
      // Nothing can be modified
    }

    // ----- Return
    return $v_result;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : _writeFileHeader()
  // Description :
  // Parameters :
  // Return Values :
  // ---------------------------------------------------------------------------
  /**
  * Archive_Zip::_writeFileHeader()
  *
  * { Description }
  *
  */
  function _writeFileHeader(&$p_header)
  {
    $v_result=1;

    // TBC
    //for(reset($p_header); $key = key($p_header); next($p_header)) {
    //}

    // ----- Store the offset position of the file
    $p_header['offset'] = ftell($this->_zip_fd);

    // ----- Transform UNIX mtime to DOS format mdate/mtime
    $v_date = getdate($p_header['mtime']);
    $v_mtime = ($v_date['hours']<<11) + ($v_date['minutes']<<5) + $v_date['seconds']/2;
    $v_mdate = (($v_date['year']-1980)<<9) + ($v_date['mon']<<5) + $v_date['mday'];

    // ----- Packed data
    $v_binary_data = pack("VvvvvvVVVvv", 0x04034b50, $p_header['version'], $p_header['flag'],
                          $p_header['compression'], $v_mtime, $v_mdate,
                          $p_header['crc'], $p_header['compressed_size'], $p_header['size'],
                          strlen($p_header['stored_filename']), $p_header['extra_len']);

    // ----- Write the first 148 bytes of the header in the archive
    fputs($this->_zip_fd, $v_binary_data, 30);

    // ----- Write the variable fields
    if (strlen($p_header['stored_filename']) != 0)
    {
      fputs($this->_zip_fd, $p_header['stored_filename'], strlen($p_header['stored_filename']));
    }
    if ($p_header['extra_len'] != 0)
    {
      fputs($this->_zip_fd, $p_header['extra'], $p_header['extra_len']);
    }

    // ----- Return
    return $v_result;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : _writeCentralFileHeader()
  // Description :
  // Parameters :
  // Return Values :
  // ---------------------------------------------------------------------------
  /**
  * Archive_Zip::_writeCentralFileHeader()
  *
  * { Description }
  *
  */
  function _writeCentralFileHeader(&$p_header)
  {
    $v_result=1;

    // TBC
    //for(reset($p_header); $key = key($p_header); next($p_header)) {
    //}

    // ----- Transform UNIX mtime to DOS format mdate/mtime
    $v_date = getdate($p_header['mtime']);
    $v_mtime = ($v_date['hours']<<11) + ($v_date['minutes']<<5) + $v_date['seconds']/2;
    $v_mdate = (($v_date['year']-1980)<<9) + ($v_date['mon']<<5) + $v_date['mday'];

    // ----- Packed data
    $v_binary_data = pack("VvvvvvvVVVvvvvvVV", 0x02014b50, $p_header['version'], $p_header['version_extracted'],
                          $p_header['flag'], $p_header['compression'], $v_mtime, $v_mdate, $p_header['crc'],
                          $p_header['compressed_size'], $p_header['size'],
                          strlen($p_header['stored_filename']), $p_header['extra_len'], $p_header['comment_len'],
                          $p_header['disk'], $p_header['internal'], $p_header['external'], $p_header['offset']);

    // ----- Write the 42 bytes of the header in the zip file
    fputs($this->_zip_fd, $v_binary_data, 46);

    // ----- Write the variable fields
    if (strlen($p_header['stored_filename']) != 0)
    {
      fputs($this->_zip_fd, $p_header['stored_filename'], strlen($p_header['stored_filename']));
    }
    if ($p_header['extra_len'] != 0)
    {
      fputs($this->_zip_fd, $p_header['extra'], $p_header['extra_len']);
    }
    if ($p_header['comment_len'] != 0)
    {
      fputs($this->_zip_fd, $p_header['comment'], $p_header['comment_len']);
    }

    // ----- Return
    return $v_result;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : _writeCentralHeader()
  // Description :
  // Parameters :
  // Return Values :
  // ---------------------------------------------------------------------------
  /**
  * Archive_Zip::_writeCentralHeader()
  *
  * { Description }
  *
  */
  function _writeCentralHeader($p_nb_entries, $p_size, $p_offset, $p_comment)
  {
    $v_result=1;

    // ----- Packed data
    $v_binary_data = pack("VvvvvVVv", 0x06054b50, 0, 0, $p_nb_entries, $p_nb_entries, $p_size, $p_offset, strlen($p_comment));

    // ----- Write the 22 bytes of the header in the zip file
    fputs($this->_zip_fd, $v_binary_data, 22);

    // ----- Write the variable fields
    if (strlen($p_comment) != 0)
    {
      fputs($this->_zip_fd, $p_comment, strlen($p_comment));
    }

    // ----- Return
    return $v_result;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : _list()
  // Description :
  // Parameters :
  // Return Values :
  // ---------------------------------------------------------------------------
  /**
  * Archive_Zip::_list()
  *
  * { Description }
  *
  */
  function _list(&$p_list)
  {
    $v_result=1;

    // ----- Open the zip file
    if (($this->_zip_fd = @fopen($this->_zipname, 'rb')) == 0)
    {
      // ----- Error log
      $this->_errorLog(ARCHIVE_ZIP_ERR_READ_OPEN_FAIL, 'Unable to open archive \''.$this->_zipname.'\' in binary read mode');

      // ----- Return
      return Archive_Zip::errorCode();
    }

    // ----- Read the central directory informations
    $v_central_dir = array();
    if (($v_result = $this->_readEndCentralDir($v_central_dir)) != 1)
    {
      return $v_result;
    }

    // ----- Go to beginning of Central Dir
    @rewind($this->_zip_fd);
    if (@fseek($this->_zip_fd, $v_central_dir['offset']))
    {
      // ----- Error log
      $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_ARCHIVE_ZIP, 'Invalid archive size');

      // ----- Return
      return Archive_Zip::errorCode();
    }

    // ----- Read each entry
    for ($i=0; $i<$v_central_dir['entries']; $i++)
    {
      // ----- Read the file header
      if (($v_result = $this->_readCentralFileHeader($v_header)) != 1)
      {
        return $v_result;
      }
      $v_header['index'] = $i;

      // ----- Get the only interesting attributes
      $this->_convertHeader2FileInfo($v_header, $p_list[$i]);
      unset($v_header);
    }

    // ----- Close the zip file
    $this->_closeFd();

    // ----- Return
    return $v_result;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : _convertHeader2FileInfo()
  // Description :
  //   This function takes the file informations from the central directory
  //   entries and extract the interesting parameters that will be given back.
  //   The resulting file infos are set in the array $p_info
  //     $p_info['filename'] : Filename with full path. Given by user (add),
  //                           extracted in the filesystem (extract).
  //     $p_info['stored_filename'] : Stored filename in the archive.
  //     $p_info['size'] = Size of the file.
  //     $p_info['compressed_size'] = Compressed size of the file.
  //     $p_info['mtime'] = Last modification date of the file.
  //     $p_info['comment'] = Comment associated with the file.
  //     $p_info['folder'] = true/false : indicates if the entry is a folder or not.
  //     $p_info['status'] = status of the action on the file.
  // Parameters :
  // Return Values :
  // ---------------------------------------------------------------------------
  /**
  * Archive_Zip::_convertHeader2FileInfo()
  *
  * { Description }
  *
  */
  function _convertHeader2FileInfo($p_header, &$p_info)
  {
    $v_result=1;

    // ----- Get the interesting attributes
    $p_info['filename'] = $p_header['filename'];
    $p_info['stored_filename'] = $p_header['stored_filename'];
    $p_info['size'] = $p_header['size'];
    $p_info['compressed_size'] = $p_header['compressed_size'];
    $p_info['mtime'] = $p_header['mtime'];
    $p_info['comment'] = $p_header['comment'];
    $p_info['folder'] = (($p_header['external']&0x00000010)==0x00000010);
    $p_info['index'] = $p_header['index'];
    $p_info['status'] = $p_header['status'];

    // ----- Return
    return $v_result;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : _extractByRule()
  // Description :
  //   Extract a file or directory depending of rules (by index, by name, ...)
  // Parameters :
  //   $p_file_list : An array where will be placed the properties of each
  //                  extracted file
  //   $p_path : Path to add while writing the extracted files
  //   $p_remove_path : Path to remove (from the file memorized path) while writing the
  //                    extracted files. If the path does not match the file path,
  //                    the file is extracted with its memorized path.
  //                    $p_remove_path does not apply to 'list' mode.
  //                    $p_path and $p_remove_path are commulative.
  // Return Values :
  //   1 on success,0 or less on error (see error code list)
  // ---------------------------------------------------------------------------
  /**
  * Archive_Zip::_extractByRule()
  *
  * { Description }
  *
  */
  function _extractByRule(&$p_file_list, &$p_params)
  {
    $v_result=1;

	$p_path = $p_params['add_path'];
	$p_remove_path = $p_params['remove_path'];
	$p_remove_all_path = $p_params['remove_all_path'];

    // ----- Check the path
    if (($p_path == "")
	    || ((substr($p_path, 0, 1) != "/")
	    && (substr($p_path, 0, 3) != "../") && (substr($p_path,1,2)!=":/")))
      $p_path = "./".$p_path;

    // ----- Reduce the path last (and duplicated) '/'
    if (($p_path != "./") && ($p_path != "/")) {
      // ----- Look for the path end '/'
      while (substr($p_path, -1) == "/") {
        $p_path = substr($p_path, 0, strlen($p_path)-1);
      }
    }

    // ----- Look for path to remove format (should end by /)
    if (($p_remove_path != "") && (substr($p_remove_path, -1) != '/')) {
      $p_remove_path .= '/';
    }
    $p_remove_path_size = strlen($p_remove_path);

    // ----- Open the zip file
    if (($v_result = $this->_openFd('rb')) != 1)
    {
      return $v_result;
    }

    // ----- Read the central directory informations
    $v_central_dir = array();
    if (($v_result = $this->_readEndCentralDir($v_central_dir)) != 1)
    {
      // ----- Close the zip file
      $this->_closeFd();

      return $v_result;
    }

    // ----- Start at beginning of Central Dir
    $v_pos_entry = $v_central_dir['offset'];

    // ----- Read each entry
    $j_start = 0;
    for ($i=0, $v_nb_extracted=0; $i<$v_central_dir['entries']; $i++) {
      // ----- Read next Central dir entry
      @rewind($this->_zip_fd);
      if (@fseek($this->_zip_fd, $v_pos_entry)) {
        $this->_closeFd();

        $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_ARCHIVE_ZIP,
		                 'Invalid archive size');

        return Archive_Zip::errorCode();
      }

      // ----- Read the file header
      $v_header = array();
      if (($v_result = $this->_readCentralFileHeader($v_header)) != 1) {
        $this->_closeFd();

        return $v_result;
      }

      // ----- Store the index
      $v_header['index'] = $i;

      // ----- Store the file position
      $v_pos_entry = ftell($this->_zip_fd);

      // ----- Look for the specific extract rules
      $v_extract = false;

      // ----- Look for extract by name rule
      if (   (isset($p_params[ARCHIVE_ZIP_PARAM_BY_NAME]))
          && ($p_params[ARCHIVE_ZIP_PARAM_BY_NAME] != 0)) {

          // ----- Look if the filename is in the list
          for ($j=0;
		          ($j<sizeof($p_params[ARCHIVE_ZIP_PARAM_BY_NAME]))
			   && (!$v_extract);
			   $j++) {

              // ----- Look for a directory
              if (substr($p_params[ARCHIVE_ZIP_PARAM_BY_NAME][$j], -1) == "/") {

                  // ----- Look if the directory is in the filename path
                  if (   (strlen($v_header['stored_filename']) > strlen($p_params[ARCHIVE_ZIP_PARAM_BY_NAME][$j]))
                      && (substr($v_header['stored_filename'], 0, strlen($p_params[ARCHIVE_ZIP_PARAM_BY_NAME][$j])) == $p_params[ARCHIVE_ZIP_PARAM_BY_NAME][$j])) {
                      $v_extract = true;
                  }
              }
              // ----- Look for a filename
              elseif ($v_header['stored_filename'] == $p_params[ARCHIVE_ZIP_PARAM_BY_NAME][$j]) {
                  $v_extract = true;
              }
          }
      }

      // ----- Look for extract by ereg rule
      else if (   (isset($p_params[ARCHIVE_ZIP_PARAM_BY_EREG]))
               && ($p_params[ARCHIVE_ZIP_PARAM_BY_EREG] != "")) {

          if (ereg($p_params[ARCHIVE_ZIP_PARAM_BY_EREG], $v_header['stored_filename'])) {
              $v_extract = true;
          }
      }

      // ----- Look for extract by preg rule
      else if (   (isset($p_params[ARCHIVE_ZIP_PARAM_BY_PREG]))
               && ($p_params[ARCHIVE_ZIP_PARAM_BY_PREG] != "")) {

          if (preg_match($p_params[ARCHIVE_ZIP_PARAM_BY_PREG], $v_header['stored_filename'])) {
              $v_extract = true;
          }
      }

      // ----- Look for extract by index rule
      else if (   (isset($p_params[ARCHIVE_ZIP_PARAM_BY_INDEX]))
               && ($p_params[ARCHIVE_ZIP_PARAM_BY_INDEX] != 0)) {

          // ----- Look if the index is in the list
          for ($j=$j_start; ($j<sizeof($p_params[ARCHIVE_ZIP_PARAM_BY_INDEX])) && (!$v_extract); $j++) {

              if (($i>=$p_params[ARCHIVE_ZIP_PARAM_BY_INDEX][$j]['start']) && ($i<=$p_params[ARCHIVE_ZIP_PARAM_BY_INDEX][$j]['end'])) {
                  $v_extract = true;
              }
              if ($i>=$p_params[ARCHIVE_ZIP_PARAM_BY_INDEX][$j]['end']) {
                  $j_start = $j+1;
              }

              if ($p_params[ARCHIVE_ZIP_PARAM_BY_INDEX][$j]['start']>$i) {
                  break;
              }
          }
      }

      // ----- Look for no rule, which means extract all the archive
      else {
          $v_extract = true;
      }


      // ----- Look for real extraction
      if ($v_extract)
      {

        // ----- Go to the file position
        @rewind($this->_zip_fd);
        if (@fseek($this->_zip_fd, $v_header['offset']))
        {
          // ----- Close the zip file
          $this->_closeFd();

          // ----- Error log
          $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_ARCHIVE_ZIP, 'Invalid archive size');

          // ----- Return
          return Archive_Zip::errorCode();
        }

        // ----- Look for extraction as string
        if ($p_params[ARCHIVE_ZIP_PARAM_EXTRACT_AS_STRING]) {

          // ----- Extracting the file
          if (($v_result = $this->_extractFileAsString($v_header, $v_string)) != 1)
          {
            // ----- Close the zip file
            $this->_closeFd();

            return $v_result;
          }

          // ----- Get the only interesting attributes
          if (($v_result = $this->_convertHeader2FileInfo($v_header, $p_file_list[$v_nb_extracted])) != 1)
          {
            // ----- Close the zip file
            $this->_closeFd();

            return $v_result;
          }

          // ----- Set the file content
          $p_file_list[$v_nb_extracted]['content'] = $v_string;

          // ----- Next extracted file
          $v_nb_extracted++;
        }
        else {
          // ----- Extracting the file
          if (($v_result = $this->_extractFile($v_header, $p_path, $p_remove_path, $p_remove_all_path, $p_params)) != 1)
          {
            // ----- Close the zip file
            $this->_closeFd();

            return $v_result;
          }

          // ----- Get the only interesting attributes
          if (($v_result = $this->_convertHeader2FileInfo($v_header, $p_file_list[$v_nb_extracted++])) != 1)
          {
            // ----- Close the zip file
            $this->_closeFd();

            return $v_result;
          }
        }
      }
    }

    // ----- Close the zip file
    $this->_closeFd();

    // ----- Return
    return $v_result;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : _extractFile()
  // Description :
  // Parameters :
  // Return Values :
  // ---------------------------------------------------------------------------
  /**
  * Archive_Zip::_extractFile()
  *
  * { Description }
  *
  */
  function _extractFile(&$p_entry, $p_path, $p_remove_path, $p_remove_all_path, &$p_params)
  {
    $v_result=1;

    // ----- Read the file header
    if (($v_result = $this->_readFileHeader($v_header)) != 1)
    {
      // ----- Return
      return $v_result;
    }


    // ----- Check that the file header is coherent with $p_entry info
    // TBC

    // ----- Look for all path to remove
    if ($p_remove_all_path == true) {
        // ----- Get the basename of the path
        $p_entry['filename'] = basename($p_entry['filename']);
    }

    // ----- Look for path to remove
    else if ($p_remove_path != "")
    {
      //if (strcmp($p_remove_path, $p_entry['filename'])==0)
      if ($this->_tool_PathInclusion($p_remove_path, $p_entry['filename']) == 2)
      {

        // ----- Change the file status
        $p_entry['status'] = "filtered";

        // ----- Return
        return $v_result;
      }

      $p_remove_path_size = strlen($p_remove_path);
      if (substr($p_entry['filename'], 0, $p_remove_path_size) == $p_remove_path)
      {

        // ----- Remove the path
        $p_entry['filename'] = substr($p_entry['filename'], $p_remove_path_size);

      }
    }

    // ----- Add the path
    if ($p_path != '')
    {
      $p_entry['filename'] = $p_path."/".$p_entry['filename'];
    }

    // ----- Look for pre-extract callback
    if (   (isset($p_params[ARCHIVE_ZIP_PARAM_PRE_EXTRACT]))
	    && ($p_params[ARCHIVE_ZIP_PARAM_PRE_EXTRACT] != '')) {

      // ----- Generate a local information
      $v_local_header = array();
      $this->_convertHeader2FileInfo($p_entry, $v_local_header);

      // ----- Call the callback
      // Here I do not use call_user_func() because I need to send a reference to the
      // header.
      eval('$v_result = '.$p_params[ARCHIVE_ZIP_PARAM_PRE_EXTRACT].'(ARCHIVE_ZIP_PARAM_PRE_EXTRACT, $v_local_header);');
      if ($v_result == 0) {
        // ----- Change the file status
        $p_entry['status'] = "skipped";
        $v_result = 1;
      }

      // ----- Update the informations
      // Only some fields can be modified
      $p_entry['filename'] = $v_local_header['filename'];
    }

    // ----- Trace

    // ----- Look if extraction should be done
    if ($p_entry['status'] == 'ok') {

    // ----- Look for specific actions while the file exist
    if (file_exists($p_entry['filename']))
    {

      // ----- Look if file is a directory
      if (is_dir($p_entry['filename']))
      {

        // ----- Change the file status
        $p_entry['status'] = "already_a_directory";

        // ----- Return
        //return $v_result;
      }
      // ----- Look if file is write protected
      else if (!is_writeable($p_entry['filename']))
      {

        // ----- Change the file status
        $p_entry['status'] = "write_protected";

        // ----- Return
        //return $v_result;
      }

      // ----- Look if the extracted file is older
      else if (filemtime($p_entry['filename']) > $p_entry['mtime'])
      {

        // ----- Change the file status
        $p_entry['status'] = "newer_exist";

        // ----- Return
        //return $v_result;
      }
    }

    // ----- Check the directory availability and create it if necessary
    else {
      if ((($p_entry['external']&0x00000010)==0x00000010) || (substr($p_entry['filename'], -1) == '/'))
        $v_dir_to_check = $p_entry['filename'];
      else if (!strstr($p_entry['filename'], "/"))
        $v_dir_to_check = "";
      else
        $v_dir_to_check = dirname($p_entry['filename']);

      if (($v_result = $this->_dirCheck($v_dir_to_check, (($p_entry['external']&0x00000010)==0x00000010))) != 1) {

        // ----- Change the file status
        $p_entry['status'] = "path_creation_fail";

        // ----- Return
        //return $v_result;
        $v_result = 1;
      }
    }
    }

    // ----- Look if extraction should be done
    if ($p_entry['status'] == 'ok') {

      // ----- Do the extraction (if not a folder)
      if (!(($p_entry['external']&0x00000010)==0x00000010))
      {

        // ----- Look for not compressed file
        if ($p_entry['compressed_size'] == $p_entry['size'])
        {

          // ----- Opening destination file
          if (($v_dest_file = @fopen($p_entry['filename'], 'wb')) == 0)
          {

            // ----- Change the file status
            $p_entry['status'] = "write_error";

            // ----- Return
            return $v_result;
          }


          // ----- Read the file by ARCHIVE_ZIP_READ_BLOCK_SIZE octets blocks
          $v_size = $p_entry['compressed_size'];
          while ($v_size != 0)
          {
            $v_read_size = ($v_size < ARCHIVE_ZIP_READ_BLOCK_SIZE ? $v_size : ARCHIVE_ZIP_READ_BLOCK_SIZE);
            $v_buffer = fread($this->_zip_fd, $v_read_size);
            $v_binary_data = pack('a'.$v_read_size, $v_buffer);
            @fwrite($v_dest_file, $v_binary_data, $v_read_size);
            $v_size -= $v_read_size;
          }

          // ----- Closing the destination file
          fclose($v_dest_file);

          // ----- Change the file mtime
          touch($p_entry['filename'], $p_entry['mtime']);
        }
        else
        {
          // ----- Trace

          // ----- Opening destination file
          if (($v_dest_file = @fopen($p_entry['filename'], 'wb')) == 0) {

            // ----- Change the file status
            $p_entry['status'] = "write_error";

            return $v_result;
          }


          // ----- Read the compressed file in a buffer (one shot)
          $v_buffer = @fread($this->_zip_fd, $p_entry['compressed_size']);

          // ----- Decompress the file
          $v_file_content = gzinflate($v_buffer);
          unset($v_buffer);

          // ----- Write the uncompressed data
          @fwrite($v_dest_file, $v_file_content, $p_entry['size']);
          unset($v_file_content);

          // ----- Closing the destination file
          @fclose($v_dest_file);

          // ----- Change the file mtime
          touch($p_entry['filename'], $p_entry['mtime']);
        }

        // ----- Look for chmod option
        if (   (isset($p_params[ARCHIVE_ZIP_PARAM_SET_CHMOD]))
		    && ($p_params[ARCHIVE_ZIP_PARAM_SET_CHMOD] != 0)) {

          // ----- Change the mode of the file
          chmod($p_entry['filename'], $p_params[ARCHIVE_ZIP_PARAM_SET_CHMOD]);
        }

      }
    }

    // ----- Look for post-extract callback
    if (   (isset($p_params[ARCHIVE_ZIP_PARAM_POST_EXTRACT]))
	    && ($p_params[ARCHIVE_ZIP_PARAM_POST_EXTRACT] != '')) {

      // ----- Generate a local information
      $v_local_header = array();
      $this->_convertHeader2FileInfo($p_entry, $v_local_header);

      // ----- Call the callback
      // Here I do not use call_user_func() because I need to send a reference to the
      // header.
      eval('$v_result = '.$p_params[ARCHIVE_ZIP_PARAM_POST_EXTRACT].'(ARCHIVE_ZIP_PARAM_POST_EXTRACT, $v_local_header);');
    }

    // ----- Return
    return $v_result;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : _extractFileAsString()
  // Description :
  // Parameters :
  // Return Values :
  // ---------------------------------------------------------------------------
  /**
  * Archive_Zip::_extractFileAsString()
  *
  * { Description }
  *
  */
  function _extractFileAsString(&$p_entry, &$p_string)
  {
    $v_result=1;

    // ----- Read the file header
    $v_header = array();
    if (($v_result = $this->_readFileHeader($v_header)) != 1)
    {
      // ----- Return
      return $v_result;
    }


    // ----- Check that the file header is coherent with $p_entry info
    // TBC

    // ----- Trace

    // ----- Do the extraction (if not a folder)
    if (!(($p_entry['external']&0x00000010)==0x00000010))
    {
      // ----- Look for not compressed file
      if ($p_entry['compressed_size'] == $p_entry['size'])
      {
        // ----- Trace

        // ----- Reading the file
        $p_string = fread($this->_zip_fd, $p_entry['compressed_size']);
      }
      else
      {
        // ----- Trace

        // ----- Reading the file
        $v_data = fread($this->_zip_fd, $p_entry['compressed_size']);

        // ----- Decompress the file
        $p_string = gzinflate($v_data);
      }

      // ----- Trace
    }
    else {
        // TBC : error : can not extract a folder in a string
    }

    // ----- Return
    return $v_result;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : _readFileHeader()
  // Description :
  // Parameters :
  // Return Values :
  // ---------------------------------------------------------------------------
  /**
  * Archive_Zip::_readFileHeader()
  *
  * { Description }
  *
  */
  function _readFileHeader(&$p_header)
  {
    $v_result=1;

    // ----- Read the 4 bytes signature
    $v_binary_data = @fread($this->_zip_fd, 4);
    $v_data = unpack('Vid', $v_binary_data);

    // ----- Check signature
    if ($v_data['id'] != 0x04034b50)
    {

      // ----- Error log
      $this->_errorLog(ARCHIVE_ZIP_ERR_BAD_FORMAT, 'Invalid archive structure');

      // ----- Return
      return Archive_Zip::errorCode();
    }

    // ----- Read the first 42 bytes of the header
    $v_binary_data = fread($this->_zip_fd, 26);

    // ----- Look for invalid block size
    if (strlen($v_binary_data) != 26)
    {
      $p_header['filename'] = "";
      $p_header['status'] = "invalid_header";

      // ----- Error log
      $this->_errorLog(ARCHIVE_ZIP_ERR_BAD_FORMAT, "Invalid block size : ".strlen($v_binary_data));

      // ----- Return
      return Archive_Zip::errorCode();
    }

    // ----- Extract the values
    $v_data = unpack('vversion/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len', $v_binary_data);

    // ----- Get filename
    $p_header['filename'] = fread($this->_zip_fd, $v_data['filename_len']);

    // ----- Get extra_fields
    if ($v_data['extra_len'] != 0) {
      $p_header['extra'] = fread($this->_zip_fd, $v_data['extra_len']);
    }
    else {
      $p_header['extra'] = '';
    }

    // ----- Extract properties
    $p_header['compression'] = $v_data['compression'];
    $p_header['size'] = $v_data['size'];
    $p_header['compressed_size'] = $v_data['compressed_size'];
    $p_header['crc'] = $v_data['crc'];
    $p_header['flag'] = $v_data['flag'];

    // ----- Recuperate date in UNIX format
    $p_header['mdate'] = $v_data['mdate'];
    $p_header['mtime'] = $v_data['mtime'];
    if ($p_header['mdate'] && $p_header['mtime'])
    {
      // ----- Extract time
      $v_hour = ($p_header['mtime'] & 0xF800) >> 11;
      $v_minute = ($p_header['mtime'] & 0x07E0) >> 5;
      $v_seconde = ($p_header['mtime'] & 0x001F)*2;

      // ----- Extract date
      $v_year = (($p_header['mdate'] & 0xFE00) >> 9) + 1980;
      $v_month = ($p_header['mdate'] & 0x01E0) >> 5;
      $v_day = $p_header['mdate'] & 0x001F;

      // ----- Get UNIX date format
      $p_header['mtime'] = mktime($v_hour, $v_minute, $v_seconde, $v_month, $v_day, $v_year);

    }
    else
    {
      $p_header['mtime'] = time();
    }

    // ----- Other informations

    // TBC
    //for(reset($v_data); $key = key($v_data); next($v_data)) {
    //}

    // ----- Set the stored filename
    $p_header['stored_filename'] = $p_header['filename'];

    // ----- Set the status field
    $p_header['status'] = "ok";

    // ----- Return
    return $v_result;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : _readCentralFileHeader()
  // Description :
  // Parameters :
  // Return Values :
  // ---------------------------------------------------------------------------
  /**
  * Archive_Zip::_readCentralFileHeader()
  *
  * { Description }
  *
  */
  function _readCentralFileHeader(&$p_header)
  {
    $v_result=1;

    // ----- Read the 4 bytes signature
    $v_binary_data = @fread($this->_zip_fd, 4);
    $v_data = unpack('Vid', $v_binary_data);

    // ----- Check signature
    if ($v_data['id'] != 0x02014b50)
    {

      // ----- Error log
      $this->_errorLog(ARCHIVE_ZIP_ERR_BAD_FORMAT, 'Invalid archive structure');

      // ----- Return
      return Archive_Zip::errorCode();
    }

    // ----- Read the first 42 bytes of the header
    $v_binary_data = fread($this->_zip_fd, 42);

    // ----- Look for invalid block size
    if (strlen($v_binary_data) != 42)
    {
      $p_header['filename'] = "";
      $p_header['status'] = "invalid_header";

      // ----- Error log
      $this->_errorLog(ARCHIVE_ZIP_ERR_BAD_FORMAT, "Invalid block size : ".strlen($v_binary_data));

      // ----- Return
      return Archive_Zip::errorCode();
    }

    // ----- Extract the values
    $p_header = unpack('vversion/vversion_extracted/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len/vcomment_len/vdisk/vinternal/Vexternal/Voffset', $v_binary_data);

    // ----- Get filename
    if ($p_header['filename_len'] != 0)
      $p_header['filename'] = fread($this->_zip_fd, $p_header['filename_len']);
    else
      $p_header['filename'] = '';

    // ----- Get extra
    if ($p_header['extra_len'] != 0)
      $p_header['extra'] = fread($this->_zip_fd, $p_header['extra_len']);
    else
      $p_header['extra'] = '';

    // ----- Get comment
    if ($p_header['comment_len'] != 0)
      $p_header['comment'] = fread($this->_zip_fd, $p_header['comment_len']);
    else
      $p_header['comment'] = '';

    // ----- Extract properties

    // ----- Recuperate date in UNIX format
    if ($p_header['mdate'] && $p_header['mtime'])
    {
      // ----- Extract time
      $v_hour = ($p_header['mtime'] & 0xF800) >> 11;
      $v_minute = ($p_header['mtime'] & 0x07E0) >> 5;
      $v_seconde = ($p_header['mtime'] & 0x001F)*2;

      // ----- Extract date
      $v_year = (($p_header['mdate'] & 0xFE00) >> 9) + 1980;
      $v_month = ($p_header['mdate'] & 0x01E0) >> 5;
      $v_day = $p_header['mdate'] & 0x001F;

      // ----- Get UNIX date format
      $p_header['mtime'] = mktime($v_hour, $v_minute, $v_seconde, $v_month, $v_day, $v_year);

    }
    else
    {
      $p_header['mtime'] = time();
    }

    // ----- Set the stored filename
    $p_header['stored_filename'] = $p_header['filename'];

    // ----- Set default status to ok
    $p_header['status'] = 'ok';

    // ----- Look if it is a directory
    if (substr($p_header['filename'], -1) == '/')
    {
      $p_header['external'] = 0x41FF0010;
    }


    // ----- Return
    return $v_result;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : _readEndCentralDir()
  // Description :
  // Parameters :
  // Return Values :
  // ---------------------------------------------------------------------------
  /**
  * Archive_Zip::_readEndCentralDir()
  *
  * { Description }
  *
  */
  function _readEndCentralDir(&$p_central_dir)
  {
    $v_result=1;

    // ----- Go to the end of the zip file
    $v_size = filesize($this->_zipname);
    @fseek($this->_zip_fd, $v_size);
    if (@ftell($this->_zip_fd) != $v_size) {
      $this->_errorLog(ARCHIVE_ZIP_ERR_BAD_FORMAT,
	                   'Unable to go to the end of the archive \''
					   .$this->_zipname.'\'');
      return Archive_Zip::errorCode();
    }

    // ----- First try : look if this is an archive with no commentaries
	// (most of the time)
    // in this case the end of central dir is at 22 bytes of the file end
    $v_found = 0;
    if ($v_size > 26) {
      @fseek($this->_zip_fd, $v_size-22);
      if (($v_pos = @ftell($this->_zip_fd)) != ($v_size-22)) {
        $this->_errorLog(ARCHIVE_ZIP_ERR_BAD_FORMAT,
		                 'Unable to seek back to the middle of the archive \''
						 .$this->_zipname.'\'');
        return Archive_Zip::errorCode();
      }

      // ----- Read for bytes
      $v_binary_data = @fread($this->_zip_fd, 4);
      $v_data = unpack('Vid', $v_binary_data);

      // ----- Check signature
      if ($v_data['id'] == 0x06054b50) {
        $v_found = 1;
      }

      $v_pos = ftell($this->_zip_fd);
    }

    // ----- Go back to the maximum possible size of the Central Dir End Record
    if (!$v_found) {
      $v_maximum_size = 65557; // 0xFFFF + 22;
      if ($v_maximum_size > $v_size)
        $v_maximum_size = $v_size;
      @fseek($this->_zip_fd, $v_size-$v_maximum_size);
      if (@ftell($this->_zip_fd) != ($v_size-$v_maximum_size)) {
        $this->_errorLog(ARCHIVE_ZIP_ERR_BAD_FORMAT,
		                 'Unable to seek back to the middle of the archive \''
						 .$this->_zipname.'\'');
        return Archive_Zip::errorCode();
      }

      // ----- Read byte per byte in order to find the signature
      $v_pos = ftell($this->_zip_fd);
      $v_bytes = 0x00000000;
      while ($v_pos < $v_size) {
        // ----- Read a byte
        $v_byte = @fread($this->_zip_fd, 1);

        // -----  Add the byte
        $v_bytes = ($v_bytes << 8) | Ord($v_byte);

        // ----- Compare the bytes
        if ($v_bytes == 0x504b0506) {
          $v_pos++;
          break;
        }

        $v_pos++;
      }

      // ----- Look if not found end of central dir
      if ($v_pos == $v_size) {
        $this->_errorLog(ARCHIVE_ZIP_ERR_BAD_FORMAT,
		                 "Unable to find End of Central Dir Record signature");
        return Archive_Zip::errorCode();
      }
    }

    // ----- Read the first 18 bytes of the header
    $v_binary_data = fread($this->_zip_fd, 18);

    // ----- Look for invalid block size
    if (strlen($v_binary_data) != 18) {
      $this->_errorLog(ARCHIVE_ZIP_ERR_BAD_FORMAT,
	                   "Invalid End of Central Dir Record size : "
					   .strlen($v_binary_data));
      return Archive_Zip::errorCode();
    }

    // ----- Extract the values
    $v_data = unpack('vdisk/vdisk_start/vdisk_entries/ventries/Vsize/Voffset/vcomment_size', $v_binary_data);

    // ----- Check the global size
    if (($v_pos + $v_data['comment_size'] + 18) != $v_size) {
      $this->_errorLog(ARCHIVE_ZIP_ERR_BAD_FORMAT,
	                   "Fail to find the right signature");
      return Archive_Zip::errorCode();
    }

    // ----- Get comment
    if ($v_data['comment_size'] != 0)
      $p_central_dir['comment'] = fread($this->_zip_fd, $v_data['comment_size']);
    else
      $p_central_dir['comment'] = '';

    $p_central_dir['entries'] = $v_data['entries'];
    $p_central_dir['disk_entries'] = $v_data['disk_entries'];
    $p_central_dir['offset'] = $v_data['offset'];
    $p_central_dir['size'] = $v_data['size'];
    $p_central_dir['disk'] = $v_data['disk'];
    $p_central_dir['disk_start'] = $v_data['disk_start'];

    // ----- Return
    return $v_result;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : _deleteByRule()
  // Description :
  // Parameters :
  // Return Values :
  // ---------------------------------------------------------------------------
  /**
  * Archive_Zip::_deleteByRule()
  *
  * { Description }
  *
  */
  function _deleteByRule(&$p_result_list, &$p_params)
  {
    $v_result=1;
    $v_list_detail = array();

    // ----- Open the zip file
    if (($v_result=$this->_openFd('rb')) != 1)
    {
      // ----- Return
      return $v_result;
    }

    // ----- Read the central directory informations
    $v_central_dir = array();
    if (($v_result = $this->_readEndCentralDir($v_central_dir)) != 1)
    {
      $this->_closeFd();
      return $v_result;
    }

    // ----- Go to beginning of File
    @rewind($this->_zip_fd);

    // ----- Scan all the files
    // ----- Start at beginning of Central Dir
    $v_pos_entry = $v_central_dir['offset'];
    @rewind($this->_zip_fd);
    if (@fseek($this->_zip_fd, $v_pos_entry)) {
      // ----- Clean
      $this->_closeFd();

      $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_ARCHIVE_ZIP,
	                   'Invalid archive size');
      return Archive_Zip::errorCode();
    }

    // ----- Read each entry
    $v_header_list = array();
    $j_start = 0;
    for ($i=0, $v_nb_extracted=0; $i<$v_central_dir['entries']; $i++) {

      // ----- Read the file header
      $v_header_list[$v_nb_extracted] = array();
      $v_result
	    = $this->_readCentralFileHeader($v_header_list[$v_nb_extracted]);
      if ($v_result != 1) {
        // ----- Clean
        $this->_closeFd();

        return $v_result;
      }

      // ----- Store the index
      $v_header_list[$v_nb_extracted]['index'] = $i;

      // ----- Look for the specific extract rules
      $v_found = false;

      // ----- Look for extract by name rule
      if (   (isset($p_params[ARCHIVE_ZIP_PARAM_BY_NAME]))
          && ($p_params[ARCHIVE_ZIP_PARAM_BY_NAME] != 0)) {

          // ----- Look if the filename is in the list
          for ($j=0;
		       ($j<sizeof($p_params[ARCHIVE_ZIP_PARAM_BY_NAME]))
			     && (!$v_found);
			   $j++) {

              // ----- Look for a directory
              if (substr($p_params[ARCHIVE_ZIP_PARAM_BY_NAME][$j], -1) == "/") {

                  // ----- Look if the directory is in the filename path
                  if (   (strlen($v_header_list[$v_nb_extracted]['stored_filename']) > strlen($p_params[ARCHIVE_ZIP_PARAM_BY_NAME][$j]))
                      && (substr($v_header_list[$v_nb_extracted]['stored_filename'], 0, strlen($p_params[ARCHIVE_ZIP_PARAM_BY_NAME][$j])) == $p_params[ARCHIVE_ZIP_PARAM_BY_NAME][$j])) {
                      $v_found = true;
                  }
                  elseif (   (($v_header_list[$v_nb_extracted]['external']&0x00000010)==0x00000010) /* Indicates a folder */
                          && ($v_header_list[$v_nb_extracted]['stored_filename'].'/' == $p_params[ARCHIVE_ZIP_PARAM_BY_NAME][$j])) {
                      $v_found = true;
                  }
              }
              // ----- Look for a filename
              elseif ($v_header_list[$v_nb_extracted]['stored_filename']
			          == $p_params[ARCHIVE_ZIP_PARAM_BY_NAME][$j]) {
                  $v_found = true;
              }
          }
      }

      // ----- Look for extract by ereg rule
      else if (   (isset($p_params[ARCHIVE_ZIP_PARAM_BY_EREG]))
               && ($p_params[ARCHIVE_ZIP_PARAM_BY_EREG] != "")) {

          if (ereg($p_params[ARCHIVE_ZIP_PARAM_BY_EREG],
		           $v_header_list[$v_nb_extracted]['stored_filename'])) {
              $v_found = true;
          }
      }

      // ----- Look for extract by preg rule
      else if (   (isset($p_params[ARCHIVE_ZIP_PARAM_BY_PREG]))
               && ($p_params[ARCHIVE_ZIP_PARAM_BY_PREG] != "")) {

          if (preg_match($p_params[ARCHIVE_ZIP_PARAM_BY_PREG],
		                 $v_header_list[$v_nb_extracted]['stored_filename'])) {
              $v_found = true;
          }
      }

      // ----- Look for extract by index rule
      else if (   (isset($p_params[ARCHIVE_ZIP_PARAM_BY_INDEX]))
               && ($p_params[ARCHIVE_ZIP_PARAM_BY_INDEX] != 0)) {

          // ----- Look if the index is in the list
          for ($j=$j_start;
		       ($j<sizeof($p_params[ARCHIVE_ZIP_PARAM_BY_INDEX]))
			     && (!$v_found);
			   $j++) {

              if (   ($i>=$p_params[ARCHIVE_ZIP_PARAM_BY_INDEX][$j]['start'])
			      && ($i<=$p_params[ARCHIVE_ZIP_PARAM_BY_INDEX][$j]['end'])) {
                  $v_found = true;
              }
              if ($i>=$p_params[ARCHIVE_ZIP_PARAM_BY_INDEX][$j]['end']) {
                  $j_start = $j+1;
              }

              if ($p_params[ARCHIVE_ZIP_PARAM_BY_INDEX][$j]['start']>$i) {
                  break;
              }
          }
      }

      // ----- Look for deletion
      if ($v_found) {
        unset($v_header_list[$v_nb_extracted]);
      }
      else {
        $v_nb_extracted++;
      }
    }

    // ----- Look if something need to be deleted
    if ($v_nb_extracted > 0) {

        // ----- Creates a temporay file
        $v_zip_temp_name = ARCHIVE_ZIP_TEMPORARY_DIR.uniqid('archive_zip-')
		                   .'.tmp';

        // ----- Creates a temporary zip archive
        $v_temp_zip = new Archive_Zip($v_zip_temp_name);

        // ----- Open the temporary zip file in write mode
        if (($v_result = $v_temp_zip->_openFd('wb')) != 1) {
            $this->_closeFd();

            // ----- Return
            return $v_result;
        }

        // ----- Look which file need to be kept
        for ($i=0; $i<sizeof($v_header_list); $i++) {

            // ----- Calculate the position of the header
            @rewind($this->_zip_fd);
            if (@fseek($this->_zip_fd,  $v_header_list[$i]['offset'])) {
                // ----- Clean
                $this->_closeFd();
                $v_temp_zip->_closeFd();
                @unlink($v_zip_temp_name);

                $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_ARCHIVE_ZIP,
				                 'Invalid archive size');
                return Archive_Zip::errorCode();
            }

            // ----- Read the file header
            if (($v_result = $this->_readFileHeader($v_header_list[$i])) != 1) {
                // ----- Clean
                $this->_closeFd();
                $v_temp_zip->_closeFd();
                @unlink($v_zip_temp_name);

                return $v_result;
            }

            // ----- Write the file header
            $v_result = $v_temp_zip->_writeFileHeader($v_header_list[$i]);
            if ($v_result != 1) {
                // ----- Clean
                $this->_closeFd();
                $v_temp_zip->_closeFd();
                @unlink($v_zip_temp_name);

                return $v_result;
            }

            // ----- Read/write the data block
            $v_result = $this->_tool_CopyBlock($this->_zip_fd,
			                                   $v_temp_zip->_zip_fd,
								       $v_header_list[$i]['compressed_size']);
            if ($v_result != 1) {
                // ----- Clean
                $this->_closeFd();
                $v_temp_zip->_closeFd();
                @unlink($v_zip_temp_name);

                return $v_result;
            }
        }

        // ----- Store the offset of the central dir
        $v_offset = @ftell($v_temp_zip->_zip_fd);

        // ----- Re-Create the Central Dir files header
        for ($i=0; $i<sizeof($v_header_list); $i++) {
            // ----- Create the file header
            $v_result=$v_temp_zip->_writeCentralFileHeader($v_header_list[$i]);
            if ($v_result != 1) {
            	// ----- Clean
                $v_temp_zip->_closeFd();
                $this->_closeFd();
                @unlink($v_zip_temp_name);

                return $v_result;
            }

            // ----- Transform the header to a 'usable' info
            $v_temp_zip->_convertHeader2FileInfo($v_header_list[$i],
			                                     $p_result_list[$i]);
        }


        // ----- Zip file comment
        $v_comment = '';

        // ----- Calculate the size of the central header
        $v_size = @ftell($v_temp_zip->_zip_fd)-$v_offset;

        // ----- Create the central dir footer
        $v_result = $v_temp_zip->_writeCentralHeader(sizeof($v_header_list),
		                                             $v_size, $v_offset,
													 $v_comment);
        if ($v_result != 1) {
            // ----- Clean
            unset($v_header_list);
            $v_temp_zip->_closeFd();
            $this->_closeFd();
            @unlink($v_zip_temp_name);

            return $v_result;
        }

        // ----- Close
        $v_temp_zip->_closeFd();
        $this->_closeFd();

        // ----- Delete the zip file
        // TBC : I should test the result ...
        @unlink($this->_zipname);

        // ----- Rename the temporary file
        // TBC : I should test the result ...
        //@rename($v_zip_temp_name, $this->_zipname);
        $this->_tool_Rename($v_zip_temp_name, $this->_zipname);

        // ----- Destroy the temporary archive
        unset($v_temp_zip);
    }

    // ----- Return
    return $v_result;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : _dirCheck()
  // Description :
  //   Check if a directory exists, if not it creates it and all the parents directory
  //   which may be useful.
  // Parameters :
  //   $p_dir : Directory path to check.
  // Return Values :
  //    1 : OK
  //   -1 : Unable to create directory
  // ---------------------------------------------------------------------------
  /**
  * Archive_Zip::_dirCheck()
  *
  * { Description }
  *
  * @param [type] $p_is_dir
  */
  function _dirCheck($p_dir, $p_is_dir=false)
  {
    $v_result = 1;

    // ----- Remove the final '/'
    if (($p_is_dir) && (substr($p_dir, -1)=='/')) {
      $p_dir = substr($p_dir, 0, strlen($p_dir)-1);
    }

    // ----- Check the directory availability
    if ((is_dir($p_dir)) || ($p_dir == "")) {
      return 1;
    }

    // ----- Extract parent directory
    $p_parent_dir = dirname($p_dir);

    // ----- Just a check
    if ($p_parent_dir != $p_dir) {
      // ----- Look for parent directory
      if ($p_parent_dir != "") {
        if (($v_result = $this->_dirCheck($p_parent_dir)) != 1) {
          return $v_result;
        }
      }
    }

    // ----- Create the directory
    if (!@mkdir($p_dir, 0777)) {
      $this->_errorLog(ARCHIVE_ZIP_ERR_DIR_CREATE_FAIL,
	                   "Unable to create directory '$p_dir'");
      return Archive_Zip::errorCode();
    }

    // ----- Return
    return $v_result;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : _merge()
  // Description :
  //   If $p_archive_to_add does not exist, the function exit with a success result.
  // Parameters :
  // Return Values :
  // ---------------------------------------------------------------------------
  /**
  * Archive_Zip::_merge()
  *
  * { Description }
  *
  */
  function _merge(&$p_archive_to_add)
  {
    $v_result=1;

    // ----- Look if the archive_to_add exists
    if (!is_file($p_archive_to_add->_zipname)) {
      // ----- Nothing to merge, so merge is a success
      return 1;
    }

    // ----- Look if the archive exists
    if (!is_file($this->_zipname)) {
      // ----- Do a duplicate
      $v_result = $this->_duplicate($p_archive_to_add->_zipname);

      return $v_result;
    }

    // ----- Open the zip file
    if (($v_result=$this->_openFd('rb')) != 1) {
      return $v_result;
    }

    // ----- Read the central directory informations
    $v_central_dir = array();
    if (($v_result = $this->_readEndCentralDir($v_central_dir)) != 1) {
      $this->_closeFd();
      return $v_result;
    }

    // ----- Go to beginning of File
    @rewind($this->_zip_fd);

    // ----- Open the archive_to_add file
    if (($v_result=$p_archive_to_add->_openFd('rb')) != 1) {
      $this->_closeFd();
      return $v_result;
    }

    // ----- Read the central directory informations
    $v_central_dir_to_add = array();
    $v_result = $p_archive_to_add->_readEndCentralDir($v_central_dir_to_add);
    if ($v_result != 1) {
      $this->_closeFd();
      $p_archive_to_add->_closeFd();
      return $v_result;
    }

    // ----- Go to beginning of File
    @rewind($p_archive_to_add->_zip_fd);

    // ----- Creates a temporay file
    $v_zip_temp_name = ARCHIVE_ZIP_TEMPORARY_DIR.uniqid('archive_zip-').'.tmp';

    // ----- Open the temporary file in write mode
    if (($v_zip_temp_fd = @fopen($v_zip_temp_name, 'wb')) == 0) {
      $this->_closeFd();
      $p_archive_to_add->_closeFd();
      $this->_errorLog(ARCHIVE_ZIP_ERR_READ_OPEN_FAIL,
	                   'Unable to open temporary file \''
					   .$v_zip_temp_name.'\' in binary write mode');
      return Archive_Zip::errorCode();
    }

    // ----- Copy the files from the archive to the temporary file
    // TBC : Here I should better append the file and go back to erase the
	// central dir
    $v_size = $v_central_dir['offset'];
    while ($v_size != 0) {
      $v_read_size = ($v_size < ARCHIVE_ZIP_READ_BLOCK_SIZE
	                  ? $v_size : ARCHIVE_ZIP_READ_BLOCK_SIZE);
      $v_buffer = fread($this->_zip_fd, $v_read_size);
      @fwrite($v_zip_temp_fd, $v_buffer, $v_read_size);
      $v_size -= $v_read_size;
    }

    // ----- Copy the files from the archive_to_add into the temporary file
    $v_size = $v_central_dir_to_add['offset'];
    while ($v_size != 0) {
      $v_read_size = ($v_size < ARCHIVE_ZIP_READ_BLOCK_SIZE
	                  ? $v_size : ARCHIVE_ZIP_READ_BLOCK_SIZE);
      $v_buffer = fread($p_archive_to_add->_zip_fd, $v_read_size);
      @fwrite($v_zip_temp_fd, $v_buffer, $v_read_size);
      $v_size -= $v_read_size;
    }

    // ----- Store the offset of the central dir
    $v_offset = @ftell($v_zip_temp_fd);

    // ----- Copy the block of file headers from the old archive
    $v_size = $v_central_dir['size'];
    while ($v_size != 0) {
      $v_read_size = ($v_size < ARCHIVE_ZIP_READ_BLOCK_SIZE
	                  ? $v_size : ARCHIVE_ZIP_READ_BLOCK_SIZE);
      $v_buffer = @fread($this->_zip_fd, $v_read_size);
      @fwrite($v_zip_temp_fd, $v_buffer, $v_read_size);
      $v_size -= $v_read_size;
    }

    // ----- Copy the block of file headers from the archive_to_add
    $v_size = $v_central_dir_to_add['size'];
    while ($v_size != 0) {
      $v_read_size = ($v_size < ARCHIVE_ZIP_READ_BLOCK_SIZE
	                  ? $v_size : ARCHIVE_ZIP_READ_BLOCK_SIZE);
      $v_buffer = @fread($p_archive_to_add->_zip_fd, $v_read_size);
      @fwrite($v_zip_temp_fd, $v_buffer, $v_read_size);
      $v_size -= $v_read_size;
    }

    // ----- Zip file comment
    // TBC : I should merge the two comments
    $v_comment = '';

    // ----- Calculate the size of the (new) central header
    $v_size = @ftell($v_zip_temp_fd)-$v_offset;

    // ----- Swap the file descriptor
    // Here is a trick : I swap the temporary fd with the zip fd, in order to use
    // the following methods on the temporary fil and not the real archive fd
    $v_swap = $this->_zip_fd;
    $this->_zip_fd = $v_zip_temp_fd;
    $v_zip_temp_fd = $v_swap;

    // ----- Create the central dir footer
    if (($v_result = $this->_writeCentralHeader($v_central_dir['entries']
	                                          +$v_central_dir_to_add['entries'],
												$v_size, $v_offset,
												$v_comment)) != 1) {
      $this->_closeFd();
      $p_archive_to_add->_closeFd();
      @fclose($v_zip_temp_fd);
      $this->_zip_fd = null;

      // ----- Reset the file list
      unset($v_header_list);

      // ----- Return
      return $v_result;
    }

    // ----- Swap back the file descriptor
    $v_swap = $this->_zip_fd;
    $this->_zip_fd = $v_zip_temp_fd;
    $v_zip_temp_fd = $v_swap;

    // ----- Close
    $this->_closeFd();
    $p_archive_to_add->_closeFd();

    // ----- Close the temporary file
    @fclose($v_zip_temp_fd);

    // ----- Delete the zip file
    // TBC : I should test the result ...
    @unlink($this->_zipname);

    // ----- Rename the temporary file
    // TBC : I should test the result ...
    //@rename($v_zip_temp_name, $this->_zipname);
    $this->_tool_Rename($v_zip_temp_name, $this->_zipname);

    // ----- Return
    return $v_result;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : _duplicate()
  // Description :
  // Parameters :
  // Return Values :
  // ---------------------------------------------------------------------------
  /**
  * Archive_Zip::_duplicate()
  *
  * { Description }
  *
  */
  function _duplicate($p_archive_filename)
  {
    $v_result=1;

    // ----- Look if the $p_archive_filename exists
    if (!is_file($p_archive_filename)) {

      // ----- Nothing to duplicate, so duplicate is a success.
      $v_result = 1;

      // ----- Return
      return $v_result;
    }

    // ----- Open the zip file
    if (($v_result=$this->_openFd('wb')) != 1) {
      // ----- Return
      return $v_result;
    }

    // ----- Open the temporary file in write mode
    if (($v_zip_temp_fd = @fopen($p_archive_filename, 'rb')) == 0) {
      $this->_closeFd();
      $this->_errorLog(ARCHIVE_ZIP_ERR_READ_OPEN_FAIL,
	                   'Unable to open archive file \''
					   .$p_archive_filename.'\' in binary write mode');
      return Archive_Zip::errorCode();
    }

    // ----- Copy the files from the archive to the temporary file
    // TBC : Here I should better append the file and go back to erase the
	// central dir
    $v_size = filesize($p_archive_filename);
    while ($v_size != 0) {
      $v_read_size = ($v_size < ARCHIVE_ZIP_READ_BLOCK_SIZE
	                  ? $v_size : ARCHIVE_ZIP_READ_BLOCK_SIZE);
      $v_buffer = fread($v_zip_temp_fd, $v_read_size);
      @fwrite($this->_zip_fd, $v_buffer, $v_read_size);
      $v_size -= $v_read_size;
    }

    // ----- Close
    $this->_closeFd();

    // ----- Close the temporary file
    @fclose($v_zip_temp_fd);

    return $v_result;
  }
  // ---------------------------------------------------------------------------

  /**
  * Archive_Zip::_check_parameters()
  *
  * { Description }
  *
  * @param integer $p_error_code
  * @param string $p_error_string
  */
  function _check_parameters(&$p_params, $p_default)
  {
    
    // ----- Check that param is an array
    if (!is_array($p_params)) {
        $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_PARAMETER,
		                 'Unsupported parameter, waiting for an array');
        return Archive_Zip::errorCode();
    }
    
    // ----- Check that all the params are valid
    for (reset($p_params); list($v_key, $v_value) = each($p_params); ) {
    	if (!isset($p_default[$v_key])) {
            $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_PARAMETER,
			                 'Unsupported parameter with key \''.$v_key.'\'');

            return Archive_Zip::errorCode();
    	}
    }

	// ----- Set the default values
    for (reset($p_default); list($v_key, $v_value) = each($p_default); ) {
    	if (!isset($p_params[$v_key])) {
    		$p_params[$v_key] = $p_default[$v_key];
    	}
    }
    
    // ----- Check specific parameters
    $v_callback_list = array ('callback_pre_add','callback_post_add',
	                          'callback_pre_extract','callback_post_extract');
    for ($i=0; $i<sizeof($v_callback_list); $i++) {
    	$v_key=$v_callback_list[$i];
        if (   (isset($p_params[$v_key])) && ($p_params[$v_key] != '')) {
            if (!function_exists($p_params[$v_key])) {
                $this->_errorLog(ARCHIVE_ZIP_ERR_INVALID_PARAM_VALUE,
				                 "Callback '".$p_params[$v_key]
								 ."()' is not an existing function for "
								 ."parameter '".$v_key."'");
                return Archive_Zip::errorCode();
            }
	    }
    }

    return(1);
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : _errorLog()
  // Description :
  // Parameters :
  // ---------------------------------------------------------------------------
  /**
  * Archive_Zip::_errorLog()
  *
  * { Description }
  *
  * @param integer $p_error_code
  * @param string $p_error_string
  */
  function _errorLog($p_error_code=0, $p_error_string='')
  {
      $this->_error_code = $p_error_code;
      $this->_error_string = $p_error_string;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : _errorReset()
  // Description :
  // Parameters :
  // ---------------------------------------------------------------------------
  /**
  * Archive_Zip::_errorReset()
  *
  * { Description }
  *
  */
  function _errorReset()
  {
      $this->_error_code = 1;
      $this->_error_string = '';
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : $this->_tool_PathReduction()
  // Description :
  // Parameters :
  // Return Values :
  // ---------------------------------------------------------------------------
  /**
  * _tool_PathReduction()
  *
  * { Description }
  *
  */
  function _tool_PathReduction($p_dir)
  {
    $v_result = "";

    // ----- Look for not empty path
    if ($p_dir != "")
    {
      // ----- Explode path by directory names
      $v_list = explode("/", $p_dir);

      // ----- Study directories from last to first
      for ($i=sizeof($v_list)-1; $i>=0; $i--)
      {
        // ----- Look for current path
        if ($v_list[$i] == ".")
        {
          // ----- Ignore this directory
          // Should be the first $i=0, but no check is done
        }
        else if ($v_list[$i] == "..")
        {
          // ----- Ignore it and ignore the $i-1
          $i--;
        }
        else if (($v_list[$i] == "") && ($i!=(sizeof($v_list)-1)) && ($i!=0))
        {
          // ----- Ignore only the double '//' in path,
          // but not the first and last '/'
        }
        else
        {
          $v_result = $v_list[$i].($i!=(sizeof($v_list)-1)?"/".$v_result:"");
        }
      }
    }

    // ----- Return
    return $v_result;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : $this->_tool_PathInclusion()
  // Description :
  //   This function indicates if the path $p_path is under the $p_dir tree. Or,
  //   said in an other way, if the file or sub-dir $p_path is inside the dir
  //   $p_dir.
  //   The function indicates also if the path is exactly the same as the dir.
  //   This function supports path with duplicated '/' like '//', but does not
  //   support '.' or '..' statements.
  // Parameters :
  // Return Values :
  //   0 if $p_path is not inside directory $p_dir
  //   1 if $p_path is inside directory $p_dir
  //   2 if $p_path is exactly the same as $p_dir
  // ---------------------------------------------------------------------------
  /**
  * _tool_PathInclusion()
  *
  * { Description }
  *
  */
  function _tool_PathInclusion($p_dir, $p_path)
  {
    $v_result = 1;

    // ----- Explode dir and path by directory separator
    $v_list_dir = explode("/", $p_dir);
    $v_list_dir_size = sizeof($v_list_dir);
    $v_list_path = explode("/", $p_path);
    $v_list_path_size = sizeof($v_list_path);

    // ----- Study directories paths
    $i = 0;
    $j = 0;
    while (($i < $v_list_dir_size) && ($j < $v_list_path_size) && ($v_result)) {

      // ----- Look for empty dir (path reduction)
      if ($v_list_dir[$i] == '') {
        $i++;
        continue;
      }
      if ($v_list_path[$j] == '') {
        $j++;
        continue;
      }

      // ----- Compare the items
      if (   ($v_list_dir[$i] != $v_list_path[$j])
	      && ($v_list_dir[$i] != '')
		  && ( $v_list_path[$j] != ''))  {
        $v_result = 0;
      }

      // ----- Next items
      $i++;
      $j++;
    }

    // ----- Look if everything seems to be the same
    if ($v_result) {
      // ----- Skip all the empty items
      while (($j < $v_list_path_size) && ($v_list_path[$j] == '')) $j++;
      while (($i < $v_list_dir_size) && ($v_list_dir[$i] == '')) $i++;

      if (($i >= $v_list_dir_size) && ($j >= $v_list_path_size)) {
        // ----- There are exactly the same
        $v_result = 2;
      }
      else if ($i < $v_list_dir_size) {
        // ----- The path is shorter than the dir
        $v_result = 0;
      }
    }

    // ----- Return
    return $v_result;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : $this->_tool_CopyBlock()
  // Description :
  // Parameters :
  //   $p_mode : read/write compression mode
  //             0 : src & dest normal
  //             1 : src gzip, dest normal
  //             2 : src normal, dest gzip
  //             3 : src & dest gzip
  // Return Values :
  // ---------------------------------------------------------------------------
  /**
  * _tool_CopyBlock()
  *
  * { Description }
  *
  * @param integer $p_mode
  */
  function _tool_CopyBlock($p_src, $p_dest, $p_size, $p_mode=0)
  {
    $v_result = 1;

    if ($p_mode==0)
    {
      while ($p_size != 0)
      {
        $v_read_size = ($p_size < ARCHIVE_ZIP_READ_BLOCK_SIZE
		                ? $p_size : ARCHIVE_ZIP_READ_BLOCK_SIZE);
        $v_buffer = @fread($p_src, $v_read_size);
        @fwrite($p_dest, $v_buffer, $v_read_size);
        $p_size -= $v_read_size;
      }
    }
    else if ($p_mode==1)
    {
      while ($p_size != 0)
      {
        $v_read_size = ($p_size < ARCHIVE_ZIP_READ_BLOCK_SIZE
		                ? $p_size : ARCHIVE_ZIP_READ_BLOCK_SIZE);
        $v_buffer = @gzread($p_src, $v_read_size);
        @fwrite($p_dest, $v_buffer, $v_read_size);
        $p_size -= $v_read_size;
      }
    }
    else if ($p_mode==2)
    {
      while ($p_size != 0)
      {
        $v_read_size = ($p_size < ARCHIVE_ZIP_READ_BLOCK_SIZE
		                ? $p_size : ARCHIVE_ZIP_READ_BLOCK_SIZE);
        $v_buffer = @fread($p_src, $v_read_size);
        @gzwrite($p_dest, $v_buffer, $v_read_size);
        $p_size -= $v_read_size;
      }
    }
    else if ($p_mode==3)
    {
      while ($p_size != 0)
      {
        $v_read_size = ($p_size < ARCHIVE_ZIP_READ_BLOCK_SIZE
		                ? $p_size : ARCHIVE_ZIP_READ_BLOCK_SIZE);
        $v_buffer = @gzread($p_src, $v_read_size);
        @gzwrite($p_dest, $v_buffer, $v_read_size);
        $p_size -= $v_read_size;
      }
    }

    // ----- Return
    return $v_result;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : $this->_tool_Rename()
  // Description :
  //   This function tries to do a simple rename() function. If it fails, it
  //   tries to copy the $p_src file in a new $p_dest file and then unlink the
  //   first one.
  // Parameters :
  //   $p_src : Old filename
  //   $p_dest : New filename
  // Return Values :
  //   1 on success, 0 on failure.
  // ---------------------------------------------------------------------------
  /**
  * _tool_Rename()
  *
  * { Description }
  *
  */
  function _tool_Rename($p_src, $p_dest)
  {
    $v_result = 1;

    // ----- Try to rename the files
    if (!@rename($p_src, $p_dest)) {

      // ----- Try to copy & unlink the src
      if (!@copy($p_src, $p_dest)) {
        $v_result = 0;
      }
      else if (!@unlink($p_src)) {
        $v_result = 0;
      }
    }

    // ----- Return
    return $v_result;
  }
  // ---------------------------------------------------------------------------

  // ---------------------------------------------------------------------------
  // Function : $this->_tool_TranslateWinPath()
  // Description :
  //   Translate windows path by replacing '\' by '/' and optionally removing
  //   drive letter.
  // Parameters :
  //   $p_path : path to translate.
  //   $p_remove_disk_letter : true | false
  // Return Values :
  //   The path translated.
  // ---------------------------------------------------------------------------
  /**
  * _tool_TranslateWinPath()
  *
  * { Description }
  *
  * @param [type] $p_remove_disk_letter
  */
  function _tool_TranslateWinPath($p_path, $p_remove_disk_letter=true)
  {
    if (stristr(php_uname(), 'windows')) {
      // ----- Look for potential disk letter
      if (   ($p_remove_disk_letter)
	      && (($v_position = strpos($p_path, ':')) != false)) {
          $p_path = substr($p_path, $v_position+1);
      }
      // ----- Change potential windows directory separator
      if ((strpos($p_path, '\\') > 0) || (substr($p_path, 0,1) == '\\')) {
          $p_path = strtr($p_path, '\\', '/');
      }
    }
    return $p_path;
  }
  // ---------------------------------------------------------------------------

  }
  // End of class


