<?php
require_once '../model/VGS_DB_Table.php';
require_once '../forms/VGS_Form.php';
require_once '../model/Code_Values_Master.php';

class Authorities extends VGS_DB_Table
{
    public function __construct($conn) {
    	parent::__construct($conn);
//		$this->checkPermissionByCategory('SEC', 'INQUIRY');
    	$this->tableName = 'AUTHORITIES';
		$this->tablePrefix = 'AD_';
	    $this->keyFields = array('AD_AUTH_ID');
    	$this->hasAuditFields = true;
    }
     
    public function create( $rec ) {
    	$this->checkPermissionByCategory('SEC', 'CREATE');
    	return $this->autoCreateRecord($rec);
    }


    public function update( $rec ) {
    	$this->checkPermissionByCategory('SEC', 'UPDATE');
    	return $this->autoUpdateRecord($rec);
    }	

    public function delete( $rec ) {
    	$this->checkPermissionByCategory('SEC', 'DELETE');
    	return $this->autoDeleteRecord($rec);
    }	


    public function retrieveByID( $authID ) {
    	$data['AD_AUTH_ID'] = trim($authID);
    	return $this->retrieve($data);
    }

    public function retrieve( $data ) {
		$select = new VGS_DB_Select();
		$select->from = $this->tableName;
		$select->andWhere("AD_AUTH_ID = ?", trim($data['AD_AUTH_ID']) );

		$this->execListQuery($select->toString(), $select->parms);
		$row = db2_fetch_assoc( $this->stmt );
		if (is_array($row)) {
			$row['AD_CHANGE_TIME'] = VGS_Form::getTimeStampOutputFormat($row['AD_CHANGE_TIME']);
			$row['AD_CREATE_TIME'] = VGS_Form::getTimeStampOutputFormat($row['AD_CREATE_TIME']);
		}
		return $row;
    }
    
    public function buildFilteredSelect(
       	array &$screenData, 
    	VGS_DB_Select $select,
    	VGS_Search_Filter_Group $filter
	) 
	{
		$filter->addFilter('AD_AUTH_ID', 'Auth ID', 'LIKE');
		$filter->setInputSize('AD_AUTH_ID', 5);
		$filter->setUpperCase('AD_AUTH_ID');
		
		$filter->addFilter('AD_AUTH_NAME', 'Auth Name', 'LIKE');
		$filter->setInputSize('AD_AUTH_NAME', 15);
		
		$filter->addFilter('AD_DESCRIPTION', 'Description', 'LIKE');
		$filter->setInputSize('AD_DESCRIPTION', 25);
		
		$filter->addFilter('AD_FUNCTIONAL_AREA', 'Func. Area');
		
//		$dd = new Code_Values_Master($this->db_conn_obj);
//		$ddList = $cvm->getCodeValuesList('AD_FUNCTIONAL_AREA', '-- All --');
//		$filter->setDropDownList('AD_FUNCTIONAL_AREA', $ddList);
		
		$filter->saveRestoreFilters($screenData);
		
		$select->from = $this->tableName;
		$select->order = 'AD_AUTH_ID';
		
		$filter->renderWhere($screenData, $select);
		
    }	
    
}