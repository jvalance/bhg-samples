<?php 

class VGS_DB_Select {
	public $columns;
	public $from;
	public $joins;
	public $where;
	public $parms = array();
	public $order;
	public $having;
	
	/**
	 * @param boolean $dummy If true, add dummy 1st column to workaround db2_fetch_assoc bug. Default is true.
	 * @return string
	 */
	// TODO: DUMMYZEND71 - Remove temporary code
	public function toString($dummy = true) {
		if ($dummy) {
			/*
			 * Add a dummy field as the first column to get around bug in db2_fetch_assoc
			 * during upgrade to v7.1
			 */
			$tokens = explode(' ', trim($this->from));
			if (count($tokens) == 1) {
				// 1 token = No table alias specified
				// ie: $this->from == 'TABLE'
				$table = $tokens[0];
			} elseif (count($tokens) == 2) {
				// 2 tokens = table alias specified without 'AS' keyword
				// ie: $this->from == 'TABLE tbl'
				$table = $tokens[1]; 
			} elseif (count($tokens) == 3) {
				// 3 tokens = table alias specified with 'AS' keyword
				// ie: $this->from == 'TABLE as tbl'
				$table = $tokens[2];
			}
			$cols = " 0 as dummyzend71, $table.* ";
			if (trim($this->columns) != '') $cols = ' 0 as dummyzend71, ' . $this->columns;
		} else {
			$cols = " * ";
			if (trim($this->columns) != '') $cols = $this->columns;
		}
		$string = "Select {$cols} FROM {$this->from} ";
		
		if (trim($this->joins) != '')
			$string .= " $this->joins ";
		
		if (trim($this->where) != '')
			$string .= " WHERE $this->where ";
			
		if (trim($this->having) != '')
			$string .= " HAVING $this->having ";
			
		if (trim($this->order) != '')
			$string .= " ORDER BY $this->order ";
			
		return $string;
	}
	
	public function toStringRowCount($name = 'ROW_COUNT') {
		$string = "Select COUNT(*) as $name FROM {$this->from} ";
		
		if (trim($this->joins) != '') 
			$string .= " $this->joins ";
		
		if (trim($this->where) != '') 
			$string .= " WHERE $this->where ";
			
		return $string;
	}


	public function where( $whereCondition, $logicalOper, $value = NULL ) {
		if ($logicalOper == 'or'){
			$this->orWhere($whereCondition, $value);
		} elseif ($logicalOper == 'and'){
			$this->andWhere($whereCondition, $value);
		} else {
			throw new Exception('Invalid logical operator passed to ' . __FUNCTION__ . ": $logicalOper. Value must be 'and' or 'or'.");
		}  
	}
	
	public function andWhere( $whereCondition, $value = NULL ) {
		if ($this->where == '') {
			$this->where = " ($whereCondition) ";
		} else {
			$this->where .= " AND ($whereCondition) ";
		}
		if (isset($value)) {
			if (is_array($value)) {
				$this->parms = array_merge($this->parms, $value);
			} else {
				$this->parms[] = $value;
			}
		}
	}
	
	public function orWhere( $whereCondition, $value = NULL ) {
		if ($this->where == '') {
			$this->where = " ($whereCondition) ";
		} else {
			$this->where .= " OR ($whereCondition) ";
		}
		if (isset($value)) {
			if (is_array($value)) {
				$this->parms = array_merge($this->parms, $value);
			} else {
				$this->parms[] = $value;
			}
		}
	}
	
}
//--------------------------------------------------------
/** 
 * 
 * @author John
 *
 */
class VGS_DB_Procedure extends VGS_DB_Select {
	private $storedProcName;
	
	public function __construct($storedProc) {
		$this->storedProcName = $storedProc;
	}
	
	function addStoredProcParm( $value ) {
		$this->parms[] = $value;
	}
	
	function toString() {
		$string = "call {$this->storedProcName}(";
		$separ = '';
		foreach ($this->parms as $parm) {
			$string .= $separ . '?';
			$separ = ', ';
		}
		$string .= ')';
		return $string;
	}
}
