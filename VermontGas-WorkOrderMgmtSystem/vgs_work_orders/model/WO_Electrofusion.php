<?php
require_once '../model/VGS_DB_Table.php';
require_once '../forms/VGS_Form.php';
require_once '../model/Code_Values_Master.php';
require_once 'Zend/Loader/Autoloader.php';
require_once '../model/Sec_Profiles.php';

class WO_Electrofusion extends VGS_DB_Table
{
	
	//---------------------------------------------------
	public function __construct($conn) {
    	parent::__construct($conn);

		Zend_Loader_Autoloader::getInstance();
    	
    	$this->tableName = 'WO_Electrofusion';
		$this->tablePrefix = 'WEF_';
		$this->keyFields = array('WEF_WO_NUM', 'WEF_SEQNO');
    	$this->hasAuditFields = true;
    	$this->isRecordDeletionAllowed = true;
    }
     
	//---------------------------------------------------
    public function create( $rec ) {
    	$this->checkPermissionByCategory('WEF', 'CREATE');
    	$rec['WEF_SEQNO'] = $this->getNextEFSeqNum($rec['WEF_WO_NUM']);
    	return $this->autoCreateRecord($rec);
    }
    
	//---------------------------------------------------
    public function update( $rec ) {
    	$this->checkPermissionByCategory('WEF', 'UPDATE');
    	return $this->autoUpdateRecord($rec);
    }	

	 //---------------------------------------------------
    public function delete( $rec ) {
    	$this->checkPermissionByCategory('WEF', 'UPDATE');
    	return $this->autoDeleteRecord($rec);
    }	

    public function getNextEFSeqNum($woNum) {
    	$select = new VGS_DB_Select();
    	$select->from = $this->tableName;
    	$select->columns = 'ifnull(max(WEF_SEQNO),0) as LAST_SEQNO';
    	$select->andWhere("WEF_WO_NUM = ?", $woNum);
    	$maxAry = $this->fetchRow($select->toString(), $select->parms);
    	return (int)$maxAry['LAST_SEQNO'] + 1;
    }
    
	 //---------------------------------------------------
    public function retrieve( $data ) {
		$select = new VGS_DB_Select();
		$select->columns = 'wef.*'; 
		$select->from = $this->tableName . ' as wef ';
		$select->joins = "join workorder_master as wo on wef_wo_num = wo_num";
		$select->andWhere("WEF_WO_NUM = ?", trim($data['WEF_WO_NUM']) );
		$select->andWhere("WEF_SEQNO = ?", trim($data['WEF_SEQNO']) );

		$this->execListQuery($select->toString(), $select->parms);
		//pre_dump($select->toString());
		$row = db2_fetch_assoc( $this->stmt );
		if (is_array($row)) {
			$row['WEF_CHANGE_TIME'] = VGS_Form::getTimeStampOutputFormat($row['WEF_CHANGE_TIME']);
			$row['WEF_CREATE_TIME'] = VGS_Form::getTimeStampOutputFormat($row['WEF_CREATE_TIME']);
    		$row = array_map('trim', $row);
		}
		return $row;
    }
 	
    //---------------------------------------------------
    public function retrieveByID( $woNum, $seqNo ) {
    	$data['WEF_WO_NUM'] = $woNum;
    	$data['WEF_SEQNO'] = $seqNo;
    	
    	return $this->retrieve($data);
    }

    //---------------------------------------------------
	public function buildFilteredSelect(
    		array &$screenData,
    		VGS_DB_Select $select,
    		VGS_Search_Filter_Group $filter
    )
    {
    	$cvm = new Code_Values_Master($this->db_conn_obj);
    	   
    	$filter->addFilter('WEF_WO_NUM', 'W/O#');
    	
    	$filter->addFilter('WO_TYPE', 'WO Type');
    	$cvList = $cvm->getCodeValuesList('WO_TYPE', '-- All --');
    	$filter->setDropDownList('WO_TYPE', $cvList);

    	$filter->addFilter('WEF_FUSION_TYPE', 'Fusion Type');
    	$cvList = $cvm->getCodeValuesList('FUSION_TYPE', '-- All --');
    	$filter->setDropDownList('WEF_FUSION_TYPE', $cvList);
    	 
    	$filter->addFilter('WEF_ADDRESS', 'Street Addr.', 'LIKE');
    	
    	$filter->addFilter('WO_TAX_MUNICIPALITY', 'Town');
    	$cvList = $cvm->getCodeValuesList('TOWN', '-- All --');
    	$filter->setDropDownList('WO_TAX_MUNICIPALITY', $cvList);
    	
    	$filter->saveRestoreFilters($screenData);
    
    	$select->from = $this->tableName . ' wef ';
    	$select->columns = '
	    	wef.*, WO_DESCRIPTION, WO_TYPE, WO_STATUS, 
    		WO_DATE_COMPLETED, WO_TAX_MUNICIPALITY,
	    	cv1.CV_VALUE as WO_TYPE_DESC,
	    	cv2.CV_VALUE as WO_STATUS_DESC,
	    	cv3.CV_VALUE as TOWN_DESC,
    		cv4.CV_VALUE as FUSION_TYPE_DESC';
    		
    	$select->order = 'WEF_WO_NUM DESC, WEF_SEQNO';
    	$select->joins = "LEFT JOIN WORKORDER_MASTER wo on WEF_WO_NUM = WO_NUM
    	LEFT JOIN CODE_VALUES_MASTER as cv1 ON cv1.CV_GROUP = 'WO_TYPE' and cv1.CV_CODE = WO_TYPE
    	LEFT JOIN CODE_VALUES_MASTER as cv2 ON cv2.CV_GROUP = 'WO_STATUS' and cv2.CV_CODE = WO_STATUS
    	LEFT JOIN CODE_VALUES_MASTER as cv3 ON cv3.CV_GROUP = 'TOWN' and cv3.CV_CODE = WO_TAX_MUNICIPALITY
    	LEFT JOIN CODE_VALUES_MASTER as cv4 ON cv4.CV_GROUP = 'FUSION_TYPE' and cv4.CV_CODE = WEF_FUSION_TYPE		
    	";
    		
    	$filter->renderWhere($screenData, $select);
    
    }
       
}