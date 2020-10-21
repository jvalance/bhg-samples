<?php 
//require_once 'Zend/Db/Select.php';
require_once '../forms/VGS_Form.php';
require_once '../model/Security.php';
require_once '../model/VGS_DB_Conn.php';
require_once '../model/VGS_DB_Conn_Singleton.php';
require_once '../model/VGS_DB_Select.php';
require_once '../common/common_errors.php';

class VGS_DB_Table {
	
//	public static $db_conn_obj;
	public static $db2connection;
	public $stmt;
	private static $sec; // Security object
	
	/**
	 * Name of the database table, specified in UPPERCASE. 
	 * @var string
	 */
    public $tableName;
    
    /**
     * String that prefixes all field names in this table (eg: 'WO_');
     * Used in autoCreate/autoUpdate methods to extract the update
     * fields for this table from the form inputs.
     * @var string
     */
    public $tablePrefix;
    
    /**
     * Array of key field names for this table
     * @var array
     */
    public $keyFields;
    
	/**
	 * Boolean indicating whether this table includes audit fields, 
	 * ie create/change user and timestamp; 
	 * Defaults to true.
	 * @var boolean
	 */
    public $hasAuditFields;
    
    /**
	 * Boolean indicating whether a delete function should be provided, 
	 * allowing physical deletion of records from the database.  
	 * Defaults to false.
	 * @var boolean
	 */
    public $isRecordDeletionAllowed;
    
    /**
     * If set to true, then errors will not be echoed - instead, Exception will be thrown. 
     * @var boolean
     */
    private $isBatchRequest = false;
    
	//------------------------------------------------------------------
	/** 9/2/2011 JGV  
	 * NOTE: $connectObj passed to constructor is no longer relevant.
	 * We will now just store a reference to the singleton connection 
	 * in field db2connection
	 */
    public function __construct($connectObj) {
    	
    	$this->db2connection = VGS_DB_Conn_Singleton::getInstance();
    	$this->hasAuditFields = true;
    	$this->isRecordDeletionAllowed = false;
    	$this->keyFields = array();
   	}
   	
    /**
     * If true is passed, then db2 errors will not be echoed - instead, Exception will be thrown. 
     * @var boolean
     */
   	public function setIsBatchRequest ( $isBatchRequest ) {
		$this->isBatchRequest = $isBatchRequest;   		
   	}
   	
    /**
     * If true is returned, then db2 errors will not be echoed - instead, Exception will be thrown. 
     * @var boolean
     */
   	public function getIsBatchRequest ( ) {
		return $this->isBatchRequest;   		
   	}
   	
   	protected function trap_sql_error($file, $function, $sql_func,
   			$sql_str, $sql_state, $sql_msg, $user_msg = '')
   	{
   		$dispAttrs = "<tt><font face=\"courier\" color=\"red\">";
   		$msg = <<<SQL_MSG
	   		SQL error in PHP file $file; PHP function $function; 
	   		SQL function: $sql_func; SQL state: $sql_state; \n
	   		SQL message: $sql_msg \n
	   		SQL string: $sql_str \n
SQL_MSG;
   		 
   		if (!$this->isBatchRequest) {
   			echo '<span style="font-family: courier-new; color:red">' .$msg. '</span><br>'; 
	   		echo parse_backtrace(debug_backtrace());
   			exit;
   		} else {
			throw new Exception($sql_msg);
		}
   	}
   	
	/**
	* Wrapper function for Security->checkPermissionByCategory(), which instantiates
	* this->sec (Security object) if it is NULL. 
	* @param string $category Category of authorities required
	* @param string $mode Database operation mode (eg, CREATE, UPDATE, INQUIRY, DELETE)
	*/
	public function checkPermissionByCategory( $category, $mode ) {
		if ($this->sec == NULL || !is_a($this->sec, 'Security')) {
			$this->sec = new Security();
		}
		return $this->sec->checkPermissionByCategory( $category, $mode );
	}
		
	// ------------------------------------------------------------------
	public function fetchRow($queryString, $bindParms = array()) {
		$this->stmt = db2_prepare ( $this->db2connection, $queryString ) 
			or $this->trap_sql_error ( __FILE__, __FUNCTION__, 'db2_prepare', $queryString, 
					db2_stmt_error (), db2_stmt_errormsg () );
		
		$result = db2_execute ( $this->stmt, $bindParms ) 
			or $this->trap_sql_error ( __FILE__, __FUNCTION__, 'db2_execute', $queryString, 
					db2_stmt_error (), db2_stmt_errormsg () );
		
		$row = db2_fetch_assoc ( $this->stmt );
		// if (! $row) {
		// $this->trap_sql_error(__FILE__, __FUNCTION__, 'db2_fetch_assoc',
		// $queryString, db2_stmt_error(), db2_stmt_errormsg());
		// //throw new Exception("Could not find record in table
		// {$this->tableName}" . "<p>$queryString");
		// }
		
		return $row;
	} 

	//------------------------------------------------------------------
	public function execListQuery($queryString, $bindParms = array()) {
		$queryString = preg_replace('/(\s+)/', ' ', $queryString);
//		pre_dump($queryString);
//		pre_dump($bindParms);

		$this->stmt = db2_prepare( $this->db2connection, $queryString )
   			or $this->trap_sql_error(__FILE__, __FUNCTION__, 'db2_prepare', 
                     $queryString, db2_stmt_error(), db2_stmt_errormsg());
//		pre_dump($this->stmt);
   		$result = db2_execute ( $this->stmt, $bindParms )  
   			or $this->trap_sql_error(__FILE__, __FUNCTION__, 'db2_execute', 
                     $queryString, db2_stmt_error(), db2_stmt_errormsg());
   		
	   return $result;
	} 

	//------------------------------------------------------------------
	public function execUpdate($queryString, $bindParms = array()) {
// 		pre_dump($queryString);
// 		pre_dump($bindParms);
		if ( $_REQUEST['debug_sql'] == '1') {
// 			$quest = array_fill(0, count($bindParms), '?');
// 			ary_dump($quest);
// 			pre_dump(str_replace($quest, $bindParms, $queryString));
			$sqlstr = $this->getSqlString($queryString, $bindParms);
			pre_dump($sqlstr);
		}
		$this->stmt = db2_prepare( $this->db2connection, $queryString ) 
   			or $this->trap_sql_error(__FILE__, __FUNCTION__, 'db2_prepare', 
                     $queryString, db2_stmt_error(), db2_stmt_errormsg());
		$result = db2_execute ( $this->stmt, $bindParms )  
   			or $this->trap_sql_error(__FILE__, __FUNCTION__, 'db2_execute', 
                     $queryString, db2_stmt_error(), db2_stmt_errormsg());
		
		// Fetch the results
		return $result;
	} 
	
	//------------------------------------------------------------------
	public function getSqlString( $query, $parms ) {
		$res = $query;
		if (is_array($parms) && count($parms) > 0) {
			foreach ($parms as $pval) {
				$res = preg_replace('/\?/', $pval, $res, 1);
			}
		}
		return $res;
	}
	
	//------------------------------------------------------------------
	public function execScrollableListQuery(VGS_DB_Select $select) {   
		$options = 
			array('cursor'=>DB2_SCROLLABLE, 'i5_fetch_only'=>DB2_I5_FETCH_ON);

		$queryString = $select->toString();
		
		$this->stmt = db2_prepare( $this->db2connection, $queryString ) 
   			or $this->trap_sql_error(__FILE__, __FUNCTION__, 'db2_prepare', 
                     $queryString, db2_stmt_error(), db2_stmt_errormsg());
			   		
		db2_set_option($this->stmt, $options, 2);
	 
	  	$result = db2_execute ( $this->stmt, $select->parms )  
   		or $this->trap_sql_error(__FILE__, __FUNCTION__, 'db2_execute', 
   								$queryString, db2_stmt_error(), db2_stmt_errormsg());
	   		   		
	   return $result;	// boolean success or failure
	}
	
	//------------------------------------------------------------------
	/**
	 * Retrieve a scrollable result set for a specified sql query -
	 * This is the same as execScrollableListQuery, except this receives an sql string
	 * to execute plus an array of bind parameters, instead of a VGS_DB_Select object.  
	 * @param string $queryString
	 * @param array $bindParms
	 */
	function execScrollableListQuery_String($queryString, $bindParms = array()) {   
		$options = 
			array('cursor'=>DB2_SCROLLABLE, 'i5_fetch_only'=>DB2_I5_FETCH_ON);

		$this->stmt = db2_prepare( $this->db2connection, $queryString ) 
   			or $this->trap_sql_error(__FILE__, __FUNCTION__, 'db2_prepare', 
                     $queryString, db2_stmt_error(), db2_stmt_errormsg());
			   		
		db2_set_option($this->stmt, $options, 2);
	 
	   	$result = db2_execute ( $this->stmt, $bindParms )  
   			or $this->trap_sql_error(__FILE__, __FUNCTION__, 'db2_execute', 
                     $queryString, db2_stmt_error(), db2_stmt_errormsg());
	   		   		
	   return $result;	// boolean success or failure
	}

	//------------------------------------------------------------------
	public function getRowCount(VGS_DB_Select $select) {
		$queryString = $select->toStringRowCount();
		
		$this->stmt = db2_prepare( $this->db2connection, $queryString ) 
   			or $this->trap_sql_error(__FILE__, __FUNCTION__, 'db2_prepare', 
                     $queryString, db2_stmt_error(), db2_stmt_errormsg());
                     
		$result = db2_execute ( $this->stmt, $select->parms )  
   			or $this->trap_sql_error(__FILE__, __FUNCTION__, 'db2_execute', 
                     $queryString, db2_stmt_error(), db2_stmt_errormsg());
                     
   		$row = db2_fetch_assoc( $this->stmt );
		return $row['ROW_COUNT'];
	}
	
	/**
	 * Automatically build an SQL update statement, based on form values passed
	 * in parm $inputs, and execute the SQL statement; 
	 * This will also automatically handle updating last changed user/timestamp. 
	 * 
	 * @param array $inputs This should be the inputs array from the Zend_Form object. 
	 * @return The result of the db2_execute() call (true=succes, false=failure).
	 */
	public function autoUpdateRecord(array $inputs) 
	{
		$updateFields = array();
		$updateKeys = array();
		
		$pfxLength = strlen($this->tablePrefix);
		
		foreach ($inputs as $inputName => $inputValue) {
			$fieldPrefix = substr($inputName, 0, $pfxLength);
			if ( ($fieldPrefix == $this->tablePrefix)	// Must match table prefix
			&& (strpos($inputName, "{$this->tablePrefix}CHANGE", 0) === false)	// Ignore "Last Changed on/by" fields
			&& (strpos($inputName, "{$this->tablePrefix}CREATE", 0) === false)  // Ignore "Created on/by" fields
			) {
				if(in_array($inputName, $this->keyFields)) {
					$updateKeys[$inputName] = $inputValue;
				} else {
					$updateFields[$inputName] = $inputValue;
				}
			}
		}
		
		// If no fields to update are found, return.
		if (count($updateFields) == 0) return true;
		
		$updateFieldNames = array_keys($updateFields);
		$update_set_flds = implode(' = ?,', $updateFieldNames) . ' = ?';
		
		$updateKeyNames = array_keys($updateKeys);
		$update_set_keys = implode(' = ? AND ', $updateKeyNames) . ' = ?';
		
	   	$sql = "update {$this->tableName} set $update_set_flds ";
	   	if ($this->hasAuditFields) {
	   		$sql .= ", {$this->tablePrefix}CHANGE_USER = ?, {$this->tablePrefix}CHANGE_TIME = current timestamp ";
	   	}
		$sql .= "where $update_set_keys";
		
		$values = $updateFields;
	   	if ($this->hasAuditFields) {
	   		$values["{$this->tablePrefix}CHANGE_USER"] = strtoupper($_SESSION['current_user']);
	   	} 
	    $values = array_merge($values, $updateKeys);

// 		pre_dump($sql);
// 		ary_dump($values);

	    $this->log_field_changes( $updateKeys, $updateFields );
		$res = $this->execUpdate($sql, $values);
	    return $res;
	}
	/**
	 * Automatically build an SQL delete statement, based on form values passed
	 * in parm $inputs, and execute the SQL statement; 
	 * 
	 * @param array $inputs This should be the inputs array from the Zend_Form object. 
	 * @return The result of the db2_execute() call (true=success, false=failure). 
	 */ 
	public function autoDeleteRecord(array $inputs) 
	{
		$updateFields = array();
		$updateKeys = array();
		
		$pfxLength = strlen($this->tablePrefix);
		
		foreach ($inputs as $inputName => $inputValue) {
			$fieldPrefix = substr($inputName, 0, $pfxLength);
			if ( ($fieldPrefix == $this->tablePrefix)	// Must match table prefix
			) {
				if(in_array($inputName, $this->keyFields)) {
					$updateKeys[$inputName] = $inputValue;
				}
			}
		}
		
		$updateKeyNames = array_keys($updateKeys);
		$update_set_keys = implode(' = ? AND ', $updateKeyNames) . ' = ?';
		
	   	$sql = "delete from {$this->tableName} where $update_set_keys";
		
//		pre_dump($sql);
//		pre_dump($values);
//	   	exit;
	   	$this->write_DB_Update_Log($this->tableName, $updateKeys, '*RECORD', 
	   								'', '** DELETED **', 'DELETE');
	    return $this->execUpdate($sql, $updateKeys);
	}
	
	/**
	 * Automatically build an SQL create statement, based on form values passed
	 * in parm $inputs, and execute the SQL statement; 
	 * This will also automatically handle updating last changed user/timestamp. 
	 * 
	 * @param array $inputs This should be the inputs array from the Zend_Form object. 
	 * @return The result of the db2_execute() call (true=succes, false=failure).
	 */
	public function autoCreateRecord(array $inputs) {
		
		$updateFields = array();
		$updateKeys = array();
		$pfxLength = strlen($this->tablePrefix);
		
		foreach ($inputs as $inputName => $inputValue) {
			$fieldPrefix = substr($inputName, 0, $pfxLength);
			if ( ($fieldPrefix == $this->tablePrefix)	// Must match table prefix
			&& (strpos($inputName, "{$this->tablePrefix}CHANGE", 0) === false)	// Ignore "Last Changed on/by" fields
			&& (strpos($inputName, "{$this->tablePrefix}CREATE", 0) === false)  // Ignore "Created on/by" fields
			) {
				if(in_array($inputName, $this->keyFields)) {
					$updateKeys[$inputName] = $inputValue;
				} 
				$updateFields[$inputName] = $inputValue;
			}
		}

		$updateFieldNames = array_keys($updateFields);
		$update_set_flds = implode(',', $updateFieldNames);
		
		if ($this->hasAuditFields) {
			$update_set_flds .= ", {$this->tablePrefix}CREATE_USER, {$this->tablePrefix}CREATE_TIME "; 
	   	}
		
	   	$sql = "insert into {$this->tableName} ($update_set_flds";
	   	
	   	// Add parameter markers (i.e.: question marks) to the values clause 
	   	$parmCount = count($updateFields);
	   	$parmMarkerArray = array_fill(1, $parmCount, '?');
	   	$sql .= ") values (" . implode(',', $parmMarkerArray);
		
		if ($this->hasAuditFields) {
			$user = strtoupper($_SESSION['current_user']);
			$sql .= ", '$user', current timestamp";
	   	}
	   	$sql .= ") ";
		
		$values = $updateFields;

// 		pre_dump($sql);
// 		pre_dump($values);
		
		$this->write_DB_Update_Log($this->tableName, $updateKeys, '*RECORD',
				'', '** CREATED **', 'CREATE');
		return $this->execUpdate($sql, $values);
	    
	}
	
	protected function log_field_changes( $keys, $new_values) {
		$tableName = $this->tableName;
		$key_names = array_keys($keys);
		$keys_where = implode(' = ? AND ', $key_names) . ' = ?';
		$sql = "select * from {$this->tableName} where $keys_where";

		static $objTable ;
		if ($objTable == NULL) $objTable = new VGS_DB_Table();
		
		if ($objTable->execListQuery($sql, $keys)) {
			$old_values = db2_fetch_assoc($objTable->stmt);
			foreach ($old_values as $db_field => $old_value) {
				if (array_key_exists($db_field, $new_values)) {
					$new_value = trim($new_values[$db_field]);
					$old_value = trim($old_value);
					if ($new_value != $old_value) {
						$this->write_DB_Update_Log(
								$tableName, $keys, $db_field, $old_value, $new_value, 'UPDATE');
					} 
				}
			}
		}
	}
	
	protected function write_DB_Update_Log(
			$tableName, $keys, $db_field, $old_value, $new_value, $action 
	) {
		$sequence_select = "(select (ifnull(max(DBL_REC_SEQ),0)+1) from DB_UPDATE_LOG " .
				"where DBL_TABLE_NAME = ? and DBL_KEY_FIELDS = ?)";
		$sql = "INSERT INTO DB_UPDATE_LOG (DBL_TABLE_NAME, DBL_KEY_FIELDS, DBL_REC_SEQ, DBL_ACTION, " .
				"DBL_UPD_TIMESTAMP, DBL_FIELD_CHANGED, DBL_UPD_USER, DBL_VALUE_BEFORE, DBL_VALUE_AFTER) ".
				"VALUES(?, ?, $sequence_select, '$action', CURRENT TIMESTAMP, ?, ?, ?, ?)";
		
		$json_keys = json_encode($keys, JSON_FORCE_OBJECT);
		$user = $_SESSION['current_user'];
		$bind_params = array($tableName, $json_keys, $tableName, $json_keys, 
							$db_field, $user, $old_value, $new_value);
		
		static $objTable ;
		if ($objTable == NULL) $objTable = new VGS_DB_Table();
		$result = $objTable->execUpdate($sql, $bind_params);
	} 
	
}

