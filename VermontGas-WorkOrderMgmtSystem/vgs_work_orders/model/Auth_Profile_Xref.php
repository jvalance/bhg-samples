<?php
require_once '../model/VGS_DB_Table.php';
require_once '../model/Code_Values_Master.php';

class Auth_Profile_Xref extends VGS_DB_Table
{
	
    public function __construct($conn) {
    	parent::__construct($conn);
//		$this->checkPermissionByCategory('SEC', 'INQUIRY');
		$this->tableName = 'AUTH_PROFILE_XREF';
		$this->tablePrefix = 'AP_';
	    $this->keyFields = array('AP_AUTH_ID', 'AP_PROFILE_ID');
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
		$select->andWhere("AP_AUTH_ID = ?", trim($data['AP_AUTH_ID']) );
		$select->andWhere("AP_PROFILE_ID = ?", trim($data['AP_PROFILE_ID']) );

		$this->execListQuery($select->toString(), $select->parms);
		$row = db2_fetch_assoc( $this->stmt );
		if (is_array($row)) {
			$row['AP_CHANGE_TIME'] = VGS_Form::getTimeStampOutputFormat($row['AP_CHANGE_TIME']);
			$row['AP_CREATE_TIME'] = VGS_Form::getTimeStampOutputFormat($row['AP_CREATE_TIME']);
		}
		return $row;
    }

    public function getProfileAuthorities( $profileId ) {
		$select = new VGS_DB_Select();
		$select->from = $this->tableName;
		$select->joins = "join sec_profiles on ap_profile_id = prf_profile_id";
		$select->joins .= " join authorities on ap_auth_id = ad_auth_id";
		$select->andWhere("AP_PROFILE_ID = ?", trim($profileId));

		$this->execListQuery($select->toString(), $select->parms);
		$rows = array();
		while ($row = db2_fetch_assoc( $this->stmt )) {
			$rows[] = $row;
		}
		return $rows;
    }

    public function getPermission( $authority, $profile ) {
    	$data['AP_AUTH_ID'] = $authority;
    	$data['AP_PROFILE_ID'] = $profile;
    	$rec = $this->retrieve($data);
   	
    	if (isset($rec['AP_PERMISSION'])) {
			return trim(strtoupper($rec['AP_PERMISSION']));
    	} else {
			return '';
    	}
    }
    
    public function isUserSysAdmin( $user = NULL ) {
    	if ( ! isset( $user ) ) $user = $_SESSION['current_user'];
    	$saPermission = $this->getPermission('*SYSADMIN', $user);
    	return ('ALLOW' == trim($saPermission) );
    }
    
    public function buildFilteredSelect(
       	array &$screenData, 
    	VGS_DB_Select $select,
    	VGS_Search_Filter_Group $filter
	) 
	{
		$filter->addFilter('AP_PROFILE_ID', 'Profile ID', 'LIKE');
		$filter->setInputSize('AP_PROFILE_ID', 10);

		$filter->addFilter('PRF_DESCRIPTION', 'Profile Name', 'LIKE');
		$filter->setInputSize('PRF_DESCRIPTION', 20);
		$filter->setUpperCase('AP_PROFILE_ID');
		
		$filter->addFilter('AP_AUTH_ID', 'Auth ID', 'LIKE');
		$filter->setInputSize('AP_AUTH_ID', 10);
		$filter->setUpperCase('AP_AUTH_ID');

		$filter->addFilter('AP_PERMISSION', 'Permission');
		$filter->setDropDownList('AP_PERMISSION', array(' '=>' ', 'ALLOW'=>'Allow', 'DENY'=>'Deny'));
		
//		$dd = new Code_Values_Master($this->db_conn_obj);
//		$ddList = $cvm->getCodeValuesList('AP_FUNCTIONAL_AREA', '-- All --');
//		$filter->setDropDownList('AP_FUNCTIONAL_AREA', $ddList);
		
		$filter->saveRestoreFilters($screenData);
		
		$select->from = $this->tableName;
		$select->joins = 'join Sec_Profiles on ap_Profile_Id = prf_Profile_Id';
		$select->joins .= ' join Authorities on ap_Auth_Id = ad_Auth_Id';
		$select->order = 'PRF_PROFILE_TYPE, AP_PROFILE_ID, AP_AUTH_ID';
		
		$filter->renderWhere($screenData, $select);
		
    }	
    
}