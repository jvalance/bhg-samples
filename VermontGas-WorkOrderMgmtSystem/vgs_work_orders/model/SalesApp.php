<?php
require_once '../model/VGS_DB_Table.php';

class SalesApp extends VGS_DB_Table
{
    public function __construct($conn) {
    	parent::__construct($conn);
    	$this->tableName = 'SLSAPP';
    	// Following not needed for UPRM - since no maintenance allowed
		$this->tablePrefix = 'SL';
	   $this->keyFields = array('SLSAP#');
    	$this->hasAuditFields = false;
    }

    public function retrieve( $slsAppNo ) {
		$select = new VGS_DB_Select();
		$select->from = $this->tableName . ' as sa ';
		$select->joins = "LEFT JOIN UPRM prm on sa.SLSBKF = prm.UPPRM
						  LEFT JOIN SLFEAS fs on sa.SLSFS# = fs.SLFFS#";
		$select->andWhere("trim(SLSAP#) = ?", trim($slsAppNo) );
//		pre_dump($select->toString());
//		pre_dump($select->parms);
		$this->execListQuery($select->toString(), $select->parms);
		$row = db2_fetch_assoc( $this->stmt );
		return $row;
    }

    public function retrieveByPremiseNo( $premiseNo ) {
		$select = new VGS_DB_Select();
		$select->from = $this->tableName . ' as sa ';
		$select->joins = "LEFT JOIN UPRM prm on sa.SLSBKF = prm.UPPRM
						  LEFT JOIN SLFEAS fs on sa.SLSFS# = fs.SLFFS#";
		$select->andWhere("trim(sa.SLSBKF) = ?", trim($premiseNo) );
		$select->andWhere("sa.SLSAP# = (select min(sa2.SLSAP#) from SLSAPP sa2 where trim(sa2.SLSBKF) = ?)", trim($premiseNo) );
//		pre_dump($select->toString());
//		pre_dump($select->parms);
		$this->execListQuery($select->toString(), $select->parms);
		$row = db2_fetch_assoc( $this->stmt );
		return $row;
    }
    
    public function getSlsAppsForWO( $woNum ) {
		$select = new VGS_DB_Select();
		$select->columns = "SLSAP# as SLSAPP, 
							SLSBKF as PREMNO, 
							trim(UPSAD) as ADDRESS, 
							trim(UPSSA) as APT, 
							trim(SLSSMN) as SLSMN";
		$select->from = 'WO_SLSAPP_XREF as xrf ';
		$select->joins = "JOIN SLSAPP sa on SLX_SLSAPP_NUM = SLSAP#
						  LEFT JOIN UPRM prm on sa.SLSBKF = prm.UPPRM";
		$select->andWhere("SLX_WO_NUM = ?", trim($woNum));
//		pre_dump($select->toString());
//		pre_dump($select->parms);
		$this->execListQuery($select->toString(), $select->parms);
		$rows = array();
		while ( $row = db2_fetch_assoc($this->stmt) ) {
			$rows[] = $row;
		}
		return $rows;
    	
    }
    
    public function linkSlsAppToWO( $slsAppNo, $woNum ) {
    	$this->checkPermissionByCategory('WO', 'CREATE');
    	
    	$select = new VGS_DB_Select();

		// See if the link already exists
		$select->columns = "SLX_SLSAPP_NUM"; 
		$select->from = 'WO_SLSAPP_XREF as xrf ';
		$select->andWhere("SLX_WO_NUM = ?", trim($woNum));
		$select->andWhere("SLX_SLSAPP_NUM = ?", trim($slsAppNo));
		$this->execListQuery($select->toString(), $select->parms);
		$row = db2_fetch_assoc($this->stmt);
		// If row found, return true
		if (is_array($row)) {
			return true;
		}
		
		// If link not found, create it.
		$sql = "INSERT INTO WO_SLSAPP_XREF (SLX_SLSAPP_NUM, SLX_WO_NUM) values (?, ?)";
    	$ins_res = $this->execUpdate($sql, array($slsAppNo, $woNum));
    	
    	// Update SLSAPP with WO# and status
		$upd_res = true;
		if ($ins_res) {
			$sql = "UPDATE SLSAPP set SLSWO# = ?, SLSWOS = 'P' WHERE SLSAP# = ?";
    		$upd_res = $this->execUpdate($sql, array($woNum, $slsAppNo));
    	}
    	return $ins_res && $upd_res;
    }
    
    public function unLinkSlsAppFromWO( $slsAppNo, $woNum ) {
    	$this->checkPermissionByCategory('WO', 'UPDATE');
    	
    	// If link not found, create it.
		$sql = "DELETE FROM WO_SLSAPP_XREF where SLX_SLSAPP_NUM = ? and SLX_WO_NUM = ? ";
    	$del_res = $this->execUpdate($sql, array($slsAppNo, $woNum));
    	
    	// Update SLSAPP to remove WO# and clear status
		$upd_res = true;
		if ($del_res) {
			$sql = "UPDATE SLSAPP set SLSWO# = 0, SLSWOS = ' ' WHERE SLSAP# = ?";
    		$upd_res = $this->execUpdate($sql, array($slsAppNo));
    	}
    	return $del_res && $upd_res;
    }
    
    public function updateSlsAppForWOCompletion( $woNum, $woCompletionDate ) {
    	$this->checkPermissionByCategory('WO', 'UPDATE');
    	
    	$installDate = VGS_Form::convertDateFormat($woCompletionDate, 'Y-m-d', 'Ymd');
    	$completion_result = true;

    	$sel = new VGS_DB_Select;
    	$sel->from = 'WO_SLSAPP_XREF';
    	$sel->andWhere("SLX_WO_NUM = $woNum");

    	$xRefStmt = db2_prepare($this->db2connection, $sel->toString());
    	db2_execute($xRefStmt);
		
		// Loop through all SLSAPPs linked to this work order and update each of them.
		while ($slsAppXrefRec = db2_fetch_assoc($xRefStmt)) {
			$slsAppNo = $slsAppXrefRec['SLX_SLSAPP_NUM'];
			$slsAppRec = $this->retrieve($slsAppNo);
			
			if (is_array($slsAppRec)
			 && $slsAppRec['SLSWOS'] != 'I' 
			 && $slsAppRec['STATUS'] != 'B'
			 && $slsAppRec['STATUS'] != 'W'
			 && $slsAppRec['STATUS'] != 'R') 
			{
				$status = 'I';
				$slsidt = $installDate;
			} else {
				$status = $slsAppRec['STATUS'];
				$slsidt = $slsAppRec['SLSIDT'];
			}
			
			$sql = "UPDATE SLSAPP 
						set STATUS = '$status', 
						SLSIDT = '$slsidt',
						SLSWOS = 'I' 
					 WHERE SLSAP# = $slsAppNo";
    		$updres = db2_exec($this->db2connection, $sql);
    		$completion_result = $completion_result && $updres;
		}
    	return $completion_result;
    }
    
    public function updateSlsAppFor_ST_Completion( $slsAppNum ) {
    	$this->checkPermissionByCategory('WO', 'UPDATE');
			
    	// When completing an ST w/o, set slsapp status to 'W'
		$sql = "UPDATE SLSAPP set STATUS = 'W' WHERE SLSAP# = $slsAppNum";
  		$updres = db2_exec($this->db2connection, $sql);
    	return $updres;
    }
    
    
    public function buildFilteredSelect(
      array &$screenData, 
    	VGS_DB_Select $select,
    	VGS_Search_Filter_Group $filter	) 
	{
		
		$woNum = $screenData['filter_WONUM'];
		if (!isset($woNum) ) die('W/O Number is required.');
		
		$select->from = 'SLSAPP as sa';
		$select->joins = "LEFT JOIN WO_SLSAPP_XREF as xrf on SLX_WO_NUM = $woNum and SLX_SLSAPP_NUM = SLSAP# 
						  LEFT JOIN UPRM prm on sa.SLSBKF = prm.UPPRM"; 
		$select->order = 'SLSAP#';
		
		// Drop-down - to show only sales apps linked to current WO#
		$filter->addFilter('WONUM_ONLY', 'Linked Only?');
		$filter->setSpecialWhere('WONUM_ONLY');
		$linkedOnlyChoices = array('' => 'All', 'L' => "Linked to $woNum");
		$filter->setDropDownList('WONUM_ONLY', $linkedOnlyChoices); 
		
		$filter->addFilter('FROM_SLSAPP', 'From SlsApp#', '>=');
		$filter->setSpecialWhere('FROM_SLSAPP');
		$filter->setInputSize('FROM_SLSAPP', 4);
		
		$filter->addFilter('TO_SLSAPP', 'To SlsApp#', '<=');
		$filter->setSpecialWhere('TO_SLSAPP');
		$filter->setInputSize('TO_SLSAPP', 4);
		
		$filter->addFilter('FROM_PREMISE', '<br>From Premise', '>=');
		$filter->setSpecialWhere('FROM_PREMISE');
		$filter->setInputSize('FROM_PREMISE', 4);
		
		$filter->addFilter('TO_PREMISE', 'To Premise', '<=');
		$filter->setSpecialWhere('TO_PREMISE');
		$filter->setInputSize('TO_PREMISE', 4);
		
		$filter->addFilter('UPSAD', 'Svc Address', 'LIKE');
		$filter->setInputSize('UPSAD', 15);
		
		$filter->addFilter('UPCTC', 'City', 'LIKE');
		$filter->setInputSize('UPCTC', 10);
		
		//-------------------------------------------------
		$filter->saveRestoreFilters($screenData);
		//-------------------------------------------------
		
		// If only linked SlsApps requested, ignore all other filters 
		if (isset($screenData['filter_WONUM_ONLY'])
		&& trim($screenData['filter_WONUM_ONLY']) == 'L') {
			$select->andWhere("SLX_WO_NUM = ?", trim($woNum));
		} else {
			//-------------------------------------------------
			$filter->renderWhere($screenData, $select);
			//-------------------------------------------------
			// Show either SlsApps not linked to any workorder, or those linked to this w/o
			$select->andWhere("SLSWO# = 0 or SLX_WO_NUM = ?", trim($woNum));
		
			// SLSSMN Filter - special where 
			if (isset($screenData['filter_SLSSMN']) 
				&& trim($screenData['filter_SLSSMN']) != '') {
				$select->andWhere("trim(SLSSMN) = ?", trim(strtoupper($screenData['filter_SLSSMN'])));
			}
		
			// FROM_SLSAPP Filter - special where 
			if (isset($screenData['filter_FROM_SLSAPP']) 
				&& trim($screenData['filter_FROM_SLSAPP']) != '') {
				$select->andWhere("SLSAP# >= ?", trim($screenData['filter_FROM_SLSAPP']));
			}
		
			// TO_SLSAPP Filter - special where 
			if (isset($screenData['filter_TO_SLSAPP']) 
				&& trim($screenData['filter_TO_SLSAPP']) != '') {
				$select->andWhere("SLSAP# <= ?", trim($screenData['filter_TO_SLSAPP']));
			}
		
			// FROM_PREMISE Filter - special where 
			if (isset($screenData['filter_FROM_PREMISE']) 
				&& trim($screenData['filter_FROM_PREMISE']) != '') {
				$select->andWhere("SLSBKF >= ?", trim($screenData['filter_FROM_PREMISE']));
			}
		
			// TO_PREMISE Filter - special where 
			if (isset($screenData['filter_TO_PREMISE']) 
				&& trim($screenData['filter_TO_PREMISE']) != '') {
				$select->andWhere("SLSBKF <= ?", trim($screenData['filter_TO_PREMISE']));
			}
						
		}
		
    }	
    
}