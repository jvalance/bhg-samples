<?php 
namespace polarbev;

require_once 'DB_Select.php';
require_once 'autoloader.php';

class DB_Table {
	
	public static $db2connection;
	public $stmt;
	
	/**
	 * Name of the database table, specified in UPPERCASE. 
	 * @var string
	 */
    public $tableName;
    
	//------------------------------------------------------------------
	/** 9/2/2011 JGV  
	 * NOTE: $connectObj passed to constructor is no longer relevant.
	 * We will now just store a reference to the singleton connection 
	 * in field db2connection
	 */
    public function __construct( ) {
    	$this->db2connection = DB_Conn::getInstance();
   	}

   	protected function trap_sql_error(
   			$file, $function, $sql_func,
   			$sql_str, $sql_state, $sql_msg, $sql_parms = array())
   	{

   		//Construct full log message - disabled for now  
   		$msg = <<<SQL_MSG
	   		SQL error in PHP file $file; PHP function $function;
	   		SQL function: $sql_func; SQL state: $sql_state; \n
	   		SQL message: $sql_msg \n
	   		SQL string: $sql_str \n
SQL_MSG;
   		throw new \Exception($sql_msg . "\n" . $msg);
   		 
   	}
   	
	public function fetchRow($queryString, $bindParms = array()) {
		$this->stmt = db2_prepare ( $this->db2connection, $queryString ) 
			or $this->trap_sql_error ( __FILE__, __FUNCTION__, 'db2_prepare', $queryString, 
					db2_stmt_error (), db2_stmt_errormsg (), $bindParms );
		
		$result = db2_execute ( $this->stmt, $bindParms ) 
			or $this->trap_sql_error ( __FILE__, __FUNCTION__, 'db2_execute', $queryString, 
					db2_stmt_error (), db2_stmt_errormsg (), $bindParms );
		
		$row = db2_fetch_assoc ( $this->stmt );
		
		return $row;
	} 

	public function execListQuery($queryString, $bindParms = array()) {
		$queryString = preg_replace('/(\s+)/', ' ', $queryString);
// 		pre_dump($queryString);
// 		pre_dump($bindParms);

		$this->stmt = db2_prepare( $this->db2connection, $queryString )
   			or $this->trap_sql_error(__FILE__, __FUNCTION__, 'db2_prepare', 
                     $queryString, db2_stmt_error(), db2_stmt_errormsg(), $bindParms);
//		pre_dump($this->stmt);
   		$result = db2_execute ( $this->stmt, $bindParms )  
   			or $this->trap_sql_error(__FILE__, __FUNCTION__, 'db2_execute', 
                     $queryString, db2_stmt_error(), db2_stmt_errormsg(), $bindParms);
   		
	   return $result;
	} 

	public function execUpdate($queryString, $bindParms = array()) {
// 		pre_dump($queryString);
// 		pre_dump($bindParms);
		$this->stmt = db2_prepare( $this->db2connection, $queryString ) 
   			or $this->trap_sql_error(__FILE__, __FUNCTION__, 'db2_prepare', 
                     $queryString, db2_stmt_error(), db2_stmt_errormsg(), $bindParms);
		$result = db2_execute ( $this->stmt, $bindParms )  
   			or $this->trap_sql_error(__FILE__, __FUNCTION__, 'db2_execute', 
                     $queryString, db2_stmt_error(), db2_stmt_errormsg(), $bindParms);
		
		// Fetch the results
		return $result;
	} 
	
	function execScrollableListQuery(DB_Select $select) {   
		$options = 
			array('cursor'=>DB2_SCROLLABLE, 'i5_fetch_only'=>DB2_I5_FETCH_ON);

		$queryString = $select->toString();
		
		$this->stmt = db2_prepare( $this->db2connection, $queryString ) 
   			or $this->trap_sql_error(__FILE__, __FUNCTION__, 'db2_prepare', 
                     $queryString, db2_stmt_error(), db2_stmt_errormsg(), $select->parms);
			   		
		db2_set_option($this->stmt, $options, 2);
	 
	  	$result = db2_execute ( $this->stmt, $select->parms )  
   		or $this->trap_sql_error(__FILE__, __FUNCTION__, 'db2_execute', 
   								$queryString, db2_stmt_error(), db2_stmt_errormsg(), $select->parms);
	   		   		
	   return $result;	// boolean success or failure
	}
	
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
                     $queryString, db2_stmt_error(), db2_stmt_errormsg(), $bindParms );
			   		
		db2_set_option($this->stmt, $options, 2);
	 
	   	$result = db2_execute ( $this->stmt, $bindParms )  
   			or $this->trap_sql_error(__FILE__, __FUNCTION__, 'db2_execute', 
                     $queryString, db2_stmt_error(), db2_stmt_errormsg(), $bindParms );
	   		   		
	   return $result;	// boolean success or failure
	}

	function getRowCount(DB_Select $select) {
		$queryString = $select->toStringRowCount();
		
		$this->stmt = db2_prepare( $this->db2connection, $queryString ) 
   			or $this->trap_sql_error(__FILE__, __FUNCTION__, 'db2_prepare', 
                     $queryString, db2_stmt_error(), db2_stmt_errormsg(), $select->parms);
                     
		$result = db2_execute ( $this->stmt, $select->parms )  
   			or $this->trap_sql_error(__FILE__, __FUNCTION__, 'db2_execute', 
                     $queryString, db2_stmt_error(), db2_stmt_errormsg(), $select->parms);
                     
   		$row = db2_fetch_assoc( $this->stmt );
		return $row['ROW_COUNT'];
	}
	
}

