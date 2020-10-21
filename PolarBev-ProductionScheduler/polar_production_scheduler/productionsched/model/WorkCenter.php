<?php
require_once '../common/DB_Conn.php';
require_once '../common/DB_Table.php';
require_once '../common/DB_Select.php';

class WorkCenter extends polarbev\DB_Table {

	function __construct() {
		parent::__construct ();
	}
	public function getWorkCenterRec( $workCenter )
	{
		$sql = "select * from LWK where WWRKC = ?";
		return $this->fetchRow($sql, array($workCenter));

	}

	public function getMfgMethRoutingCode( $prod, $whs, $wrkctr ) {
		$prod = trim($prod);
		$whs = trim($whs);
		$wrkctr = trim($wrkctr);

		$sql = <<<getMMRCode
			select RTRTEM
			from FRT
			where trim(RPROD) = ?
			and RTWHS = ?
			and ROPNO = 10
			and RWRKC = ?
getMMRCode;
		$row = $this->fetchRow($sql, array($prod, $whs, $wrkctr));
		if (is_array($row))
			return $row['RTRTEM'];
		else
			return '';
	}

	/**
	 * Retrieve a multi-dim array of all facilities.
	 * @return array: List of all rows/columns for all records in Facilty Master table (ZMF)
	 */
	public function getFacilitiesList() {
		$facs = array();
		$sql = "select * from ZMF where MFCPUT = 'AUTH' order by MFDESC";
		$rs = $this->execListQuery($sql);
		if ($rs) {
			while ($row = db2_fetch_assoc($this->stmt)) {
				$facs[] = array_map('trim', $row);
			}
		}
		return $facs;
	}

	/**
	 * Retrieve a multi-dim array of all facilities.
	 * @return array: List of all rows/columns for all records in Facilty Master table (ZMF)
	 */
	public function getFacilityRec( $facility ) {
	    $sql = "select * from ZMF where MFFACL = ?";
	    return $this->fetchRow($sql, array($facility));
	}

	/**
	 * Retrieve a string of option tags for a select list of all Facilities.
	 * @return array: List of all rows/columns for all records in Facilty Master table (ZMF)
	 */
	public function getFacilitiesSelectOptions(
			$blankValue = ' ', $selectedFacility = NULL)
	{
		$facs = $this->getFacilitiesList();
		$optionsList = buildSelectOptionsFromRecord($facs, 'MFFACL', 'MFFACL-MFDESC', $selectedFacility, $blankValue);
		return $optionsList;
	}

	/**
	 * Retrieve an array of all facilities, for use in an auto-complete Facility input field.
	 * @return array: Array of all facilities, for use in an auto-complete Facility input field.
	 */
	public function getFacilitiesAutoCompleteList() {
		$facs = $this->getFacilitiesList();
		$autoCompList = array();
		foreach ($facs as $workCtr) {
			$autoCompText = "{$workCtr['MFFACL']} {$workCtr['MFDESC']} {$workCtr['MFAD1']} {$workCtr['MFAD2']} {$workCtr['MFAD3']} {$workCtr['MFSTE']} {$workCtr['MFZIP']} ";
			$autoCompList[$workCtr['MFFACL']] = $autoCompText;
		}
		return $autoCompList;
	}

	/**
	 * Retrieve a multi-dim array of all work centers.
	 * @return array: List of all rows/columns for all records in Work Center Master table (LWK)
	 */
	public function getWorkCentersList() {
		$wrk_ctrs = array();
		$sql = "select lwk.*, zmf.MFDESC
				from LWK
				left join ZMF on WFAC = MFFACL";
		$rs = $this->execListQuery($sql);
		if ($rs) {
			while ($row = db2_fetch_assoc($this->stmt)) {
				$wrk_ctrs[] = array_map('trim', $row);
			}
		}
		return $wrk_ctrs;
	}

	/**
	 * Retrieve a multi-dim array of all work centers.
	 * @return array: List of all rows/columns for all records in Work Center Master table (LWK)
	 */
	public function getFacilityWorkCenters() {
		$sql = "select * from LWK order by WFAC";
		$rs = $this->execListQuery($sql);
		if ($rs) {
			$wc = '';
			$facWCs = array();
			while ($row = db2_fetch_assoc($this->stmt)) {
				$wcs = array_map('trim', $row);
				$facWCs[$wcs['WFAC']][$wcs['WDESC']] = $wcs['WWRKC'];
			}
			return $facWCs;
		}
		return false;
	}

	// 	'WO' :
	// 	{'WORCESTER LINE #1' : 1,
	// 	'WORCESTER LINE #2' : 2,
	// 	'WORCESTER LINE #3' : 3 },
	// 	'WX' :
	// 	{'FITZGERALD LINE #1 2 LITERS' : 31,
	// 	'FITZGERALD LINE #2 1 LITERS' : 32,
	// 	'FITZGERALD LINE #3 CANS' :     33  }


	/**
	 * Retrieve a string of option tags for a select list of all Facilities.
	 * @return array: List of all rows/columns for all records in Facilty Master table (ZMF)
	 */
	public function getWorkCentersSelectOptions(
			$blankValue = ' ', $selectedWorkCenter = NULL)
	{
		$facs = $this->getWorkCentersList();
		$optionsList = buildSelectOptionsFromRecord($facs, 'WKPLN', 'WDESC', $selectedWorkCenter, $blankValue);
		return $optionsList;
	}

	/**
	 * Retrieve an array of all facilities, for use in an auto-complete Facility input field.
	 * @return array: Array of all facilities, for use in an auto-complete Facility input field.
	 */
	public function getWorkCentersAutoCompleteList() {
		$wrk_ctrs = $this->getWorkCentersList();
		$autoCompList = array();
		foreach ($wrk_ctrs as $workCtr) {
			$autoCompText = "{$workCtr['WKPLN']} {$workCtr['WDESC']}; Fac: {$workCtr['WKPLN']}-{$workCtr['MFDESC']} ";
			$autoCompList[$workCtr['WKPLN']] = $autoCompText;
		}
		return $autoCompList;
	}

	public function createUserLock($data) {

		$lock_status = $this->readUserLock($data);

		if (! $lock_status) {
			$sql  = "INSERT INTO KSCDLK
			(SLFAC, SLWRKC, SLUSER, SLSCRTOK, SLLSTACTTM)
			VALUES(?, ?, ?, ?, CURRENT_TIMESTAMP)";

			$values = array (
					$data["facility"],
					$data["work_ctr"],
					$_SESSION["current_user"],
					" "
			);

			$this->execUpdate($sql,$values);
		}

		return	$lock_status;
	}

	public function readUserLock($data) {
		// Return array of data regarding lock on given work center.
		// If no lock, return false. If expired lock, remove it and return false.

		// This constant represents number of minutes until lock expires.
		// It is presently a placeholder value.
		// The plan is to replace it with a value pulled from a db table.
		$TIMEOUT = 30;

		$facility = $data["facility"];
		$work_ctr = $data["work_ctr"];

		// Check to see if there is a lock present.
		$sql  = "SELECT SLFAC, SLWRKC, SLUSER, SLSCRTOK, SLLSTACTTM,
		TIMESTAMPDIFF(4, CAST(CURRENT_TIMESTAMP-SLLSTACTTM AS CHAR (22))) AS LOCK_AGE
		FROM KSCDLK WHERE
		SLFAC = '{$facility}' AND
		SLWRKC = '{$work_ctr}'";

		$lock_row = $this->fetchRow($sql);

		$min_left = $TIMEOUT - $lock_row['LOCK_AGE'];

		// If no lock present, return false.
		if(! $lock_row) {
			return false;

		// If lock is present and expired, remove it and return false.
		} else if($min_left < 0 ) {
			$this -> deleteWorkCtrLock($data);
			return false;

		// If lock is present and unexpired, return user lock data,
		// including number of minutes left.
		} else {
			$response = array (
					"facility" => $lock_row['SLFAC'],
					"work_ctr" => $lock_row['SLWRKC'],
					"current_user" => trim($lock_row['SLUSER']),
					"token" => $lock_row['SLSCRTOK'],
					"min_left" => $min_left
			);

			return $response;
		}
	}

	public function isLockedIn($data) {
		$lock_status = $this->readUserLock($data);

		if ($lock_status && $lock_status["current_user"] == $_SESSION["current_user"]) {
			$this->updateUserLock($data);
			return true;
		} else {
			return false;
		}
	}

	public function updateUserLock($data) {
		// Refresh timestamp on user lock.

		$sql = "UPDATE KSCDLK
					SET SLLSTACTTM = CURRENT_TIMESTAMP
					WHERE
					SLFAC = '{$data['facility']}'
					AND SLWRKC = '{$data['work_ctr']}'";

		$this->execUpdate($sql);
	}

	public function deleteWorkCtrLock($data) {

		$sql = "DELETE FROM KSCDLK WHERE
				SLFAC = '{$data['facility']}'
				AND SLWRKC = '{$data['work_ctr']}'";

		$this->execUpdate($sql);
	}

	public function deleteUserLock($user) {

		$sql = "DELETE FROM KSCDLK WHERE SLUSER = '{$user}'";

		$this->execUpdate($sql);
	}

}



// WWRKC	2	Numeric		6	0	N	Work Center Number
// WDESC	3	Char	30			N	Description
// WKPLN	80	Char	4			N	Production Line
// WFAC		81	Char	3			N	Facility
