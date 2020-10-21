<?php
require_once '../model/VGS_DB_Table.php';
require_once '../model/Code_Values_Master.php';

class Pipe_Type_Master extends VGS_DB_Table
{
    public function __construct($conn) {
    	parent::__construct($conn);
//    	$this->checkPermissionByCategory('PIPE', 'INQUIRY');
    	$this->tableName = 'PIPE_TYPE_MASTER';
		$this->tablePrefix = 'PT_';
	    $this->keyFields = array('PT_PIPE_TYPE');
    	$this->hasAuditFields = true;
    }
     
    public function create( $rec ) {
    	$this->checkPermissionByCategory('PIPE', 'CREATE');
    	return $this->autoCreateRecord($rec);
    }

    public function update( $rec ) {
    	$this->checkPermissionByCategory('PIPE', 'UPDATE');
    	return $this->autoUpdateRecord($rec);
    }	

    public function retrieve( $data ) {
		$select = new VGS_DB_Select();
		$select->from = $this->tableName;
		$select->andWhere("PT_PIPE_TYPE = ?", trim($data['PT_PIPE_TYPE']) );

		$this->execListQuery($select->toString(), $select->parms);
		$row = db2_fetch_assoc( $this->stmt );
		if (is_array($row)) {
			$row['PT_CHANGE_TIME'] = VGS_Form::getTimeStampOutputFormat($row['PT_CHANGE_TIME']);
			$row['PT_CREATE_TIME'] = VGS_Form::getTimeStampOutputFormat($row['PT_CREATE_TIME']);
		}
		return $row;
    }

    public function retrieveById( $pipeTypeCode ) {
    	$data = array('PT_PIPE_TYPE' => $pipeTypeCode);
    	return $this->retrieve($data);
    }

    public function getPipeTypeDescription ( $pipeTypeCode ) {
		$select = new VGS_DB_Select();
		$select->from = $this->tableName;
		$select->andWhere("PT_PIPE_TYPE = ?", trim($pipeTypeCode));

		$this->execListQuery($select->toString(), $select->parms);
		if ( $row = db2_fetch_assoc( $this->stmt )) {
			return $row['PT_DESCRIPTION'];
		} else {
			return '';
		}
    } 
    
    public function buildFilteredSelect(
       	array &$screenData, 
    	VGS_DB_Select $select,
    	VGS_Search_Filter_Group $filter
	) 
	{
		$filter->addFilter('PT_PIPE_TYPE', 'Code');
		$filter->setInputSize('PT_PIPE_TYPE', 2);
		$filter->addFilter('PT_DESCRIPTION', 'Description', 'LIKE');
		$filter->addFilter('PT_CAP_EXP', 'Cap/Exp');
		$filter->addFilter('PT_MATERIAL', 'Material');
		$filter->addFilter('PT_CATEGORY', 'Category');
		$filter->addFilter('PT_COATING', 'Coating');
 
		$cvm = new Code_Values_Master($this->db_conn_obj);
		$cvList = $cvm->getCodeValuesList('PT_MATERIAL', '-- All --');
		$filter->setDropDownList('PT_MATERIAL', $cvList);
		 
		$cvList = $cvm->getCodeValuesList('PT_CATEGORY', '-- All --');
		$filter->setDropDownList('PT_CATEGORY', $cvList);
		 
		$cvList = $cvm->getCodeValuesList('PT_COATING', '-- All --');
		$filter->setDropDownList('PT_COATING', $cvList);
		 
		$cvList = $cvm->getCodeValuesList('PT_CAP_EXP', '-- All --');
		$filter->setDropDownList('PT_CAP_EXP', $cvList);


		if (isset($screenData['WO_TYPE'])) {
			$wotype = $screenData['WO_TYPE'];
			
			if (Workorder_Master::isMain_WO_TYPE($wotype)) {
				$filter->setDefaultValue('PT_CATEGORY', 'M');
			}
			if (Workorder_Master::isService_WO_TYPE($wotype)) {
				$filter->setDefaultValue('PT_CATEGORY', 'S');
			}
		}
		
		if (isset($screenData['WO_PROJECT_NUM'])) {
			$proj = new Project_Master($this->db_conn_obj);
			$projRec = $proj->retrieveById($screenData['WO_PROJECT_NUM']);
			if (isset($projRec)) {
				$filter->setDefaultValue('PT_CAP_EXP', $projRec['PRJ_CAP_EXP_CODE']);
			}
		}
		
		$filter->saveRestoreFilters($screenData);
		
		$select->from = $this->tableName;
		$select->order = 'PT_PIPE_TYPE';
		
		$filter->renderWhere($screenData, $select);
		
    }	
    
}