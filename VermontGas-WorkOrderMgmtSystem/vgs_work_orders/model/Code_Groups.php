<?php
require_once '../model/VGS_DB_Table.php';
require_once '../model/Code_Values_Master.php';

class Code_Groups extends VGS_DB_Table
{
	public static $statusCodes = array('' => '*All', 'ACT' => 'Active', 'INA' => 'Inactive');
	
    public function __construct($conn) {
    	parent::__construct($conn);
    	$this->tableName = 'Code_Groups';
		$this->tablePrefix = 'CG_';
	   $this->keyFields = array('CG_GROUP');
    	$this->hasAuditFields = true;
    	$this->isRecordDeletionAllowed = true;
    }

	/**
	 * Retrieve a single record from CODE_GROUPS table by key
	 * @param string $group
	 */
    public function retrieve ( $group ) {
		$select = new VGS_DB_Select();
		$select->from = $this->tableName;
		$select->andWhere("trim(CG_GROUP) = ?", trim($group));
		$this->execListQuery($select->toString(), $select->parms);
		$rec = db2_fetch_assoc( $this->stmt );
		if (is_array($rec)) {
			$rec['CG_CHANGE_TIME'] = VGS_Form::getTimeStampOutputFormat($rec['CG_CHANGE_TIME']);
			$rec['CG_CREATE_TIME'] = VGS_Form::getTimeStampOutputFormat($rec['CG_CREATE_TIME']);
		}
		return $rec;
    }
     
    public function create( $rec ) {
    	$this->checkPermissionByCategory('DD', 'CREATE');
    	return $this->autoCreateRecord($rec);
    }

    public function update( $rec ) {
    	$this->checkPermissionByCategory('DD', 'UPDATE');
    	return $this->autoUpdateRecord($rec);
    }	

    public function delete( $rec ) {
    	$this->checkPermissionByCategory('DD', 'DELETE');
    	return $this->autoDeleteRecord($rec);
    }	
    
	/**
	 * Insert a record in the CODE_GROUPS table
	 * @param array $rec
	 */
//    public function create( $rec ) {
//    	$this->checkPermissionByCategory('DD', 'CREATE');
//    	
//    	$sql = <<<CREATE_CodeGroup
//    		insert into {$this->tableName} (
//    			CG_GROUP, 
//    			CG_DESCRIPTION, 
//    			CG_STATUS, 
//    			CG_SEQUENCE,
//    			CG_CREATE_USER,
//    			CG_CREATE_TIME 
//    		) values(
//    			?, ?, ?, ?, ?, current timestamp 
//    		) 
//CREATE_CodeGroup;
//
//    	$values = array(
//    		$rec['CG_GROUP'],
//    		$rec['CG_DESCRIPTION'],
//    		$rec['CG_STATUS'], 
//    		(int) $rec['CG_SEQUENCE'],
//    		$_SESSION['current_user']
//    	);
//    	
//    	return $this->execUpdate($sql, $values);
//    }

    /**
     * Update a record in the CODE_GROUPS table
     * @param array $rec
     */
//    public function update( $rec ) {
//    	$this->checkPermissionByCategory('DD', 'UPDATE');
//    	
//    	$sql = <<<UPDATE_CodeGroup
//    		update {$this->tableName}
//    		set CG_DESCRIPTION = ?, 
//    			CG_STATUS = ?, 
//    			CG_SEQUENCE = ?,
//    			CG_CHANGE_USER = ?, 
//    			CG_CHANGE_TIME = current timestamp
//    		where CG_GROUP = ?
//UPDATE_CodeGroup;
//
//    	$values = array(
//    		$rec['CG_DESCRIPTION'],
//    		$rec['CG_STATUS'], 
//    		$rec['CG_SEQUENCE'],
//    		$_SESSION['current_user'], 
//    		$rec['CG_GROUP']
//    	);
//    	
//    	return $this->execUpdate($sql, $values);
//    }
    
    /**
     * Build VGS_DB_Select object for retrieving a filtered search result 
     * on the CODE_GROUPS table.
     * @param array reference $screenData
     * @param VGS_DB_Select $sel
     */
    public function buildFilteredSelect(
    	&$screenData, 
    	VGS_DB_Select $sel, 
    	VGS_Search_Filter_Group $filter
    ) {
    	$cvm = new Code_Values_Master($this->conn);

    	$filter->addFilter('CG_GROUP', 'List ID', 'LIKE');
    	$filter->setInputSize('CG_GROUP', 10);
    	$filter->setUpperCase('CG_GROUP');

    	$cvList = $cvm->getCodeValuesList('RECSTATUS', 'All');
    	$filter->addFilter('CG_STATUS', 'Record Status');
		$filter->setDropDownList('CG_STATUS', $cvList);

    	$filter->addFilter('CG_DESCRIPTION', 'Description', 'LIKE');
    	$filter->setInputSize('CG_DESCRIPTION', 25);

    	$filter->saveRestoreFilters($screenData);
    	 
		$sel->from = "{$this->tableName} as cg";
		$sel->columns = "cg.*, (select count(*) 
								from Code_Values_Master as cv 
								where cg.CG_GROUP = cv.CV_GROUP) 
								as VALUES_COUNT";
		$sel->order = 'CG_SEQUENCE, CG_CREATE_TIME DESC';

		$filter->renderWhere($screenData, $sel);
		
// 		// CG_GROUP Filter
// 		if (isset($screenData['filter_CG_GROUP']) 
// 			&& (trim($screenData['filter_CG_GROUP']) != '')) 
// 		{
// 	    	$sel->andWhere('CG_GROUP = ?', trim($screenData['filter_CG_GROUP']));
// 		}

// 		// CG_STATUS Filter
// 		if (isset($screenData['filter_CG_STATUS']) 
// 			&& (trim($screenData['filter_CG_STATUS']) != '')) 
// 		{
// 	    	$sel->andWhere('CG_STATUS = ?', trim($screenData['filter_CG_STATUS']));
// 		}
		
// 		// CG_DESCRIPTION Filter
// 		if (isset($screenData['filter_CG_DESCRIPTION']) 
// 			&& trim($screenData['filter_CG_DESCRIPTION']) <> '') 
// 		{
// 	    	$sel->andWhere("lower(trim(CG_DESCRIPTION)) LIKE lower('%" . trim($screenData['filter_CG_DESCRIPTION']) . "%') ");
// 		}
		
		//	pre_dump($sel->toString());
		//	pre_dump($sel->parms);
    }	    
}