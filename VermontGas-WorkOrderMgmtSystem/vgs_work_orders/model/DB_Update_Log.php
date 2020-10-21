<?php
require_once '../model/VGS_DB_Table.php';
require_once '../forms/VGS_Form.php';
require_once '../model/Sec_Profiles.php';

class DB_Update_Log extends VGS_DB_Table
{
	//---------------------------------------------------
	public function __construct($conn) {
    	parent::__construct($conn);

    	$this->tableName = 'DB_UPDATE_LOG';
		$this->tablePrefix = 'DBL_';
		$this->keyFields = array('DBL_TABLE_NAME', 'DBL_KEY_FIELDS', 'DBL_REC_SEQ');
    	$this->hasAuditFields = false;
    	$this->isRecordDeletionAllowed = false;
    }
     
	//---------------------------------------------------
    public function create( $rec ) {
    	// Records in the DB Update Log are created automatically in parent class (VGS_DB_Table) 
    }
    
	//---------------------------------------------------
    public function update( $rec ) {
    	// Records in the DB Update Log cannot be updated. 
    }	

	 //---------------------------------------------------
    public function delete( $rec ) {
    	// Records in the DB Update Log cannot be deleted.
    }	
    
	 //---------------------------------------------------
    public function retrieve( $data ) {
		$select = new VGS_DB_Select();
		$select->columns = 'dbl.*'; 
		$select->from = $this->tableName . ' as dbl ';
		$select->andWhere("DBL_TABLE_NAME = ?", trim($data['DBL_TABLE_NAME']) );
		$select->andWhere("DBL_KEY_FIELDS = ?", trim($data['DBL_KEY_FIELDS']) );
		$select->andWhere("DBL_REC_SEQ = ?", trim($data['DBL_REC_SEQ']) );

		$this->execListQuery($select->toString(), $select->parms);
		//pre_dump($select->toString());
		$row = db2_fetch_assoc( $this->stmt );
		if (is_array($row)) {
			$row['DBL_UPD_TIMESTAMP'] = VGS_Form::getTimeStampOutputFormat($row['DBL_UPD_TIMESTAMP']);
    		$row = array_map('trim', $row);
		}
		return $row;
    }
 	
    //---------------------------------------------------
    public function retrieveByID( $table, $keys, $seqNo ) {
    	$data['DBL_TABLE_NAME'] = $table;
    	$data['DBL_KEY_FIELDS'] = $keys;
    	$data['DBL_REC_SEQ'] = $seqNo;
    	
    	return $this->retrieve($data);
    }

    //---------------------------------------------------
	public function buildFilteredSelect(
    		array &$screenData,
    		VGS_DB_Select $select,
    		VGS_Search_Filter_Group $filter
    )
    {
   	 
    	$filter->addFilter('DBL_TABLE_NAME', 'Table Name', 'LIKE');
    	$filter->setUpperCase('DBL_TABLE_NAME');
    	$filter->setInputSize('DBL_TABLE_NAME', 15);
    	
    	$filter->addFilter('DBL_FIELD_CHANGED', 'Field Name', 'LIKE');
    	$filter->setUpperCase('DBL_FIELD_CHANGED');
    	$filter->setInputSize('DBL_FIELD_CHANGED', 15);

    	$filter->addFilter('DBL_KEY_FIELDS', 'Record Keys', 'LIKE');
    	$filter->setInputSize('DBL_KEY_FIELDS', 20);
    	   
    	$filter->addFilter('DBL_UPD_USER', 'User ID');
    	$filter->setUpperCase('DBL_UPD_USER');
    	   
    	$filter->addFilter('DBL_ACTION', 'Record Action');
    	$filter->setUpperCase('DBL_ACTION');
    	 
    	$filter->saveRestoreFilters($screenData);
    	$select->columns = 'dbl.*, date(DBL_UPD_TIMESTAMP) as DBL_DATE, time(DBL_UPD_TIMESTAMP) as DBL_TIME';
    	$select->from = $this->tableName . ' dbl ';
    	$select->order = 'DBL_UPD_TIMESTAMP DESC';
    	
    	$filter->renderWhere($screenData, $select);

    }
       
}