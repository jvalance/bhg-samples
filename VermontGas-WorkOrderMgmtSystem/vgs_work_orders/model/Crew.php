<?php
require_once '../model/VGS_DB_Table.php';
//require_once '../model/Code_Values_Master.php';

class Crew extends VGS_DB_Table
{
    public function __construct($conn) {
    	parent::__construct($conn);
    }

    public function retrieve( $crewId ) {
		$sql = <<<crew_retrieve
			select 	distinct WIECLK as id, 
					'VGS Crew: ' || trim(DEMPFRSTNM) || ' ' || trim(DEMPLSTNM) as name,
					'Employee' as type
			from WOCRWID 
			join vgs/PDHREMP on WIECLK = DEMPEMPLY 
			where WIECLK = ?
			
			union all
			
			select 	distinct SWID# as id, 
					SWDESC as name, 
					'SubContractor' as type
			from WOSCCWID
			where SWID# = ?
crew_retrieve;
//			DEMPEMPSTT in ('F', 'P') and 
//			DEMPDPRTMN = '42' and 
		
		$this->execListQuery($sql, array($crewId, $crewId));
		$row = db2_fetch_assoc( $this->stmt );
		return $row;
    }

    public function getCrewName( $crewId ) {
    	if (trim($crewId) != '') {
    		$rec = $this->retrieve($crewId);
    		return $rec['NAME'];
    	} else {
    		return '';
    	}
    }
    
    public function buildFilteredSelect(
       	array &$screenData, 
    	VGS_DB_Select $selectEmployee,
    	VGS_DB_Select $selectSubContractor,
    	VGS_Search_Filter_Group $filter	) 
	{
		$filter->addFilter('ID', 'Emp/Sub ID');
		$filter->setInputSize('ID', 3);
		$filter->setSpecialWhere('ID');
		
		$filter->addFilter('NAME', 'Name', 'LIKE');
		$filter->setInputSize('NAME', 25);
		$filter->setSpecialWhere('NAME');
		
		$filter->addFilter('TYPE', 'Type');
		$filter->setDropDownList('TYPE', array(''=>'', 'Employee'=>'Employee', "SubContractor"=>"SubContractor"));
		$filter->setSpecialWhere('TYPE');
		
		$filter->addFilter('STATUS', 'Status');
		$filter->setInputSize('STATUS', 1);
		$filter->setSpecialWhere('STATUS');
		
		$filter->addFilter('DEPT', 'Dept.');
		$filter->setInputSize('DEPT', 3);
		$filter->setSpecialWhere('DEPT');
		
		$filter->saveRestoreFilters($screenData);
		
		$selectEmployee->from = 'WOCRWID ';
		$selectEmployee->joins = 'join vgs/PDHREMP on WIECLK = DEMPEMPLY ';

		$selectEmployee->columns = 
			"distinct WIECLK as id, 
			'VGS Crew: ' || trim(DEMPFRSTNM) || ' ' || trim(DEMPLSTNM) as name,
			'Employee' as type,
			DEMPEMPSTT as status,
			DEMPDPRTMN as dept";
//		$selectEmployee->andWhere("DEMPEMPSTT in ('F', 'P')");
//		$selectEmployee->andWhere("DEMPDPRTMN = '42'");
		
		$selectSubContractor->from = 'WOSCCWID';
		$selectSubContractor->columns = 
			"SWID# as id, 
			SWDESC as name, 
			'SubContractor' as type,
			'' as Status,
			'' as dept";

		// Special WHERE conditions to handle filtering on two unioned tables
		if (isset($screenData['filter_ID'])	&& (trim($screenData['filter_ID']) != '')) {
			$selectEmployee->andWhere("WIECLK = ?", trim($screenData['filter_ID']));
			$selectSubContractor->andWhere("SWID# = ?", trim($screenData['filter_ID']));
		}

		if (isset($screenData['filter_NAME'])	&& (trim($screenData['filter_NAME']) != '')) {
    		$selectEmployee->andWhere("lower(trim(DEMPFRSTNM) || ' ' || trim(DEMPLSTNM)) LIKE ?", 
   				"%" .strtolower(trim($screenData['filter_NAME'])). "%" );
    		$selectSubContractor->andWhere("lower(trim(SWDESC)) LIKE ?", 
   				"%" .strtolower(trim($screenData['filter_NAME'])). "%" );
		}
		
		if (isset($screenData['filter_TYPE'])	&& (trim($screenData['filter_TYPE']) != '')) {
			$selectEmployee->andWhere("'Employee' = ?", trim($screenData['filter_TYPE']));
			$selectSubContractor->andWhere("'SubContractor' = ?", trim($screenData['filter_TYPE']));
		}
		
		if (isset($screenData['filter_DEPT'])	&& (trim($screenData['filter_DEPT']) != '')) {
			$selectEmployee->andWhere("DEMPDPRTMN = ?", trim($screenData['filter_DEPT']));
			$selectSubContractor->andWhere("1 = 0"); // Dept filter only applies to employee records
		}
		
		if (isset($screenData['filter_STATUS'])	&& (trim($screenData['filter_STATUS']) != '')) {
			$selectEmployee->andWhere("DEMPEMPSTT = ?", trim($screenData['filter_STATUS']));
			$selectSubContractor->andWhere("1 = 0"); // Status filter only applies to employee records
		}
		
    }	
    
}
