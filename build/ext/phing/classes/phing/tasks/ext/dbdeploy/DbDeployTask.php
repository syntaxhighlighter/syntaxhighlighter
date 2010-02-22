<?php
/*
 *  $Id: DbDeployTask.php 59 2006-04-28 14:49:47Z lcrouch $
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
require_once 'phing/tasks/ext/dbdeploy/DbmsSyntaxFactory.php';


/**
 *  Generate SQL script for db using dbdeploy schema version table and delta scripts
 *
 *  <dbdeploy url="mysql:host=localhost;dbname=test" userid="dbdeploy" password="dbdeploy" dir="db" outputfile=""> 
 * 
 *  @author   Luke Crouch at SourceForge (http://sourceforge.net)
 *  @version  $Revision: 1.1 $
 *  @package  phing.tasks.ext.dbdeploy
 */

class DbDeployTask extends Task {
	
	public static $TABLE_NAME = 'changelog';

	protected $url;
	protected $userid;
	protected $password;
	protected $dir;
	protected $outputFile = 'dbdeploy_deploy.sql';
	protected $undoOutputFile = 'dbdeploy_undo.sql';
	protected $deltaSet = 'Main';
	protected $lastChangeToApply = 999;
	protected $dbmsSyntax = null;
	
    function main() {
    	try{
    		// get correct DbmsSyntax object
    		$dbms = substr($this->url, 0, strpos($this->url, ':'));
    		$dbmsSyntaxFactory = new DbmsSyntaxFactory($dbms);
    		$this->dbmsSyntax = $dbmsSyntaxFactory->getDbmsSyntax();
    		
			// open file handles for output
    		$outputFileHandle = fopen($this->outputFile, "w+");
    		$undoOutputFileHandle = fopen($this->undoOutputFile, "w+");
    		
    		// figure out which revisions are in the db already
			$this->appliedChangeNumbers = $this->getAppliedChangeNumbers();
			$this->log('Current db revision: ' . $this->getLastChangeAppliedInDb());
			
			// generate sql file needed to take db to "lastChangeToApply" version
			$doSql = $this->doDeploy();
			$undoSql = $this->undoDeploy();
			
			// write the do and undo SQL to their respective files
			fwrite($outputFileHandle, $doSql);
			fwrite($undoOutputFileHandle, $undoSql);
			
    	} catch (Exception $e){
    		throw new BuildException($e);
    	}
    }
	
    function getAppliedChangeNumbers(){
    	if(count($this->appliedChangeNumbers) == 0){
	        $this->log('Getting applied changed numbers from DB: ' . $this->url );
	    	$appliedChangeNumbers = array();
    		$dbh = new PDO($this->url, $this->userid, $this->password);
    		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    	$sql = "SELECT * FROM " . DbDeployTask::$TABLE_NAME . " WHERE delta_set = '$this->deltaSet' ORDER BY change_number";
			foreach($dbh->query($sql) as $change){
	    		$appliedChangeNumbers[] = $change['change_number'];
	    	}
	    	$this->appliedChangeNumbers = $appliedChangeNumbers;
    	}
    	return $this->appliedChangeNumbers;
    }
    
    function getLastChangeAppliedInDb(){
    	return (count($this->appliedChangeNumbers) > 0) ? max($this->appliedChangeNumbers) : 0;
    }

    function doDeploy(){
    	$sqlToPerformDeploy = '';
    	$lastChangeAppliedInDb = $this->getLastChangeAppliedInDb();    	
    	$files = $this->getDeltasFilesArray();
    	ksort($files);
    	foreach($files as $fileChangeNumber=>$fileName){
    		if($fileChangeNumber > $lastChangeAppliedInDb && $fileChangeNumber <= $this->lastChangeToApply){
    			$sqlToPerformDeploy .= '-- Fragment begins: ' . $fileChangeNumber . ' --' . "\n";
    			$sqlToPerformDeploy .= 'INSERT INTO ' . DbDeployTask::$TABLE_NAME . ' (change_number, delta_set, start_dt, applied_by, description)'.
					' VALUES ('. $fileChangeNumber .', \''. $this->deltaSet .'\', '. $this->dbmsSyntax->generateTimestamp() .', \'dbdeploy\', \''. $fileName .'\');' . "\n";
				$fullFileName = $this->dir . '/' . $fileName;
    			$fh = fopen($fullFileName, 'r');
    			$contents = fread($fh, 	filesize($fullFileName));
    			$deploySQLFromFile = substr($contents,0,strpos($contents, '-- //@UNDO'));
    			$sqlToPerformDeploy .= $deploySQLFromFile;
    			$sqlToPerformDeploy .= 'UPDATE ' . DbDeployTask::$TABLE_NAME . ' SET complete_dt = ' . $this->dbmsSyntax->generateTimestamp() . ' WHERE change_number = ' . $fileChangeNumber . ' AND delta_set = \'' . $this->deltaSet . '\';' . "\n";
    			$sqlToPerformDeploy .= '-- Fragment ends: ' . $fileChangeNumber . ' --' . "\n";
    		}
    	}
		return $sqlToPerformDeploy;
    }
    
    function undoDeploy(){
    	$sqlToPerformUndo = '';
    	$lastChangeAppliedInDb = $this->getLastChangeAppliedInDb();    	
    	$files = $this->getDeltasFilesArray();
    	krsort($files);
    	foreach($files as $fileChangeNumber=>$fileName){
    		if($fileChangeNumber > $lastChangeAppliedInDb && $fileChangeNumber <= $this->lastChangeToApply){
				$fullFileName = $this->dir . '/' . $fileName;
    			$fh = fopen($fullFileName, 'r');
    			$contents = fread($fh, 	filesize($fullFileName));
    			$undoSQLFromFile = substr($contents,strpos($contents, '-- //@UNDO')+10);
    			$sqlToPerformUndo .= $undoSQLFromFile;
    			$sqlToPerformUndo .= 'DELETE FROM ' . DbDeployTask::$TABLE_NAME . ' WHERE change_number = ' . $fileChangeNumber . ' AND delta_set = \'' . $this->deltaSet . '\';' . "\n";
    			$sqlToPerformUndo .= '-- Fragment ends: ' . $fileChangeNumber . ' --' . "\n";
    		}
    	}
		return $sqlToPerformUndo;
    }
    
   function getDeltasFilesArray(){
    	$baseDir = realpath($this->dir);
    	$dh = opendir($baseDir);
    	$fileChangeNumberPrefix = '';
    	while(($file = readdir($dh)) !== false){
    		if(preg_match('[\d+]', $file, $fileChangeNumberPrefix)){
    			$files[$fileChangeNumberPrefix[0]] = $file;
    		}
    	}
    	return $files;
    }
    
	function setUrl($url){
		$this->url = $url;
	}
	
	function setUserId($userid){
		$this->userid = $userid;
	}
	
	function setPassword($password){
		$this->password = $password;
	}

	function setDir($dir){
		$this->dir = $dir;
	}

	function setOutputFile($outputFile){
		$this->outputFile = $outputFile;
	}

	function setUndoOutputFile($undoOutputFile){
		$this->undoOutputFile = $undoOutputFile;
	}
	
	function setLastChangeToApply($lastChangeToApply){
		$this->lastChangeToApply = $lastChangeToApply;
	}

	function setDeltaSet($deltaSet){
		$this->deltaSet = $deltaSet;
	}
	
    /**
     * Add a new fileset.
     * @return FileSet
     */
    public function createFileSet() {
        $this->fileset = new FileSet();
        $this->filesets[] = $this->fileset;
        return $this->fileset;
    }
}

