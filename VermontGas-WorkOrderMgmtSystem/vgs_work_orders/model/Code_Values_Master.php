<?php
require_once '../model/VGS_DB_Table.php';
//require_once '../model/VGS_DB_Select.php';
class Code_Values_Master extends VGS_DB_Table
{
	public static $statusCodes = array('' => '*All', 'ACT' => 'Active', 'INA' => 'Inactive');
    
	public function __construct($conn) {
    	parent::__construct($conn);
//    	$this->checkPermissionByCategory('DD', 'INQUIRY');
    	$this->tableName = 'CODE_VALUES_MASTER';
		$this->tablePrefix = 'CV_';
	   $this->keyFields = array('CV_GROUP', 'CV_CODE');
    	$this->hasAuditFields = true;
    	$this->isRecordDeletionAllowed = true;
	}
    /**
     * Retrieve an associative array of codes => values from CODE_VALUES_MASTER
     * for the passed group code. 
     * 
     * @param string $group The code/values group (CV_GROUP on CODE_VALUES_MASTER)
     * @param string $blankValue Optional first entry for no selection
     */   
    
   public function retrieveRecord ( $data ) {
		$select = new VGS_DB_Select();
		$select->from = $this->tableName;
		$select->andWhere("CV_GROUP = ?", $data['CV_GROUP']);
		$select->andWhere("CV_CODE = ?", $data['CV_CODE']);
		
		$this->execListQuery($select->toString(), $select->parms);
		$rec = db2_fetch_assoc( $this->stmt );
		if (is_array($rec)) {
			$rec['CV_CHANGE_TIME'] = VGS_Form::getTimeStampOutputFormat($rec['CV_CHANGE_TIME']);
			$rec['CV_CREATE_TIME'] = VGS_Form::getTimeStampOutputFormat($rec['CV_CREATE_TIME']);
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
    
    public function getCodeValuesList ( $group, $blankValue = NULL, $codesOnly = false ) {
		$select = new VGS_DB_Select();
		
		$select->from = $this->tableName;
		$select->andWhere("trim(CV_GROUP) = ?", trim($group));
		$select->andWhere("CV_STATUS <> ?", 'INA');
		$select->order = 'CV_SEQUENCE';

		$qry = $select->toString();
		$this->execListQuery($qry, $select->parms);
		$codeList = array();

		if (NULL != $blankValue) {
			$codeList[''] = $blankValue;
		}
		
		if ($codesOnly) {
			while ( $rec = db2_fetch_assoc( $this->stmt )) {
				$codeList[trim($rec['CV_CODE'])] = trim($rec['CV_CODE']);
			}
			return $codeList;
		} else {
			while ( $rec = db2_fetch_assoc( $this->stmt )) {
				$codeList[trim($rec['CV_CODE'])] = trim($rec['CV_VALUE']);
			}
		}
	    
		return $codeList;
	    
    }

    public function getCodeValue ( $group, $code ) {
		$select = new VGS_DB_Select();
		$select->from = $this->tableName;
		$select->andWhere("trim(CV_GROUP) = ?", trim($group));
		$select->andWhere("trim(CV_CODE) = ?", trim($code));
		$select->andWhere("CV_STATUS <> ?", 'INA');

		$this->execListQuery($select->toString(), $select->parms);

		if ( $rec = db2_fetch_assoc( $this->stmt )) {
			return $rec['CV_VALUE'];
		} else {
			return '';
		}
    }

    public function getCVSearchSelect(
    	array &$screenData, 
    	VGS_DB_Select $select,
    	VGS_Search_Filter_Group $filter
	   	) 
	{
    	
		$select->from = $this->tableName;
		$select->order = 'CV_GROUP, CV_SEQUENCE, CV_CODE';
		
		$filter->renderWhere($screenData, $select);

    }	    
}