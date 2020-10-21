<?php
require_once '../model/VGS_DB_Table.php';
require_once '../model/Code_Values_Master.php';

class Plastic_Pipe_Fail extends VGS_DB_Table
{
    public function __construct($conn) {
    	parent::__construct($conn);
    	$this->tableName = 'PLASTIC_PIPE_FAIL';
		$this->tablePrefix = 'PP_';
	   $this->keyFields = array('PP_WONUM');
    	$this->hasAuditFields = true;
    }
     
    public function create( $rec ) {
    	$this->checkPermissionByCategory('WO', 'CREATE');
    	return $this->autoCreateRecord($rec);
    }

    public function update( $rec ) {
    	$this->checkPermissionByCategory('WO', 'UPDATE');
    	return $this->autoUpdateRecord($rec);
    }	

    public function retrieve( $data ) {
    	$this->checkPermissionByCategory('WO', 'INQUIRY');
    	$select = new VGS_DB_Select();
		$select->from = $this->tableName . ' as pp';
		$select->columns = 'pp.*, wo.*, cv1.cv_value as WO_TYPE_DESC';
		$select->joins = 'left join WorkOrder_Master as wo on pp_wonum = wo_num
						  left join Code_Values_Master as cv1 on cv_group = \'WO_TYPE\' and cv_value = wo_type ';
		$select->andWhere("PP_WONUM = ?", trim($data['PP_WONUM']) );

		$this->execListQuery($select->toString(), $select->parms);
		$row = db2_fetch_assoc( $this->stmt );
		if (is_array($row)) {
			$row['PP_CHANGE_TIME'] = VGS_Form::getTimeStampOutputFormat($row['PP_CHANGE_TIME']);
			$row['PP_CREATE_TIME'] = VGS_Form::getTimeStampOutputFormat($row['PP_CREATE_TIME']);
		}
//		pre_dump($row);
		return $row;
    }

    public function retrieveByWONum( $woNum ) {
    	$data = array('PP_WONUM' => $woNum );
    	return $this->retrieve( $data );
    }
    
    public function exists_PP_Failure( $ppwonum ) {
		$select = new VGS_DB_Select();
		$select->from = $this->tableName;
		$select->andWhere("PP_WONUM = ?", trim($ppwonum));

		$this->execListQuery($select->toString(), $select->parms);
		$row = db2_fetch_assoc( $this->stmt );
		if ( isset($row) && is_array($row)) {
			return true;
		} else {
			return false;
		}
    } 
    
    public function getPPSearchSelect(
       	array &$screenData, 
    	VGS_DB_Select $select,
    	VGS_Search_Filter_Group $filter
	   	) 
	{
    	$filter->saveRestoreFilters($screenData);
		
		$select->from = $this->tableName;
		$select->joins= 'left join WORKORDER_MASTER on PP_WONUM = WO_NUM ';
		$select->order = 'PP_WONUM';
		
		$filter->renderWhere($screenData, $select);
	}	
    
}