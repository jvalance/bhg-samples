<?php
require_once '../model/VGS_DB_Table.php';
require_once '../model/Code_Values_Master.php';

class Project_MCF_Rates extends VGS_DB_Table
{
    public function __construct($conn) {
    	parent::__construct($conn);
    	$this->tableName = 'PROJECT_MCF_RATES';
    	$this->tablePrefix = 'PM_';
	    $this->keyFields = array('PM_PRJ_NUM', 'PM_EST_YEAR', 'PM_RATE_CLASS');
    	$this->hasAuditFields = FALSE;
    }
     
    public function create( $rec ) {
    	$this->checkPermissionByCategory('PROJ', 'CREATE');
    	return $this->autoCreateRecord($rec);
    }

    public function update( $rec ) {
    	$this->checkPermissionByCategory('PROJ', 'UPDATE');
    	return $this->autoUpdateRecord($rec);
    }	

    public function delete( $rec ) {
    	$this->checkPermissionByCategory('PROJ', 'UPDATE');
    	return $this->autoDeleteRecord($rec);
    }	
    
    public function retrieve( $data ) {
		$select = new VGS_DB_Select();
		$select->from = $this->tableName;
		$select->andWhere("PM_PRJ_NUM = ?", trim($data['PM_PRJ_NUM']) );
		$select->andWhere("PM_EST_YEAR  = ?", trim($data['PM_EST_YEAR']) );
		$select->andWhere("PM_RATE_CLASS = ?", trim($data['PM_RATE_CLASS']) );

		$this->execListQuery($select->toString(), $select->parms);
		if ( $row = db2_fetch_assoc( $this->stmt )) {
			return $row;
		} else {
			return NULL;
		}
    }

    public function retrieveByKey( $projectNum, $estYear, $rateClass ) {
		$select->andWhere("PM_PRJ_NUM = ?", trim($projectNum) );
		$select->andWhere("PM_EST_YEAR  = ?", trim($estYear) );
		$select->andWhere("PM_RATE_CLASS  = ?", trim($rateClass) );
		return $this->retrieve($data);
    }
    
//    public function buildFilteredSelect(
//      array &$screenData, 
//    	VGS_DB_Select $select,
//    	VGS_Search_Filter_Group $filter
//	) 
//	{
//		$filter->addFilter('PRJ_NUM', 'Proj#');
//		$filter->setInputSize('PRJ_NUM', 3);
//		$filter->addFilter('PRJ_DESCRIPTION', 'Description', 'LIKE');
//		$filter->setInputSize('PRJ_DESCRIPTION', 25);
//		$filter->addFilter('PRJ_STATUS', 'Status');
//		$filter->addFilter('PRJ_CONTACT_PERSON', 'Contact', 'LIKE');
//		$filter->addFilter('PRJ_FEASABILITY_NUM', '<br>Feas#');
//		$filter->setInputSize('PRJ_FEASABILITY_NUM', 3);
//		$filter->addFilter('PRJ_MUNICIPALITY_CODE', 'Town');
//		$filter->addFilter('PRJ_ZONE', 'Zone');
//		$filter->setInputSize('PRJ_ZONE', 3);
//		$filter->addFilter('PRJ_CAP_EXP_CODE', 'Cap/Exp');
//		
//		$cvm = new Code_Values_Master($this->db_conn_obj);
//		$cvList = $cvm->getCodeValuesList('TOWN', '-- All --');
//		$filter->setDropDownList('PRJ_MUNICIPALITY_CODE', $cvList);
//		 
//		$cvList = $cvm->getCodeValuesList('PRJ_STATUS', '-- All --');
//		$filter->setDropDownList('PRJ_STATUS', $cvList);
//		 
//		$cvList = $cvm->getCodeValuesList('PT_CAP_EXP', '-- All --');
//		$filter->setDropDownList('PRJ_CAP_EXP_CODE', $cvList);
//
//		if (isset($screenData['PT_CAP_EXP'])) {
//			$filter->setDefaultValue('PRJ_CAP_EXP_CODE', $screenData['PT_CAP_EXP']);
//		}
//		
//		$filter->saveRestoreFilters($screenData);
//		
//		$select->from = $this->tableName;
//		$select->order = 'PRJ_NUM DESC';
//		
//		$filter->renderWhere($screenData, $select);
//		
//    }	
    
}