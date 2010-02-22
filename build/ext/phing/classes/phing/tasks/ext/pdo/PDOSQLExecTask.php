<?php
/*
 *  $Id: CreoleSQLExecTask.php 83 2006-07-07 18:17:00Z mrook $
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

require_once 'phing/tasks/ext/pdo/PDOTask.php';
include_once 'phing/system/io/StringReader.php';
include_once 'phing/tasks/ext/pdo/PDOSQLExecFormatterElement.php';

/**
 * Executes a series of SQL statements on a database using PDO.
 *
 * <p>Statements can
 * either be read in from a text file using the <i>src</i> attribute or from 
 * between the enclosing SQL tags.</p>
 * 
 * <p>Multiple statements can be provided, separated by semicolons (or the 
 * defined <i>delimiter</i>). Individual lines within the statements can be 
 * commented using either --, // or REM at the start of the line.</p>
 * 
 * <p>The <i>autocommit</i> attribute specifies whether auto-commit should be 
 * turned on or off whilst executing the statements. If auto-commit is turned 
 * on each statement will be executed and committed. If it is turned off the 
 * statements will all be executed as one transaction.</p>
 * 
 * <p>The <i>onerror</i> attribute specifies how to proceed when an error occurs 
 * during the execution of one of the statements. 
 * The possible values are: <b>continue</b> execution, only show the error;
 * <b>stop</b> execution and commit transaction;
 * and <b>abort</b> execution and transaction and fail task.</p>
 *
 * @author    Hans Lellelid <hans@xmpl.org> (Phing)
 * @author    Jeff Martin <jeff@custommonkey.org> (Ant)
 * @author    Michael McCallum <gholam@xtra.co.nz> (Ant)
 * @author    Tim Stephenson <tim.stephenson@sybase.com> (Ant)
 * @package   phing.tasks.ext
 * @version   $Revision: 1.21 $
 */
class PDOSQLExecTask extends PDOTask {

	/**
	 * Count of how many statements were executed successfully.
	 * @var int
	 */
	private $goodSql = 0;

	/**
	 * Count of total number of SQL statements.
	 * @var int
	 */
	private $totalSql = 0;

	const DELIM_ROW = "row";
	const DELIM_NORMAL = "normal";

    /**
     * Database connection
     * @var PDO
     */
    private $conn = null;

    /**
     * Files to load
     * @var array FileSet[]
     */
    private $filesets = array();

    /**
     * Formatter elements.
     * @var array PDOSQLExecFormatterElement[]
     */
    private $formatters = array();

    /**
     * SQL statement
     * @var PDOStatement
     */
    private $statement;

    /**
     * SQL input file
     * @var PhingFile
     */
    private $srcFile;

    /**
     * SQL input command
     * @var string
     */
    private $sqlCommand = "";

    /**
     * SQL transactions to perform
     */
    private $transactions = array();

    /**
     * SQL Statement delimiter (for parsing files)
     * @var string
     */
    private $delimiter = ";";

    /**
     * The delimiter type indicating whether the delimiter will
     * only be recognized on a line by itself
     */
    private $delimiterType = "normal"; // can't use constant just defined

    /**
     * Action to perform if an error is found
     **/
    private $onError = "abort";

    /**
     * Encoding to use when reading SQL statements from a file
     */
    private $encoding = null;

    /**
     * Fetch mode for PDO select queries.
     * @var int
     */
    private $fetchMode;

    /**
     * Set the name of the SQL file to be run.
     * Required unless statements are enclosed in the build file
     */
    public function setSrc(PhingFile $srcFile) {
    	$this->srcFile = $srcFile;
    }

    /**
     * Set an inline SQL command to execute. 
     * NB: Properties are not expanded in this text.
     */
    public function addText($sql) {
    	$this->sqlCommand .= $sql;
    }

    /**
     * Adds a set of files (nested fileset attribute).
     */
    public function addFileset(FileSet $set) {
    	$this->filesets[] = $set;
    }

    /**
     * Creates a new PDOSQLExecFormatterElement for <formatter> element.
     * @return PDOSQLExecFormatterElement
     */
    public function createFormatter()
    {
    	$fe = new PDOSQLExecFormatterElement($this);
    	$this->formatters[] = $fe;
    	return $fe;
    }

    /**
     * Add a SQL transaction to execute
     */
    public function createTransaction() {
    	$t = new PDOSQLExecTransaction($this);
    	$this->transactions[] = $t;
    	return $t;
    }

    /**
     * Set the file encoding to use on the SQL files read in
     *
     * @param encoding the encoding to use on the files
     */
    public function setEncoding($encoding) {
    	$this->encoding = $encoding;
    }

    /**
     * Set the statement delimiter.
     *
     * <p>For example, set this to "go" and delimitertype to "ROW" for
     * Sybase ASE or MS SQL Server.</p>
     *
     * @param delimiter
     */
    public function setDelimiter($delimiter)
    {
    	$this->delimiter = $delimiter;
    }

    /**
     * Set the Delimiter type for this sql task. The delimiter type takes two
     * values - normal and row. Normal means that any occurence of the delimiter
     * terminate the SQL command whereas with row, only a line containing just
     * the delimiter is recognized as the end of the command.
     *
     * @param string $delimiterType
     */
    public function setDelimiterType($delimiterType)
    {
    	$this->delimiterType = $delimiterType;
    }

    /**
     * Action to perform when statement fails: continue, stop, or abort
     * optional; default &quot;abort&quot;
     */
    public function setOnerror($action) {
    	$this->onError = $action;
    }

    /**
     * Sets the fetch mode to use for the PDO resultset.
     * @param mixed $mode The PDO fetchmode integer or constant name.
     */
    public function setFetchmode($mode) {
    	if (is_numeric($mode)) {
    		$this->fetchMode = (int) $mode;
    	} else {
    		if (defined($mode)) {
    			$this->fetchMode = constant($mode);
    		} else {
    			throw new BuildException("Invalid PDO fetch mode specified: " . $mode, $this->getLocation());
    		}
    	}
    }

    /**
     * Gets a default output writer for this task.
     * @return Writer
     */
    private function getDefaultOutput()
    {
    	return new LogWriter($this);
    }

    /**
     * Load the sql file and then execute it
     * @throws BuildException
     */
    public function main()  {

    	// Set a default fetchmode if none was specified
    	// (We're doing that here to prevent errors loading the class is PDO is not available.)
    	if ($this->fetchMode === null) {
    		$this->fetchMode = PDO::FETCH_BOTH;
    	}

    	// Initialize the formatters here.  This ensures that any parameters passed to the formatter
    	// element get passed along to the actual formatter object
    	foreach($this->formatters as $fe) {
    		$fe->prepare();
    	}

    	$savedTransaction = array();
    	for($i=0,$size=count($this->transactions); $i < $size; $i++) {
    		$savedTransaction[] = clone $this->transactions[$i];
    	}

    	$savedSqlCommand = $this->sqlCommand;

    	$this->sqlCommand = trim($this->sqlCommand);

    	try {
    		if ($this->srcFile === null && $this->sqlCommand === ""
    		&& empty($this->filesets)) {
    			if (count($this->transactions) === 0) {
    				throw new BuildException("Source file or fileset, "
    				. "transactions or sql statement "
    				. "must be set!", $this->location);
    			}
    		}

    		if ($this->srcFile !== null && !$this->srcFile->exists()) {
    			throw new BuildException("Source file does not exist!", $this->location);
    		}

    		// deal with the filesets
    		foreach($this->filesets as $fs) {
    			$ds = $fs->getDirectoryScanner($this->project);
    			$srcDir = $fs->getDir($this->project);
    			$srcFiles = $ds->getIncludedFiles();
    			// Make a transaction for each file
    			foreach($srcFiles as $srcFile) {
    				$t = $this->createTransaction();
    				$t->setSrc(new PhingFile($srcDir, $srcFile));
    			}
    		}

    		// Make a transaction group for the outer command
    		$t = $this->createTransaction();
    		if ($this->srcFile) $t->setSrc($this->srcFile);
    		$t->addText($this->sqlCommand);
    		$this->conn = $this->getConnection();

    		try {

    			$this->statement = null;

    			// Initialize the formatters.
    			$this->initFormatters();

    			try {

    				// Process all transactions
    				for ($i=0,$size=count($this->transactions); $i < $size; $i++) {
    					if (!$this->isAutocommit()) {
    						$this->log("Beginning transaction", Project::MSG_VERBOSE);
    						$this->conn->beginTransaction();
    					}
    					$this->transactions[$i]->runTransaction();
    					if (!$this->isAutocommit()) {
    						$this->log("Commiting transaction", Project::MSG_VERBOSE);
    						$this->conn->commit();
    					}
    				}
    			} catch (Exception $e) {
    				throw $e;
    			}
    		} catch (IOException $e) {
    			if (!$this->isAutocommit() && $this->conn !== null && $this->onError == "abort") {
    				try {
    					$this->conn->rollback();
    				} catch (PDOException $ex) {}
    			}
    			throw new BuildException($e->getMessage(), $this->location);
    		} catch (PDOException $e){
    			if (!$this->isAutocommit() && $this->conn !== null && $this->onError == "abort") {
    				try {
    					$this->conn->rollback();
    				} catch (PDOException $ex) {}
    			}
    			throw new BuildException($e->getMessage(), $this->location);
    		}
    			
    		// Close the formatters.
    		$this->closeFormatters();

    		$this->log($this->goodSql . " of " . $this->totalSql .
                " SQL statements executed successfully");

    	} catch (Exception $e) {
    		$this->transactions = $savedTransaction;
    		$this->sqlCommand = $savedSqlCommand;
    		throw $e;
    	}
    	// finally {
    	$this->transactions = $savedTransaction;
    	$this->sqlCommand = $savedSqlCommand;

    }


    /**
     * read in lines and execute them
     * @throws PDOException, IOException 
     */
    public function runStatements(Reader $reader) {
    	$sql = "";
		$line = "";
		$sqlBacklog = "";
		$hasQuery = false;

		$in = new BufferedReader($reader);

		try {
			while (($line = $in->readLine()) !== null) {
				$line = trim($line);
				$line = ProjectConfigurator::replaceProperties($this->project, $line,
						$this->project->getProperties());

				if (StringHelper::startsWith("//", $line) ||
					StringHelper::startsWith("--", $line) ||
					StringHelper::startsWith("#", $line)) {
					continue;
				}

				if (strlen($line) > 4
						&& strtoupper(substr($line,0, 4)) == "REM ") {
					continue;
				}

				if ($sqlBacklog !== "") {
					$sql = $sqlBacklog;
					$sqlBacklog = "";
				}

				$sql .= " " . $line . "\n";

				// SQL defines "--" as a comment to EOL
				// and in Oracle it may contain a hint
				// so we cannot just remove it, instead we must end it
				if (strpos($line, "--") !== false) {
					$sql .= "\n";
				}

				// DELIM_ROW doesn't need this (as far as i can tell)
				if ($this->delimiterType == self::DELIM_NORMAL) {

					$reg = "#((?:\"(?:\\\\.|[^\"])*\"?)+|'(?:\\\\.|[^'])*'?|" . preg_quote($this->delimiter) . ")#";

					$sqlParts = preg_split($reg, $sql, 0, PREG_SPLIT_DELIM_CAPTURE);
					$sqlBacklog = "";
					foreach ($sqlParts as $sqlPart) {
						// we always want to append, even if it's a delim (which will be stripped off later)
						$sqlBacklog .= $sqlPart;

						// we found a single (not enclosed by ' or ") delimiter, so we can use all stuff before the delim as the actual query
						if ($sqlPart === $this->delimiter) {
							$sql = $sqlBacklog;
							$sqlBacklog = "";
							$hasQuery = true;
						}
					}
				}

				if ($hasQuery || ($this->delimiterType == self::DELIM_ROW && $line == $this->delimiter)) {
					// this assumes there is always a delimter on the end of the SQL statement.
					$sql = StringHelper::substring($sql, 0, strlen($sql) - 1 - strlen($this->delimiter));
					$this->log("SQL: " . $sql, Project::MSG_VERBOSE);
					$this->execSQL($sql);
					$sql = "";
					$hasQuery = false;
				}
			}

			// Catch any statements not followed by ;
			if ($sql !== "") {
				$this->execSQL($sql);
			}
		} catch (PDOException $e) {
			throw $e;
		}
    }

    /**
     * Whether the passed-in SQL statement is a SELECT statement.
     * This does a pretty simple match, checking to see if statement starts with
     * 'select' (but not 'select into').
     * 
     * @param string $sql
     * @return boolean Whether specified SQL looks like a SELECT query.
     */
    protected function isSelectSql($sql)
    {
    	$sql = trim($sql);
    	return (stripos($sql, 'select') === 0 && stripos($sql, 'select into ') !== 0);
    }

    /**
     * Exec the sql statement.
     * @throws PDOException 
     */
    protected function execSQL($sql) {

    	// Check and ignore empty statements
    	if (trim($sql) == "") {
    		return;
    	}

    	try {
    		$this->totalSql++;

    		$this->statement = $this->conn->prepare($sql);
    		$this->statement->execute();
    		$this->log($this->statement->rowCount() . " rows affected", Project::MSG_VERBOSE);

    		// only call processResults() for statements that return actual data (such as 'select')
    		if ($this->statement->columnCount() > 0)
    		{
    			$this->processResults();
    		}

    		$this->statement->closeCursor();
    		$this->statement = null;

    		$this->goodSql++;

    	} catch (PDOException $e) {
    		$this->log("Failed to execute: " . $sql, Project::MSG_ERR);
    		if ($this->onError != "continue") {
    			throw new BuildException("Failed to execute SQL", $e);
    		}
    		$this->log($e->getMessage(), Project::MSG_ERR);
    	}
    }

    /**
     * Returns configured PDOResultFormatter objects (which were created from PDOSQLExecFormatterElement objects).
     * @return array PDOResultFormatter[]
     */
    protected function getConfiguredFormatters()
    {
    	$formatters = array();
    	foreach ($this->formatters as $fe) {
    		$formatters[] = $fe->getFormatter();
    	}
    	return $formatters;
    }

    /**
     * Initialize the formatters.
     */
    protected function initFormatters() {
    	$formatters = $this->getConfiguredFormatters();
    	foreach ($formatters as $formatter) {
    		$formatter->initialize();
    	}

    }

    /**
     * Run cleanup and close formatters.
     */
    protected function closeFormatters() {
    	$formatters = $this->getConfiguredFormatters();
    	foreach ($formatters as $formatter) {
    		$formatter->close();
    	}
    }

    /**
     * Passes results from query to any formatters.
     * @throw PDOException
     */
    protected function processResults() {

    	try {

    		$this->log("Processing new result set.", Project::MSG_VERBOSE);

    		$formatters = $this->getConfiguredFormatters();

	    	while ($row = $this->statement->fetch($this->fetchMode)) {
	    		foreach ($formatters as $formatter) {
	    			$formatter->processRow($row);
	    		}
	    	}

    	} catch (Exception $x) {
    		$this->log("Error processing reults: " . $x->getMessage(), Project::MSG_ERR);
    		foreach ($formatters as $formatter) {
	    		$formatter->close();
	    	}
    		throw $x;
    	}

    }
}

/**
 * "Inner" class that contains the definition of a new transaction element.
 * Transactions allow several files or blocks of statements
 * to be executed using the same JDBC connection and commit
 * operation in between.
 */
class PDOSQLExecTransaction {

	private $tSrcFile = null;
	private $tSqlCommand = "";
	private $parent;

	function __construct($parent)
	{
		// Parent is required so that we can log things ...
		$this->parent = $parent;
	}

	public function setSrc(PhingFile $src)
	{
		$this->tSrcFile = $src;
	}

	public function addText($sql)
	{
		$this->tSqlCommand .= $sql;
	}

    /**
     * @throws IOException, PDOException
     */
    public function runTransaction()
    {
    	if (!empty($this->tSqlCommand)) {
    		$this->parent->log("Executing commands", Project::MSG_INFO);
    		$this->parent->runStatements(new StringReader($this->tSqlCommand));
    	}

    	if ($this->tSrcFile !== null) {
    		$this->parent->log("Executing file: " . $this->tSrcFile->getAbsolutePath(),
    		Project::MSG_INFO);
    		$reader = new FileReader($this->tSrcFile);
    		$this->parent->runStatements($reader);
    		$reader->close();
    	}
    }
}


