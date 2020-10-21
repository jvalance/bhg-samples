<?php
require_once '../model/VGS_DB_Table.php';
require_once '../model/Code_Values_Master.php';

class Project_Master extends VGS_DB_Table
{
    public function __construct($conn) {
    	parent::__construct($conn);
    	$this->tableName = 'PROJECT_MASTER';
    	$this->tablePrefix = 'PRJ_';
	   $this->keyFields = array('PRJ_NUM');
    	$this->hasAuditFields = true;
    }
     
    public function create( $rec ) {
    	$rec['PRJ_NUM'] = $this->getNextProjNum();
    	$this->checkPermissionByCategory('PROJ', 'CREATE');
    	
    	// Retrieve or create feasibility record
		$feasNum = $rec['PRJ_FEASABILITY_NUM'];
		$feasRec = $this->getFeasibilityRec($feasNum);
		if ($feasRec == NULL) {
			$this->createFeasibilityRec($rec);
		}
		// Set feasibility date to current date on project creation.
		$rec['PRJ_FEASABILITY_DATE'] = date('Y-m-d');  
		$prjRes = $this->autoCreateRecord($rec);
    	
    	// Call the Lawson interface procedure.
    	$projNum = $rec['PRJ_NUM'];
		$projAlphaNum = str_pad($projNum, 5, '0', STR_PAD_LEFT); 
    	$lawsonEnv = VGS_DB_Conn_Singleton::getLawsonEnvironment();
		$spCall = "call spProjCreate_LawsonInterface('$projAlphaNum', '$lawsonEnv');";
    	$lawRes = $this->execUpdate($spCall); 
    	
    	return $prjRes && $lawRes; 
    }

   private function createFeasibilityRec( $data ) {
		//		SLFFS#     Feasibility Number                              7,0 S
		//		SLFNAM     Project Name                                     20 A
		//		SLFDES     Project Description                              50 A
		//		SLFSMN     Salesman Initials                                 3 A
		//		SLFSDT     Date Submitted                                  8,0 P
		//		SLFADT     Date Approved                                   8,0 P
		//		SLFABY     Approved By                                       3 A
		//		SLFCYR     Year Project Completed                          4,0 S
		//		SLFELD     Estimated Load                                  7,0 P
		//		SLFALD     Actual load                                     7,0 P
		//		SLFEDM     Estimated Demand                                7,0 P
		//		SLFADM     Actual Demand                                   7,0 P
		//		SLFECT     Estimt.# of Customers                           3,0 P
		//		SLFACT     Actual # of Customers                           3,0 P
		//		SLFESV     Estimt # of Services                            3,0 P
		
	   	$data = array_map(db2_escape_string, $data);
	   	$desc20 = substr($data['PRJ_DESCRIPTION'], 0, 20);
		$feasDate = date('mdY');
		
		$sqlFeasInsert = <<<INS_FEAS_SQL
			INSERT INTO SLFEAS 
				(SLFFS#, SLFNAM, SLFDES, SLFSMN, SLFSDT, SLFCTY)
			VALUES (
				{$data['PRJ_FEASABILITY_NUM']}, 
				'$desc20', 
				'{$data['PRJ_DEVELOPER']}', 
				'{$data['PRJ_SALES_REP']}', 
				$feasDate, 
				'{$data['PRJ_MUNICIPALITY_CODE']}'
			)
INS_FEAS_SQL;

		$this->execUpdate($sqlFeasInsert);	
		return $feasDate;
   }
   
   public function getFeasibilityRec( $feasNum ) {
		if ((int) $feasNum > 0) {
			$sel = new VGS_DB_Select();
			$sel->from = 'SLFEAS';
			$sel->andWhere("SLFFS# = $feasNum");
			$this->execListQuery($sel->toString());
			$feasRec = db2_fetch_assoc($this->stmt);
			if (is_array($feasRec)) {
				return $feasRec;
			}
		}
		return NULL;    	
   }
    
    private function updateLawsonActivityRec($projRec) {
    	$projNum = $projRec['PRJ_NUM'];
		$sel = new VGS_DB_Select();
		$sel->from = 'dbacacv';
		$sel->andWhere("dacvcmpny = 2"); 
		$projAlphaNum = str_pad($projNum, 5, '0', STR_PAD_LEFT); 
		$sel->andWhere("dacvactvty = 'PROJ$projAlphaNum'"); 
		$this->execListQuery($sel->toString(), $sel->parms);

		if ( $activityRec = db2_fetch_assoc( $this->stmt )) {
			$activityStatus = $activityRec['DACVUSRSTT'];
			$endDate = '';
			switch ($projRec['PRJ_STATUS']) {
				case 'P':
					$activityStatus = 'PP';
					break;
				case 'C':
					$activityStatus = 'PC';
					if ($projRec['PRJ_COMPLETION_DATE'] != '0001-01-01') {
						$endDate = $projRec['PRJ_COMPLETION_DATE'];
					}
					break;
				case 'F':
					$activityStatus = 'PF';
					break;
				case 'H':
					$activityStatus = 'PH';
					break;
				case 'X':
					$activityStatus = 'PX';
					break;
			}
			$lawsonTable = 'dbacacv';
			$updateActivitySQL = "update $lawsonTable set DACVUSRSTT = '$activityStatus' ";
			if ($endDate != '') {
				$updateActivitySQL .= ", DACVENDDT = '$endDate'";
			}
			$updateActivitySQL .= " where dacvcmpny = 2 and dacvactvty = 'PROJ$projAlphaNum'";
			//echo $updateActivitySQL;
			return $this->execUpdate($updateActivitySQL);
			
		}	
		
    }
   
    public function update( $rec ) {
    	$this->checkPermissionByCategory('PROJ', 'UPDATE');
    	$prjRes = $this->autoUpdateRecord($rec);
    	$lawRes = $this->updateLawsonActivityRec($rec);
    	return $prjRes && $lawRes; 
    }	
    
    public function retrieve( $data ) {
		$select = new VGS_DB_Select();
		$select->from = $this->tableName;
		$select->andWhere("PRJ_NUM = ?", trim($data['PRJ_NUM']) );

		$this->execListQuery($select->toString(), $select->parms);
		if ( $row = db2_fetch_assoc( $this->stmt )) {
			// Uncomment these lines when audit fields are added to the table
			// $row['PRJ_CHANGE_TIME'] = VGS_Form::getTimeStampOutputFormat($row['PRJ_CHANGE_TIME']);
			// $row['PRJ_CREATE_TIME'] = VGS_Form::getTimeStampOutputFormat($row['PRJ_CREATE_TIME']);
			return $row;
		} else {
			return NULL;
		}
    }

    public function retrieveById( $projectNum ) {
    	$data = array('PRJ_NUM' => $projectNum);
    	return $this->retrieve($data);
    }

    public function retrieveByFeasibilityNum( $feasibilityNum ) {
		$select = new VGS_DB_Select();
		$select->from = $this->tableName;
		$select->andWhere("PRJ_FEASABILITY_NUM = ?", trim($feasibilityNum));

		$this->execListQuery($select->toString(), $select->parms);
		if ( $row = db2_fetch_assoc( $this->stmt )) {
			return $row;
		} else {
			return NULL;
		}
    }
    
    public function getProjectDescription ( $projectNum ) {
    	$projRec = $this->retrieveById($projectNum);
    	if (isset($projRec)) {
    		return $projRec['PRJ_DESCRIPTION'];
    	} else {
    		return NULL;
    	}
    }

    public function getNextProjNum() {
    	$select = new VGS_DB_Select();
    	$select->from = $this->tableName;
    	$select->columns = 'max(PRJ_NUM) as LAST_PRJ_NUM';
        $maxAry = $this->fetchRow($select->toString());
        return (int)$maxAry['LAST_PRJ_NUM'] + 1;
    }

    public function getNextFeasibilityNum() {
    	$select = new VGS_DB_Select();
    	$select->from = 'SLFEAS';
    	$select->columns = 'max(SLFFS#) as LAST_FEAS_NUM';
    	$select->andWhere("substr(digits(SLFFS#),5,1) <> '9'");
        $maxAry = $this->fetchRow($select->toString());
        return (int)$maxAry['LAST_FEAS_NUM'] + 1;
    }
 
    public static function getWOCountForProject( $conn, $projNum, $where = NULL ) {
    	$select = new VGS_DB_Select();
    	$select->from = 'WORKORDER_MASTER';
    	$select->columns = 'count(*) as WO_COUNT';
    	$select->andWhere("WO_PROJECT_NUM = $projNum");
    	if ($where != NULL) $select->andWhere($where);
    	$dbTable = new VGS_DB_Table($conn);
    	$aryCount = $dbTable->fetchRow($select->toString());
        return (int)$aryCount['WO_COUNT'];
    }
    
   
    public function buildFilteredSelect(
      array &$screenData, 
    	VGS_DB_Select $select,
    	VGS_Search_Filter_Group $filter
	) 
	{
		$filter->addFilter('PRJ_NUM', 'Proj#');
		$filter->setInputSize('PRJ_NUM', 3);
		$filter->addFilter('PRJ_DESCRIPTION', 'Description', 'LIKE');
		$filter->setInputSize('PRJ_DESCRIPTION', 15);
		$filter->addFilter('PRJ_STATUS', 'Status');
		$filter->addFilter('PRJ_CONTACT_PERSON', 'Contact', 'LIKE');
		$filter->addFilter('PRJ_FEASABILITY_NUM', '<br>Feas#');
		$filter->setInputSize('PRJ_FEASABILITY_NUM', 3);
		$filter->addFilter('PRJ_MUNICIPALITY_CODE', 'Town');
		$filter->addFilter('PRJ_ZONE', 'Zone');
		$filter->setInputSize('PRJ_ZONE', 3);
		$filter->addFilter('PRJ_CAP_EXP_CODE', 'Cap/Exp');
		
		$cvm = new Code_Values_Master($this->db_conn_obj);
		$cvList = $cvm->getCodeValuesList('TOWN', '-- All --');
		$filter->setDropDownList('PRJ_MUNICIPALITY_CODE', $cvList);
		 
		$cvList = $cvm->getCodeValuesList('PRJ_STATUS', '-- All --');
		$filter->setDropDownList('PRJ_STATUS', $cvList);
		 
		$cvList = $cvm->getCodeValuesList('PT_CAP_EXP', '-- All --');
		$filter->setDropDownList('PRJ_CAP_EXP_CODE', $cvList);

		if (isset($screenData['PT_CAP_EXP'])) {
			$filter->setDefaultValue('PRJ_CAP_EXP_CODE', $screenData['PT_CAP_EXP']);
		}
		
		$filter->saveRestoreFilters($screenData);
		
		$select->from = $this->tableName;
		$select->order = 'PRJ_NUM DESC';
		
		$filter->renderWhere($screenData, $select);
		
    }	
    
}