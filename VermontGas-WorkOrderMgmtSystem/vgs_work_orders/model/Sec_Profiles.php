<?php
require_once '../model/VGS_DB_Table.php'; 
require_once '../forms/VGS_Form.php';
require_once '../model/Code_Values_Master.php';

class Sec_Profiles extends VGS_DB_Table
{
	
    public function __construct($conn) {
    	parent::__construct($conn);
//    	$this->checkPermissionByCategory('SEC', 'INQUIRY');
    	
    	$this->tableName = 'SEC_PROFILES';
		$this->tablePrefix = 'PRF_';
	    $this->keyFields = array('PRF_PROFILE_ID');
    	$this->hasAuditFields = true;
    	$this->isRecordDeletionAllowed = true;
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
		$select->andWhere("PRF_PROFILE_ID = ?", trim($data['PRF_PROFILE_ID']) );

		$this->execListQuery($select->toString(), $select->parms);
// 		pre_dump($select->toString());
// 		pre_dump($select->parms);
		$row = db2_fetch_assoc( $this->stmt );
		if (is_array($row)) {
			$row['PRF_CHANGE_TIME'] = VGS_Form::getTimeStampOutputFormat($row['PRF_CHANGE_TIME']);
			$row['PRF_CREATE_TIME'] = VGS_Form::getTimeStampOutputFormat($row['PRF_CREATE_TIME']);
    		$row = array_map('trim', $row);
		}
		return $row;
    }

    public function retrieveByID( $profileID ) {
    	$data['PRF_PROFILE_ID'] = $profileID;
    	return $this->retrieve($data);
    }
    
    public function getProfileInfo( $profile ) {
    	$i5o = new VGS_i5_Conn();
    	$tko = $i5o->connect_default(); 
    	if (!$tko) {
    		$errmsg = $tko->getErrorMessage();
    		throw new Exception("Unable to retrieve profile type for profile ID $profile. " .
    				"i5 connection failed. Error msg = $errmsg");
    	}
		
    	// Call CL program CHKUSRPRF, which adopts QSECOFR authority and runs RTVUSRPRF command. 
		$text = $type = $status = $pwExp = '';
		$param [] = $tko->AddParameterChar ( 'both', 10, 'profile', 'profile', $profile);
		$param [] = $tko->AddParameterChar ( 'both', 5, 'type', 'type', $type);
		$param [] = $tko->AddParameterChar ( 'both', 50, 'text', 'text', $text);
		$param [] = $tko->AddParameterChar ( 'both', 10, 'status', 'status', $status);
		$param [] = $tko->AddParameterChar ( 'both', 4, 'pwExp', 'pwExp', $pwExp);
		$result = $tko->PgmCall ( "CHKUSRPRF", "", $param, null, null );

		if ($result) {
			$chkusrprf = $result['io_param']; 
			// Load return array with results from RTVUSRPRF 
			$profileInfo = array(
					"profile" => $profile,
					"profileType" => $chkusrprf['type'],
					"profileText" => $chkusrprf['text'],
					"profileStatus" => $chkusrprf['status'],
					"isPswdExpired" => $chkusrprf['pwExp']
			);
		} else {
			$profileInfo = false;
		}
		
		return $profileInfo;    
    }
    
    public function buildFilteredSelect(
       	array &$screenData, 
    	VGS_DB_Select $select,
    	VGS_Search_Filter_Group $filter
	) 
	{
		$dd = new Code_Values_Master($this->db_conn_obj);
		
		$filter->addFilter('PRF_PROFILE_ID', 'Profile ID');
		$filter->setInputSize('PRF_PROFILE_ID', 8, 'LIKE');
		$filter->setUpperCase('PRF_PROFILE_ID');
		
		$filter->addFilter('PRF_DESCRIPTION', 'Description', 'LIKE');
		$filter->setInputSize('PRF_DESCRIPTION', 25);

		$filter->addFilter('PRF_PROFILE_TYPE', 'Type');
		$ddList = $dd->getCodeValuesList('AP_PROFILE_TYPE', ' ');
		$filter->setDropDownList('PRF_PROFILE_TYPE', $ddList);
		
		$filter->addFilter('PRF_PROFILE_STATUS', 'Status');
		
		$ddList = $dd->getCodeValuesList('RECSTATUS', ' ');
		$filter->setDropDownList('PRF_PROFILE_STATUS', $ddList);
		
		$filter->saveRestoreFilters($screenData);
		
		$select->from = $this->tableName . ' as prf';
		$select->order = 'PRF_PROFILE_TYPE, PRF_PROFILE_ID';
		$select->columns = 'prf.*, (select count(*) from GROUP_USER_XREF where UG_GROUP_ID = PRF_PROFILE_ID) as USER_COUNT';
		
		$filter->renderWhere($screenData, $select);
		
    }	
    
}