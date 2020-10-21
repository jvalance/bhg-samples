<?php
require_once '../model/VGS_DB_Table.php';
require_once '../forms/VGS_Form.php';
require_once '../model/Code_Values_Master.php';
require_once 'Zend/Loader/Autoloader.php';
require_once '../model/Sec_Profiles.php';

class WO_Sewer extends VGS_DB_Table
{
	
	//---------------------------------------------------
	public function __construct($conn) {
    	parent::__construct($conn);

		Zend_Loader_Autoloader::getInstance();
    	
    	$this->tableName = 'WO_SEWER';
		$this->tablePrefix = 'WSW_';
		$this->keyFields = array('WSW_WO_NUM', 'WSW_SEQNO');
    	$this->hasAuditFields = true;
    	$this->isRecordDeletionAllowed = true;
    }
     
	//---------------------------------------------------
    public function create( $rec ) {
    	$this->checkPermissionByCategory('WO', 'CREATE');
    	$rec['WSW_SEQNO'] = $this->getNextSewerNum($rec['WSW_WO_NUM']);
    	$this->autoCreateRecord($rec);
    }
    
	//---------------------------------------------------
    public function update( $rec ) {
    	$this->checkPermissionByCategory('WO', 'UPDATE');
    	$this->autoUpdateRecord($rec);
    }	

	 //---------------------------------------------------
    public function delete( $rec ) {
    	$this->checkPermissionByCategory('WO', 'UPDATE');
    	$this->autoDeleteRecord($rec);
    }	

    public function getNextSewerNum($woNum) {
    	$select = new VGS_DB_Select();
    	$select->from = $this->tableName;
    	$select->columns = 'ifnull(max(WSW_SEQNO),0) as LAST_SEQNO';
    	$select->andWhere("WSW_WO_NUM = ?", $woNum);
    	$maxAry = $this->fetchRow($select->toString(), $select->parms);
    	return (int)$maxAry['LAST_SEQNO'] + 1;
    }
    
	 //---------------------------------------------------
    public function retrieve( $data ) {
		$select = new VGS_DB_Select();
		$select->columns = 'wsw.*'; //, cvSts.CV_VALUE as REASON_TEXT';
		$select->from = $this->tableName . ' as wsw ';
		$select->joins = "join workorder_master as wo on wsw_wo_num = wo_num";
		//$select->joins = "join code_values_master as cvSts on cv_group = 'WCN_REASON_CODE' and cv_code = WCN_REASON_CODE";
		$select->andWhere("WSW_WO_NUM = ?", trim($data['WSW_WO_NUM']) );
		$select->andWhere("WSW_SEQNO = ?", trim($data['WSW_SEQNO']) );

		$this->execListQuery($select->toString(), $select->parms);
		//pre_dump($select->toString());
		$row = db2_fetch_assoc( $this->stmt );
		if (is_array($row)) {
			$row['WSW_CHANGE_TIME'] = VGS_Form::getTimeStampOutputFormat($row['WSW_CHANGE_TIME']);
			$row['WSW_CREATE_TIME'] = VGS_Form::getTimeStampOutputFormat($row['WSW_CREATE_TIME']);
    		$row = array_map('trim', $row);
		}
		return $row;
    }
 	
    //---------------------------------------------------
    public function retrieveByID( $woNum, $seqNo ) {
    	$data['WSW_WO_NUM'] = $woNum;
    	$data['WSW_SEQNO'] = $seqNo;
    	
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
    	   
    	$filter->addFilter('WSW_WO_NUM', 'W/O#');
    	
    	$filter->addFilter('WO_TYPE', 'WO Type');
    	$cvList = $cvm->getCodeValuesList('WO_TYPE', '-- All --');
    	$filter->setDropDownList('WO_TYPE', $cvList);
    	
    	$filter->addFilter('WO_STATUS', 'WO Sts');
    	$cvList = $cvm->getCodeValuesList('WO_STATUS', ' ');
    	$filter->setDropDownList('WO_STATUS', $cvList);
    	 
    	$filter->addFilter('WSW_ADDRESS', 'Street Addr.', 'LIKE');
    	
    	$filter->addFilter('WSW_CITY', '<br>Town');
    	$cvList = $cvm->getCodeValuesList('TOWN', '-- All --');
    	$filter->setDropDownList('WSW_CITY', $cvList);
    	
    	$filter->addFilter('WSW_INSPECTION_NEEDED', 'Insp. Needed');
    	$filter->setCheckbox('WSW_INSPECTION_NEEDED');
    	
    	$filter->addFilter('WSW_LOCATED_PRIOR', 'Located Prior?');
    	$filter->setCheckbox('WSW_LOCATED_PRIOR');
    	 
    	$filter->addFilter('WO_DATE_COMPLETED', 'Date installed');
    	$filter->setSpecialWhere('WO_DATE_COMPLETED');
    	$filter->setDateField('WO_DATE_COMPLETED');
    	
    	$filter->addFilter('WSW_MOC_TRENCH', '<b>MOC</b>: Trench');
    	$filter->setCheckbox('WSW_MOC_TRENCH');
    	$filter->addFilter('WSW_MOC_HDD', 'HDD');
    	$filter->setCheckbox('WSW_MOC_HDD');
    	$filter->addFilter('WSW_MOC_HOG', 'Hog');
    	$filter->setCheckbox('WSW_MOC_HOG');
    	$filter->addFilter('WSW_MOC_PLOWED', 'Plow');
    	$filter->setCheckbox('WSW_MOC_PLOWED');
    	 
    	$filter->addFilter('WSW_MOC_COMBO', 'Combo');
    	$filter->setCheckbox('WSW_MOC_COMBO');
    	$filter->setSpecialWhere('WSW_MOC_COMBO');
    
    	$filter->saveRestoreFilters($screenData);
    
    	$select->from = $this->tableName . ' wsw ';
    	$select->columns = '
    	wsw.*, WO_DESCRIPTION, WO_TYPE, WO_STATUS, WO_DATE_COMPLETED,
    	fn_MOC_Combo(WSW_MOC_TRENCH, WSW_MOC_HDD, WSW_MOC_HOG, 
				  WSW_MOC_PLOWED, WSW_MOC_OTHER) as combo,
    	cv1.CV_VALUE as WO_TYPE_DESC,
    	cv2.CV_VALUE as WO_STATUS_DESC,
    	cv3.CV_VALUE as TOWN_DESC';
    		
    	$select->order = 'WSW_WO_NUM DESC, WSW_SEQNO';
    	$select->joins = "LEFT JOIN WORKORDER_MASTER wo on WSW_WO_NUM = WO_NUM
    	LEFT JOIN CODE_VALUES_MASTER as cv1 ON cv1.CV_GROUP = 'WO_TYPE' and cv1.CV_CODE = WO_TYPE
    	LEFT JOIN CODE_VALUES_MASTER as cv2 ON cv2.CV_GROUP = 'WO_STATUS' and cv2.CV_CODE = WO_STATUS
    	LEFT JOIN CODE_VALUES_MASTER as cv3 ON cv3.CV_GROUP = 'TOWN' and cv3.CV_CODE = WSW_CITY
    	";
    		
    	$filter->renderWhere($screenData, $select);

    	// special filters
    
    	// WO_DATE_COMPLETED
    	if (isset($screenData['filter_WO_DATE_COMPLETED'])
    		&& trim($screenData['filter_WO_DATE_COMPLETED']) != '')
    	{
    		$dateCompleted = trim($screenData['filter_WO_DATE_COMPLETED']);
    		$select->andWhere("WO_DATE_COMPLETED <= ?", $dateCompleted);
    	}
    
    	// WSW_MOC_COMBO
    	if (trim($screenData['filter_WSW_MOC_COMBO']) == 'Y') {
	    		$select->andWhere("fn_MOC_Combo(WSW_MOC_TRENCH, WSW_MOC_HDD, 
	    			WSW_MOC_HOG, WSW_MOC_PLOWED, WSW_MOC_OTHER) > 1 ");
    	}
    
    }
       
}