<?php 
namespace polarbev;

class DB_Select {
	public $columns;
	public $from;
	public $joins;
	public $where;
	public $parms = array();
	public $order;
	public $having;
	/**
	 * $distinct - If true, SELECT DISTINCT will be used.
	 * @var boolean
	 */
	public $distinct = false;

	function toString() {
		$cols = " * ";
		$distict = '';
		
		if (trim($this->columns) != '') $cols = $this->columns;
		if ($this->distinct) $distict = 'DISTINCT'; 
		
		$string = "SELECT {$distict} {$cols} FROM {$this->from} ";
		
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
	
	function toStringRowCount($name = 'ROW_COUNT') {
		$string = "Select COUNT(*) as $name FROM {$this->from} ";
		
		if (trim($this->joins) != '') 
			$string .= " $this->joins ";
		
		if (trim($this->where) != '') 
			$string .= " WHERE $this->where ";
			
		return $string;
	}
	
	function andWhere( $whereCondition, $value = NULL ) {
		if ($this->where == '') {
			$this->where = " ($whereCondition) ";
		} else {
			$this->where .= " AND ($whereCondition) ";
		}
		
		if (isset($value)) {
			if (is_array($value))
				$this->parms = array_merge($this->parms, $value);
			else
				$this->parms[] = $value;
		}
			
	}
	
}
//--------------------------------------------------------
class DB_Procedure extends DB_Select {
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
