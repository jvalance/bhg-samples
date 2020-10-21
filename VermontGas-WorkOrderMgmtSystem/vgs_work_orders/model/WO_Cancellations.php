<?php
require_once '../model/VGS_DB_Table.php';
require_once '../forms/VGS_Form.php';
require_once '../model/VGS_Mail.php';
require_once '../model/Code_Values_Master.php';
require_once 'Zend/Loader/Autoloader.php';
require_once '../model/Sec_Profiles.php';

class WO_Cancellations extends VGS_DB_Table
{
	const CANCEL_REQUEST_STATUS_PENDING = 'CNLPND';
	const CANCEL_REQUEST_STATUS_COMPLETE = 'CNLCMP';
	
	//---------------------------------------------------
	public function __construct($conn) {
    	parent::__construct($conn);

		Zend_Loader_Autoloader::getInstance();
    	
    	$this->tableName = 'WO_CANCELLATIONS';
		$this->tablePrefix = 'WCN_';
	   $this->keyFields = array('WCN_WO_NUM');
    	$this->hasAuditFields = true;
    	$this->isRecordDeletionAllowed = FALSE;
    }
     
	//---------------------------------------------------
    public function create( $rec ) {
    	$this->checkPermissionByCategory('WO', 'CREATE');
    	
    	if ($rec['isDollarsApplied'] != 'Y') {
    		$rec['WCN_CANCEL_TIME'] = date('Y-m-d-H.i.s.u');
    		$rec['WCN_CANCEL_STATUS'] = self::CANCEL_REQUEST_STATUS_COMPLETE;
    		$rec['WCN_CANCELLED_BY'] = $_SESSION['current_user'];
    		$rec['WCN_PREV_WO_STATUS'] = $rec['prevWOStatus'];
    	} else {
    		unset($rec['WCN_CANCEL_TIME']);
    		$rec['WCN_PREV_WO_STATUS'] = $rec['prevWOStatus'];
    		$rec['WCN_CANCEL_STATUS'] = self::CANCEL_REQUEST_STATUS_PENDING;
    	}    	

    	$this->autoCreateRecord($rec);
    	
    	$woObj = new Workorder_Master($conn);
    	$woRec['WO_NUM'] = $rec['WCN_WO_NUM'];
    	
    	if (!$rec['isDollarsApplied']) {
    		$woRec['WO_STATUS'] = Workorder_Master::WO_STATUS_CANCELLED;
    		$woObj->updateWorkOrder($woRec, FALSE);
    		$this->cancelWO_inLawson($woRec['WO_NUM'], 'WX');
    	} else {
    		$woRec['WO_STATUS'] = Workorder_Master::WO_STATUS_CANCEL_PENDING;
    		$woObj->updateWorkOrder($woRec, FALSE);
    		$this->cancelWO_inLawson($woRec['WO_NUM'], 'XP');
    		$this->sendCancellationEmail($rec);
    	}
    }
    
	 //---------------------------------------------------
    private function cancelWO_inLawson( $woNum, $lawsonSts ) {
    	$conn = VGS_DB_Conn_Singleton::getInstance();
    	$lawActivity = new VGS_DB_Table($conn);
    	$queryString = "update DBACACV set DACVUSRSTT = '$lawsonSts' where trim(DACVACTVTY) = '$woNum'";
    	return $lawActivity->execUpdate($queryString);
    }
    
	 //---------------------------------------------------
    public function update( $rec ) {
    	
    	if ($rec['COMPLETE_CANCEL'] == 'Y') {
    		$rec['WCN_CANCEL_TIME'] = date('Y-m-d-H.i.s.u');
    		$rec['WCN_CANCEL_STATUS'] = self::CANCEL_REQUEST_STATUS_COMPLETE;
    		$rec['WCN_CANCELLED_BY'] = $_SESSION['current_user'];
    	} 

    	$this->autoUpdateRecord($rec);
		
    	if ($rec['COMPLETE_CANCEL'] == 'Y') {
    		$conn = VGS_DB_Conn_Singleton::getInstance();
	    	$woObj = new Workorder_Master($conn);
	    	$woRec['WO_NUM'] = $rec['WCN_WO_NUM'];
	    	$woRec['WO_STATUS'] = Workorder_Master::WO_STATUS_CANCELLED;
	    	$woUpdSts = $woObj->updateWorkOrder($woRec, FALSE);
    		$lawCnlSts = $this->cancelWO_inLawson($woRec['WO_NUM'], 'WX');
    	}
    }	

	 //---------------------------------------------------
    public function delete( $rec ) {
    }	

	 //---------------------------------------------------
    public function retrieve( $data ) {
		$select = new VGS_DB_Select();
		$select->columns = 'wcn.*, cvSts.CV_VALUE as REASON_TEXT';
		$select->from = $this->tableName . ' as wcn ';
		$select->joins = "join workorder_master as wo on wcn_wo_num = wo_num";
		$select->joins = "join code_values_master as cvSts on cv_group = 'WCN_REASON_CODE' and cv_code = WCN_REASON_CODE";
		$select->andWhere("WCN_WO_NUM = ?", trim($data['WCN_WO_NUM']) );

		$this->execListQuery($select->toString(), $select->parms);
		//pre_dump($select->toString());
		$row = db2_fetch_assoc( $this->stmt );
		if (is_array($row)) {
			$row['WCN_CHANGE_TIME'] = VGS_Form::getTimeStampOutputFormat($row['WCN_CHANGE_TIME']);
			$row['WCN_CREATE_TIME'] = VGS_Form::getTimeStampOutputFormat($row['WCN_CREATE_TIME']);
			$row['WCN_CANCEL_TIME'] = VGS_Form::getTimeStampOutputFormat($row['WCN_CANCEL_TIME']);
    		$row = array_map('trim', $row);
		}
		return $row;
    }
 	
    //---------------------------------------------------
    public function retrieveByID( $woNum ) {
    	$data['WCN_WO_NUM'] = $woNum;
    	return $this->retrieve($data);
    }

	 //---------------------------------------------------
    private function sendCancellationEmail($rec) {
		$userObj = new Sec_Profiles($conn);
		$dd = new Code_Values_Master($conn);
		
		$user = trim($_SESSION['current_user']);
		$userRec = $userObj->retrieveByID($user);
		$userName = trim($userRec['PRF_DESCRIPTION']);

		$glCost = $rec['GL_ACCT_COST'];
		$glClose = $rec['GL_ACCT_CLOSE'];
		
		$cancelWONum = $rec['WCN_WO_NUM'];
		$transferWONum = $rec['WCN_TRFR_WO_NUM'];
		$parentFolder = VGS_DB_Conn_Singleton::getEnvBaseFolder();
		
		$cancelURL = "http://192.168.11.11:10080/$parentFolder/controller/" .
						"wcnEditCtrl.php?COMPLETE_CANCEL=Y&WCN_WO_NUM=$cancelWONum";
		
		$reasonCode = trim($rec['WCN_REASON_CODE']);
		if ($reasonCode == 'OTHER') {
			$reasonText = $rec['WCN_REASON_DESCRIPTION'];
		} else {
	  		$reasonText = $dd->getCodeValue('WCN_REASON_CODE', $rec['WCN_REASON_CODE']);
	  		if (trim($rec['WCN_REASON_DESCRIPTION']) != '') {
	  			$reasonText .= " ({$rec['WCN_REASON_DESCRIPTION']})";
	  		}
		}
		$actionText = 
			$rec['isTransferDollarsRequired']
				? "Dollars should be transferred to W/O# $transferWONum\n"
				: "Dollars should be reversed.\n";
			
		$bodyHTML = <<<BODYTXT
User $user - $userName, has requested cancellation of W/O# $cancelWONum, but dollars were applied.<br />
Cancellation is pending approval from accounting.<br />
<br />
<ul>
<li>$actionText</li>
<li>Reason for cancellation: {$rec['WCN_REASON_CODE']} - $reasonText.</li>
<li>G/L Acct Cost: $glCost</li>
<li>G/L Acct Close: $glClose</li>
</ul>
<br />
Click this link to <a href="$cancelURL">finalize cancellation of work order $cancelWONum</a>.<br />
BODYTXT;

  		$from = $dd->getCodeValue('WCN_EMAIL_ADDR', 'EMAIL_FROM');
  		$fromName = $dd->getCodeValue('WCN_EMAIL_ADDR', 'EMAIL_FROM_NAME'); 
  		$toList = $dd->getCodeValue('WCN_EMAIL_ADDR', 'EMAILS_TO'); 
		$subject = "W/O Pending Cancellation: {$rec['WCN_WO_NUM']}";

		$mail = new VGS_Mail();
		$mail->sendHtmlMail($from, $fromName, $toList, $subject, $bodyHTML);
    }
    
}