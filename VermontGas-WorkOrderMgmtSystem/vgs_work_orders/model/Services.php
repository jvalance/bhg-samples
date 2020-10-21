<?php
require_once '../model/VGS_DB_Table.php';
require_once '../model/Code_Values_Master.php';

class Services extends VGS_DB_Table {

	public function __construct($conn) {
		parent::__construct ( $conn );
		
		$this->tableName = 'SERVICES';
		$this->tablePrefix = 'SV_';
		$this->keyFields = array (
				'SV_SERVICE_ID' 
		);
		$this->hasAuditFields = true;
		$this->isRecordDeletionAllowed = true; 
	}
	
	/**
	 * Insert a record in the SERVICES table
	 * 
	 * @param array $rec        	
	 */
	public function create(&$rec) {
		$this->checkPermissionByCategory ( 'SVC', 'CREATE' );
		$rec ['SV_SERVICE_ID'] = $this->getNextServiceID ();

		$output = $this->autoCreateRecord ( $rec );
		
		$prems = $rec[SPX_PREMISE_NUMS];
		$svID = $rec[SV_SERVICE_ID];
		$this->linkPremisesToService($prems, $svID);
		
		return $output;
	}
	
	public function getNextServiceID() {
		$select = new VGS_DB_Select ();
		$select->from = $this->tableName;
		$select->columns = 'ifnull(max(SV_SERVICE_ID),0) as LAST_SVC_ID';
		$maxAry = $this->fetchRow ( $select->toString () );
		return ( int ) $maxAry ['LAST_SVC_ID'] + 1;
	}
	public function getPDFFullPath($street, $town, $filename) {
		$folder = "file:///Vgs03\\cad\\DRAWINGS\\Service%20Cards";
		// $folder = "L:\Shared\CAD\DRAWINGS\Service%20Cards";
		// $folder = "file:///L:\Shared\CAD\DRAWINGS\Service%20Cards";
		
		$streetURL = rawurlencode ( $street );
		$townURL = rawurlencode ( $town );
		$filenameURL = rawurlencode ( $filename );
		
		$path = $folder . "\\" . $townURL . "\\" . $streetURL . "\\" . $filenameURL;
		// $path = $folder . "/" . $townURL . "/" . $streetURL . "/" . $filenameURL;
		
		return $path;
	}
	
	/**
	 * Update a record in the SERVICES table
	 * 
	 * @param array $rec        	
	 */
	public function update($rec) {
		$auth = $this->checkPermissionByCategory ( 'SVC', 'UPDATE' );
		$output = $this->autoUpdateRecord ( $rec );
		
		$prems = $rec[SPX_PREMISE_NUMS];
		$svID = $rec[SV_SERVICE_ID];
		$this->clearLinksPremisesToService($svID);
		$this->linkPremisesToService($prems, $svID);
		
		return $output;
	}
	
	public function delete( $rec ) {
		$result = $this->checkPermissionByCategory('SVC', 'DELETE');
		$result = $this->autoDeleteRecord($rec);
	}
	
	public function retrieve($data) {
		$svID = $data ['SV_SERVICE_ID'];
		
		$row = $this->retrieveByID($svID);
				
		return $row;
	}
	
	public function retrieveByID($svID) {
		$select = new VGS_DB_Select ();
		$select->from = $this->tableName;
		$select->joins = 'left join WORKORDER_MASTER on SV_WO_NO = WO_NUM';
		$select->andWhere ( "SV_SERVICE_ID = ?", trim ( $svID ) );
	
		$this->execListQuery ( $select->toString (), $select->parms );
		$row = db2_fetch_assoc ( $this->stmt );
		if (is_array ( $row )) {
			$row ['SV_CHANGE_TIME'] = VGS_Form::getTimeStampOutputFormat ( $row ['SV_CHANGE_TIME'] );
			$row ['SV_CREATE_TIME'] = VGS_Form::getTimeStampOutputFormat ( $row ['SV_CREATE_TIME'] );
		}
	
		if ( is_array($row) ) {
			$prems = $this->getXrefPremiseNums($row[SV_SERVICE_ID]);
			$row[SPX_PREMISE_NUMS] = $prems;
		}
			
		// pre_dump($row);
	
		return $row;
	}		
	
	public function getXrefPremiseNums($svID) {		
		$select = new VGS_DB_Select ();
		
		$premArray = [];
		
		$select->columns = 'SPX_PREMISE_NUM';
		$select->from = 'SV_PREMISE_XREF as xrf ';
		$select->where = 'SPX_SERVICE_ID = ' . $svID;
		
		//	pre_dump($select->toString ());
		//	pre_dump($select->parms);
		
		$this->execListQuery ( $select->toString (), $select->parms );

		while ($row = db2_fetch_assoc ( $this->stmt )) {
			$premArray [] = $row[SPX_PREMISE_NUM];
		}
		
		//	pre_dump($prems);
		
		if (count ( $premArray ) == 1) {
			$prems = $premArray[0];
		} elseif (count ( $premArray ) > 1) {
			sort ($premArray);
			$prems = implode (", ", $premArray);
		} else {
			$prems = "";
		}
		
		return $prems;
	}

	public function getXrefServiceID($premNo) {		
		$select = new VGS_DB_Select ();
		
		$select->columns = 'SPX_SERVICE_ID';
		$select->from = 'SV_PREMISE_XREF as xrf ';
		$select->where = 'SPX_PREMISE_NUM = ' . $premNo;
	
		//	pre_dump($select->toString ());
		//	pre_dump($select->parms);
	
		$row = $this->fetchRow ( $select->toString (), $select->parms );
		$svID = $row[SPX_SERVICE_ID];
		
		return $svID;
	}
	
	public function getSearchSelect(array &$screenData, VGS_DB_Select $select, VGS_Search_Filter_Group $filter) {
		$cvm = new Code_Values_Master ( $conn );
		
		$filter->addFilter ( 'SV_CITY', 'Town' );
		
		$filter->addFilter ( 'SV_STREET', 'Street', 'LIKE' );
		$filter->setInputSize ( 'SV_STREET', 20 );
		
		$filter->addFilter ( 'FROM_HOUSE', 'From House#', '>=' );
		$filter->setInputSize ( 'FROM_HOUSE', 5 );
		$filter->setSpecialWhere ( 'FROM_HOUSE' );
		
		$filter->addFilter ( 'TO_HOUSE', 'To House#', '<=' );
		$filter->setInputSize ( 'TO_HOUSE', 5 );
		$filter->setSpecialWhere ( 'TO_HOUSE' );
		
		$filter->addFilter ( 'SV_SERVICE_ID', '<br />Service ID' );
		$filter->setInputSize ( 'SV_SERVICE_ID', 5 );
		
		$filter->addFilter ( 'SV_WO_NO', 'SI/WO#' );
		$filter->setInputSize ( 'SV_WO_NO', 5 );
		
		$filter->addFilter ( 'SV_SVC_STATUS', 'Svc Status' );
		$ddlist = $cvm->getCodeValuesList ( 'SVC_STATUS', ' ' );
		$filter->setDropDownList ( 'SV_SVC_STATUS', $ddlist );
				
		$filter->addFilter ( 'SV_UPDATE_STATUS', 'Upd Sts' );
		$ddlist = $cvm->getCodeValuesList ( 'SVC_UPD_STS', ' ' );
		$filter->setDropDownList ( 'SV_UPDATE_STATUS', $ddlist );
		
		// $filter->setReadOnly('WPE_WO_NUM');
		
		$filter->addFilter ( 'SV_ENTRY_FORMAT', '<br />Search entry format' );
		$ddlist = $cvm->getCodeValuesList ( 'SV_ENTRY_FORMAT', ' ' );
		$filter->setDropDownList ( 'SV_ENTRY_FORMAT', $ddlist );
		
		$filter->addFilter ( 'DFT_ENTRY_FORMAT', 'Default entry format' );
		$filter->setDefaultValue ( 'DFT_ENTRY_FORMAT', '' );
		$filter->setSpecialWhere ( 'DFT_ENTRY_FORMAT' );
		$ddlist = $cvm->getCodeValuesList ( 'SV_ENTRY_FORMAT', ' ' );
		$filter->setDropDownList ( 'DFT_ENTRY_FORMAT', $ddlist );
		
		$town_codes = $cvm->getCodeValuesList ( 'TOWN', '-- All --' );
		$filter->setDropDownList ( 'SV_CITY', $town_codes );
		
		$filter->saveRestoreFilters ( $screenData );
		
		$select->from = $this->tableName . ' svc ';
		$select->columns = "svc.*, dec(sv_entry_format,5,0) as dec_entry_format, wo.*, " . "cv1.CV_VALUE as TOWN_NAME, " . "cv2.CV_VALUE as SVC_STATUS_DESC, " . "cv3.CV_VALUE as SVC_MATERIAL_DESC, " . "cv4.CV_VALUE as SVC_SIZE_DESC ";
		// ifnull(dec(SV_HOUSE,9,0),SV_HOUSE)
		$select->order = 'SV_STATE, SV_CITY, SV_STREET, SV_HOUSE';
		$select->joins = "LEFT JOIN WORKORDER_MASTER wo on SV_WO_NO = WO_NUM 
			LEFT JOIN CODE_VALUES_MASTER as cv1 ON cv1.CV_GROUP = 'TOWN' and cv1.CV_CODE = SV_CITY 
			LEFT JOIN CODE_VALUES_MASTER as cv2 ON cv2.CV_GROUP = 'SVC_STATUS' and cv2.CV_CODE = SV_SVC_STATUS 
			LEFT JOIN CODE_VALUES_MASTER as cv3 ON cv3.CV_GROUP = 'PIPE_MTRL' and cv3.CV_CODE = SV_MATERIAL 
			LEFT JOIN CODE_VALUES_MASTER as cv4 ON cv4.CV_GROUP = 'PIPE_DIAM' and cv4.CV_CODE = SV_SIZE 
			";
		
		$filter->renderWhere ( $screenData, $select );
		
		// From and To House# filters
		if (isset ( $screenData ['filter_FROM_HOUSE'] ) && trim ( $screenData ['filter_FROM_HOUSE'] ) != '') {
			$from_house = trim ( $screenData ['filter_FROM_HOUSE'] );
			$select->andWhere ( "SV_HOUSE >= ?", $from_house );
		}
		if (isset ( $screenData ['filter_TO_HOUSE'] ) && trim ( $screenData ['filter_TO_HOUSE'] ) != '') {
			$to_house = trim ( $screenData ['filter_TO_HOUSE'] );
			$select->andWhere ( "SV_HOUSE >= ?", $to_house );
		}
	}
	/*
	 *  Below are functions to update "SV_PREMISE_XREF", the cross-reference table that 
	 *  stores many-to-one relationships between premise numbers and service ID's. 
	 *  This table is now the primary authority on which premises are linked to a given service. 
	 *  The "SV_PREMISE_ID" column in the Services table is deprecated and preserved only as backup.
	 */
	
	private function clearLinksPremisesToService($svID) {
		$sql = "DELETE FROM SV_PREMISE_XREF WHERE SPX_SERVICE_ID=$svID";
		$ins_res = $this->execUpdate ( $sql );
		
		return $ins_res;		
	}
	
	public function linkPremisesToService ($prems, $svID) {
		if ( isBlankOrZero($prems) ) {
			return;
		}
		
		$premsArray = explode(",", $prems);
		foreach ($premsArray as $prem) {
			$this->linkPremiseToService($prem, $svID);
		}
		
	}
	
	public function linkPremiseToService($premNo, $svID) {
// 		$this->checkPermissionByCategory ( 'SVC', 'CREATE' );
		
// 		// See if the link already exists
// 		$select = new VGS_DB_Select ();		
// 		$select->columns = "SPX_PREMISE_NUM";
// 		$select->from = 'SV_PREMISE_XREF as xrf ';
// 		$select->andWhere ( "SPX_SERVICE_ID = ?", trim ( $svID ) );
// 		$select->andWhere ( "SPX_PREMISE_NUM = ?", trim ( $premNo ) );
// 		$db_table = new VGS_DB_Table(null);
// 		$db_table->execListQuery ( $select->toString (), $select->parms );
// 		$row = db2_fetch_assoc ( $db_table->stmt );
// 		// If row found, return true
// 		if ( (array) $row === $row ) {
// 			return true;
// 		}
		 
		// If link not found, create it.
		$sql = "INSERT INTO SV_PREMISE_XREF (SPX_PREMISE_NUM, SPX_SERVICE_ID) values (?, ?)";
		$ins_res = $this->execUpdate ( $sql, array($premNo,$svID) );

		return $ins_res;
	}
	public function unLinkPremiseFromService($premNo, $svID) {
		$this->checkPermissionByCategory ( 'SVC', 'UPDATE' );
				
		$sql = "DELETE FROM SV_PREMISE_XREF where SPX_PREMISE_NUM = ? and SPX_SERVICE_ID = ? ";
		$del_res = $this->execUpdate ( $sql, array (
				$premNo,
				$svID 
		) );
			
		return $del_res;
	}
	// Reset batch edit session variables
	public function unsetBatchSession() {
		if ( isset($_SESSION['SERVICE_SELECTED_IDS']) ) {
			unset($_SESSION['SERVICE_SELECTED_IDS']);
		}
		if ( isset($_SESSION['PREFILL_DATA']) ) {
			unset($_SESSION['PREFILL_DATA']);
		}
		if ( isset($_SESSION['BATCH_COUNTER']) ) {
			unset($_SESSION['BATCH_COUNTER']);
		}
	}
	
}