<?php
require_once '../model/VGS_DB_Table.php';
require_once '../model/Code_Values_Master.php';

class Group_User_Xref extends VGS_DB_Table
{
    public function __construct($conn) {
    	parent::__construct($conn);
//    	$this->checkPermissionByCategory('SEC', 'INQUIRY');
    	$this->tableName = 'GROUP_USER_XREF';
		$this->tablePrefix = 'UG_';
	    $this->keyFields = array('UG_GROUP_ID', 'UG_USER_ID');
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

    public function retrieve( $data ) {
		$select = new VGS_DB_Select();
		$select->from = $this->tableName;
		$select->andWhere("UG_GROUP_ID = ?", trim($data['UG_GROUP_ID']) );
		$select->andWhere("UG_USER_ID = ?", trim($data['UG_USER_ID']) );

		$this->execListQuery($select->toString(), $select->parms);
		$row = db2_fetch_assoc( $this->stmt );
		if (is_array($row)) {
			$row['UG_CHANGE_TIME'] = VGS_Form::getTimeStampOutputFormat($row['UG_CHANGE_TIME']);
			$row['UG_CREATE_TIME'] = VGS_Form::getTimeStampOutputFormat($row['UG_CREATE_TIME']);
		}

		return $row;
    }

    public function getGroupsForUser( $user ) {
		$select = new VGS_DB_Select();
		$select->from = $this->tableName;
		$select->andWhere("UG_USER_ID = ?", trim($user) );

		$this->execListQuery($select->toString(), $select->parms);

		$rows = array();
		while ($row = db2_fetch_assoc( $this->stmt )) {
			$rows[] = $row['UG_GROUP_ID'];
		};
		return $rows;
    }

    public function getUsersForGroup( $group ) {
		$select = new VGS_DB_Select();
		$select->from = $this->tableName;
		$select->andWhere("UG_GROUP_ID = ?", trim($group) );

		$this->execListQuery($select->toString(), $select->parms);

		$rows = array();
		while ($row = db2_fetch_assoc( $this->stmt )) {
			$rows[] = $row;
		};
		return $rows;
    }
    
    public function buildFilteredSelect(
       	array &$screenData, 
    	VGS_DB_Select $select,
    	VGS_Search_Filter_Group $filter) 
	{
		$filter->addFilter('UG_GROUP_ID', 'Group Profile', 'LIKE');
		$filter->setInputSize('UG_GROUP_ID', 10);
		$filter->setUpperCase('UG_GROUP_ID');
		
		$filter->addFilter('UG_USER_ID', 'User Profile', 'LIKE');
		$filter->setInputSize('UG_USER_ID', 10);
		$filter->setUpperCase('UG_USER_ID');
		
		$filter->saveRestoreFilters($screenData);
		
		$select->from = $this->tableName;
		$select->order = 'UG_GROUP_ID, UG_USER_ID';
		
		$filter->renderWhere($screenData, $select);
		
    }	
    
}