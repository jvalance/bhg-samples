<?php
require_once '../common/DB_Conn.php';
require_once '../common/DB_Table.php';
require_once '../common/DB_Select.php';

class VP117X extends polarbev\DB_Table {

	function __construct() {
		parent::__construct ();
	}

	public function addVP117X($data, Zend\Log\Logger $logger) {

		$whsSubSelect =
			"(select distinct fwhse from kfp

				left join FRT
					on KFP.FPROD = FRT.RPROD
					and KFP.FPFAC = FRT.RTWHS
					and FRT.ROPNO = 10
					and KFP.FPRTEM = FRT.RTRTEM

				where ftype = 'F'
					and fprod = ?
					and frdte = ?
					and fpfac = ?
					and FRT.RWRKC = ?
					and FRT.RMAC <> 0
					and FRT.RMAC is not null
					and trim(fwhse) <> ''
			)";

		// Log some details about this SQL subselect and the data values
		$logger->info($whsSubSelect);
		$dataString = implode(',', $data);
		$logger->info("data: $dataString");

		$sql  = "INSERT INTO VP117X
				(PART, RDATE, FACIL, SEQ, WHS)
				VALUES(?, ?, ?, ?, ifnull($whsSubSelect, '') )";

		$values = array (
			$data["itemno"],
			$data["date"],
			$data["facility"],
			$data["seqno"],
			// Sub-select parameters:
			$data["itemno"],
			$data["date"],
			$data["facility"],
			$data["work_ctr"],
		);

		$this->execUpdate($sql,$values);
	}

	function get_next_seqno() {
		$sql = "SELECT ifnull((max(seq)+1),1) AS NEXTSEQ FROM VP117X";
		$row = $this->fetchRow($sql);
		if (is_array($row))
			return $row['NEXTSEQ'];
		else
			return '';

	}
}