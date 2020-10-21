<?php
require_once '../forms/VGS_Form.php';
require_once '../model/WO_Cleanup.php';
require_once '../model/Workorder_Master.php';
require_once '../model/Premise.php';
/** 
 * @author John
 * 
 * 
 */
class WO_CleanupForm extends VGS_Form 
{
	private $db_object;
	private $wo_obj;
	
	public function __construct( $conn ) {
		parent::__construct ( $conn );

		$sec = new Security();
		$sec->checkPermissionByCategory('WO', $this->mode);
		
		$this->fh->addMetaData($this->conn, "WO_CLEANUP");
		$this->screen_title = 'W/O Cleanup ' . ucfirst($this->mode);
		$this->db_object = new WO_Cleanup($conn);
		$this->wo_obj = new Workorder_Master($conn);
		
		if (isset($_GET['WC_WONUM'])) {
			$this->wo_obj->getWorkorder($_GET['WC_WONUM']);
		} else {
			die("Invalid W/O#: Workorder Number required to work with cleanup records.");
		}
		
		$this->setDefaultElements( );
	}

	/**
	 * Sets cleanup status based on value of date cleanup completed
	 */
	private function setCleanupStatus() {
		$dateCompleted = trim($this->inputs['WC_DATE_COMPLETED']);
		if ($dateCompleted != '' && $dateCompleted != '0001-01-01') {
			$this->inputs['WC_CLEANUP_STATUS'] = 'Complete';	
		} else {
			$this->inputs['WC_CLEANUP_STATUS'] = 'Open';	
		}
	}
	public function createRecord() {
		$this->setCleanupStatus();
		return $this->db_object->create($this->inputs);
	}
	
	public function updateRecord() {
		$this->setCleanupStatus();
		return $this->db_object->update($this->inputs);
	}

	public function retrieveRecord() {
		return $this->db_object->retrieve($this->inputs);
	}
	
	public function setDefaultElements( ) 
	{
		$flGeneral = "WC_WONUM, WC_CLEANUP_NUM, WC_ORIGINAL_WO_TYPE, WC_CLEANUP_TYPE, WC_CLEANUP_STATUS,
							WC_ADDR_NO, WC_ADDR_STREET, WC_ADDR_CITY, WC_VENDOR_NUM";
		$this->fh->addFieldGroup( $flGeneral, 'general', 'General Information');
		$this->fh->setElementsProperties('WC_CLEANUP_NUM, WC_WONUM, WC_CLEANUP_STATUS', 'output_only', true);
		if ($this->wo_obj->record['WO_PREMISE_NUM'] != '0') {
			$this->fh->setElementsProperties('WC_ADDR_NO, WC_ADDR_STREET, WC_ADDR_CITY', 'output_only', true);
		} 

		$flComments = "WC_SPECIAL_INSTRUCTIONS, WC_COMMENTS";
		$this->fh->addFieldGroup( $flComments, 'comments', 'Instructions / Comments');
		$this->fh->setElementsProperties( 'WC_SPECIAL_INSTRUCTIONS', 'input_type', 'textarea');
		$this->fh->setElementsProperties( 'WC_COMMENTS', 'input_type', 'textarea');
		
		$flEstimates = "WC_ESTIMATED_SIZE_1, WC_ESTIMATED_SIZE_2, WC_EARLY_START_DATE, WC_LATE_FINISH_DATE";
		$this->fh->addFieldGroup( $flEstimates, 'estimates', 'Estimates');
		
		$flActual = "WC_DATE_COMPLETED, WC_ACTUAL_SIZE_1, WC_ACTUAL_SIZE_2, WC_COMPLETION_FOOTAGE, WC_REVISIT";
		$this->fh->addFieldGroup( $flActual, 'actuals', 'Actual / Completion');
		$this->fh->setElementsProperties('WC_REVISIT', 'input_type', 'y/n');
		
		$maintFieldList = 'WC_CREATE_USER, WC_CREATE_TIME, WC_CHANGE_USER, WC_CHANGE_TIME';
		$this->fh->addFieldGroup( $maintFieldList, 'maintenance', 'Record Maintenance Information');
		$this->fh->setElementsProperties($maintFieldList, 'output_only', true);
		
		if ( !$this->isInquiryMode()) {
			$requiredFields = 'WC_CLEANUP_TYPE, WC_EARLY_START_DATE, WC_LATE_FINISH_DATE';
			$this->fh->setElementsProperties($requiredFields, 'required', 'true');
		}
		
		$this->fh->setElementsProperties( 'WC_ORIGINAL_WO_TYPE, WC_CLEANUP_TYPE, WC_ADDR_CITY', 'input_type', 'select');
		$this->fh->setElementsProperties( 'WC_ORIGINAL_WO_TYPE', 'output_only', true);
		
		// This creates Zend_Form_Elements out of the meta data
		$this->fh->addElementsFromMetaData($this->mode);
		
		
		$cvm = new Code_Values_Master($this->conn);
		$cvList = $cvm->getCodeValuesList('WO_TYPE', '-- Unknown --');
		$this->fh->setMultiOptions('WC_ORIGINAL_WO_TYPE', $cvList);
		
		$cvList = $cvm->getCodeValuesList('WC_CLEANUP_TYPES', '-- Unknown --');
		$this->fh->setMultiOptions('WC_CLEANUP_TYPE', $cvList);
		
		$cvList = $cvm->getCodeValuesList('TOWN', '-- Unknown --');
		$this->fh->setMultiOptions('WC_ADDR_CITY', $cvList);
		
		$this->setName ( 'form1' );
		$this->addElements ( $this->fh->getElements() );

		$this->getElement('WC_ESTIMATED_SIZE_1')->setLabel('Estimated Size');
		$this->getElement('WC_ESTIMATED_SIZE_2')->setLabel('X');
		$this->getElement('WC_ACTUAL_SIZE_1')->setLabel('Actual Size');
		$this->getElement('WC_ACTUAL_SIZE_2')->setLabel('X');
		
		$this->getElement('WC_CREATE_TIME')->setAttrib('size', 30);
		$this->getElement('WC_CHANGE_TIME')->setAttrib('size', 30);
	}
	
	public function reset() {
		parent::reset();
		$woType = $this->wo_obj->record['WO_TYPE'];
		// In create mode, populate form with WO_NUM passed on request
		$this->getElement('WC_WONUM')->setValue($_GET['WC_WONUM']);
		$this->getElement('WC_ORIGINAL_WO_TYPE')->setValue($woType);
		$this->getElement('WC_CLEANUP_STATUS')->setValue('Open');
		if ($this->wo_obj->record['WO_PREMISE_NUM'] != '0') {
			$prem = new Premise($this->conn);
			$premRec = $prem->retrieve($this->wo_obj->record['WO_PREMISE_NUM']);
			
			$addrNo = trim($premRec['UPSH#']) . ' ' . 
						trim($premRec['UPSFR']) . ' ' . 
						trim($premRec['UPSSP']);
			
			$street = trim($premRec['UPSST']) . ' ' . 
						trim($premRec['UPSSF']) . ' ' . 
						trim($premRec['UPSSR']) . ' ' . 
						trim($premRec['UPSSA']);
						
//			$this->getElement('WC_ADDR_NO')->setValue(trim($addrNo));
			$this->getElement('WC_ADDR_STREET')->setValue(trim($premRec['UPSAD']));
		
//			$cvm = new Code_Values_Master($this->conn);
//			$cvList = $cvm->getCodeValuesList('TOWN', '-- Unknown --');
//			$this->fh->setMultiOptions('WC_ADDR_CITY', $cvList);
			$town = str_pad($this->wo_obj->record['WO_TAX_MUNICIPALITY'],4,' ',STR_PAD_RIGHT);
			$this->getElement('WC_ADDR_CITY')->setValue($town);
		} 
		$premNo = $this->wo_obj->record['WO_PREMISE_NUM'];
//		echo "Premise = $premNo<P>Town = '$town'";
	}
	
	public function validate() {
		// If WC_LATE_FINISH_DATE is blank, set it to WC_EARLY_START_DATE + 15 days
		if (trim($this->inputs['WC_LATE_FINISH_DATE']) == '' 
		&&  trim($this->inputs['WC_EARLY_START_DATE']) != '') {
			$earlyStartDate = DateTime::createFromFormat('m-d-Y', $this->inputs['WC_EARLY_START_DATE']);
			if ($earlyStartDate) {
				$earlyStartMDY = $earlyStartDate->format('m/d/Y');
				$lateFinish = strtotime($earlyStartMDY . ' + 15 days');
				$lateFinish = date('m-d-Y', $lateFinish); 
				$this->inputs['WC_LATE_FINISH_DATE'] = $lateFinish;
			}
		}

		// Validate inputs
		$this->valid = parent::isValid($this->inputs);
		return $this->valid;
	}
	
	public function populate(array &$data) 
	{
		parent::populate($data);
	}
}

//$townXRef = array(
//	".SAC" => "SAC",
//	"BULR" => "BUR",
//	"BUR " => "BUR",
//	"BURL" => "BUR",
//	"CLO " => "COL",
//	"COCL" => "COL",
//	"COL " => "COL",
//	"COL." => "COL",
//	"COLC" => "COL",
//	"E.J " => "EJV",
//	"E.J." => "EJV",
//	"E.JJ" => "EJV",
//	"EJ  " => "EJV",
//	"EJV " => "EJV",
//	"ESS " => "ESX",
//	"ESSE" => "ESX",
//	"ESSX" => "ESX",
//	"ESX " => "ESX",
//	"GEO " => "GEO",
//	"GEO." => "GEO",
//	"GEOR" => "GEO",
//	"HGT " => "HGT",
//	"HIGH" => "HGT",
//	"HINE" => "HINS",
//	"HINS" => "HINS",
//	"JER " => "JER",
//	"JERI" => "JEID",
//	"MIL " => "MLT",
//	"MIL." => "MLT",
//	"MILT" => "MLT",
//	"MITL" => "MLT",
//	"MLT " => "MLT",
//	"MLTT" => "MLT",
//	"MLV " => "MLV",
//	"RICH" => "RICH",
//	"S,B." => "SOB",
//	"S.B " => "SOB",
//	"S.B." => "SOB",
//	"SAC " => "SAC",
//	"SAT " => "SAT",
//	"SB  " => "SOB",
//	"SBR " => "SOB",
//	"SHD " => "SHD",
//	"SHEL" => "SHL",
//	"SHL " => "SHL",
//	"SHLB" => "SHL",
//	"SHLD" => "SHD",
//	"SO.B" => "SOB",
//	"SOB " => "SOB",
//	"ST A" => "SAT",
//	"ST,A" => "SAT",
//	"ST.A" => "SAT",
//	"STA " => "SAT",
//	"SWA." => "SWT",
//	"SWAN" => "SWT",
//	"SWT " => "SWT",
//	"SWV " => "SWV",
//	"UHL " => "UHL",
//	"UND " => "UHL",
//	"WIL " => "WLT",
//	"WIL." => "WLT",
//	"WILL" => "WLT",
//	"WILT" => "WLT",
//	"WIN " => "WIN",
//	"WIN." => "WIN",
//	"WINO" => "WIN",
//	"WIT " => "WLT",
//	"WLT " => "WLT"
//	);
