<?php
require_once '../model/VGS_DB_Table.php';
require_once '../model/Code_Values_Master.php';

class Mechanical_Fitting_Failure extends VGS_DB_Table
{
    public function __construct($conn) {
    	parent::__construct($conn);
    	$this->tableName = 'MECH_FIT_FAIL';
		$this->tablePrefix = 'MF_';
	    $this->keyFields = array('MF_WONUM');
    	$this->hasAuditFields = true;
    }
     
    public function create( $rec ) {
    	return $this->autoCreateRecord($rec);
    }

    public function update( $rec ) {
    	return $this->autoUpdateRecord($rec);
    }	
    
    public function retrieve( $data ) {
		$this->checkPermissionByCategory('WO', 'INQUIRY');
    	$select = new VGS_DB_Select();
		$select->from = $this->tableName . ' as mf';
		$select->columns = 'mf.*, wo.*, cv1.cv_value as WO_TYPE_DESC';
		$select->joins = 'left join WorkOrder_Master as wo on mf_wonum = wo_num
						  left join Code_Values_Master as cv1 on cv_group = \'WO_TYPE\' and cv_value = wo_type ';
		$select->andWhere("MF_WONUM = ?", trim($data['MF_WONUM']) );

		$this->execListQuery($select->toString(), $select->parms);
		$row = db2_fetch_assoc( $this->stmt );
		if (is_array($row)) {
			$row['MF_CHANGE_TIME'] = VGS_Form::getTimeStampOutputFormat($row['MF_CHANGE_TIME']);
			$row['MF_CREATE_TIME'] = VGS_Form::getTimeStampOutputFormat($row['MF_CREATE_TIME']);
		}
//		pre_dump($row);
		return $row;
    }

    public function getMechFitFailDescription ( $mfwonum ) {
		$select = new VGS_DB_Select();
		$select->from = $this->tableName;
		$select->andWhere("MF_WONUM = ?", trim($mfwonum));

		$this->execListQuery($select->toString(), $select->parms);
		if ( $row = db2_fetch_assoc( $this->stmt )) {
			return $row['MF_MECH_FITTING_DESCRIPTION'];
		} else {
			return '';
		}
    } 

    public function exists_MF_Failure( $mfwonum ) {
		$select = new VGS_DB_Select();
		$select->from = $this->tableName;
		$select->andWhere("MF_WONUM = ?", trim($mfwonum));

		$this->execListQuery($select->toString(), $select->parms);
		$row = db2_fetch_assoc( $this->stmt );
		if ( isset($row) && is_array($row)) {
			return true;
		} else {
			return false;
		}
    } 
    
    public function getMFSearchSelect(
       	array &$screenData, 
    	VGS_DB_Select $select,
    	VGS_Search_Filter_Group $filter
	   	) 
	{
    	$filter->saveRestoreFilters($screenData);
		
		$select->from = $this->tableName;
		$select->joins= 'left join WORKORDER_MASTER on MF_WONUM = WO_NUM ';
		$select->order = 'MF_WONUM';
		
		$filter->renderWhere($screenData, $select);
	}	
    
}