<?php
require_once '../common/vgs_utilities.php';
require_once '../model/VGS_DB_Select.php';
require_once '../model/VGS_DB_Table.php';

class VGS_CSV_Builder {

	private $table;
	
	public function __construct($conn) {
		$this->table = new VGS_DB_Table($conn);
	}
	
	/**
	 * Writes a CSV file to the IFS for the passed DB_Select object.
	 * 
	 * @param VGS_DB_Select $select Select object specifying SQL select to use for building the CSV file
	 * @param string $filename Full path of the IFS file to create. DO NOT include '.csv' on the end - it is added automatically. 
	 * @param bool $append_time If true, adds timestamp on end of file name (before .csv) in format 'Md-Y-his'.
	 */
	public function write_CSV_file(VGS_DB_Select $select, $filename, $append_time = true) 
	{
		$filename = $this->formatFileName($filename, $append_time);
		$this->build_CSV_file($select, $filename);		
	}

	/**
	 * Downloads a CSV file to the user immediately, based on the passed DB_Select object.
	 * 
	 * @param VGS_DB_Select $select Select object specifying SQL select to use for building the CSV file
	 * @param string $filename Default file name of downloaded file. DO NOT include '.csv' on the end - it is added automatically. 
	 * @param bool $append_time If true, adds timestamp on end of file name (before .csv) in format 'Md-Y-his'.
	 */
	public function download_CSV(VGS_DB_Select $select, $filename, $append_time = true) 
	{
		// Allow up to five minutes for large downloads
		$old_max_execution_time = ini_get('max_execution_time'); // save current value to restore after
		ini_set('max_execution_time', 300);
		
		$filename = $this->formatFileName($filename, $append_time);
		ob_start(); // start output buffering
		header("Content-type: application/csv;");
		header("Content-Disposition: attachment; filename=\"$filename\"");
		// These headers are needed for IE
		header("Cache-control: private");
		header("Pragma: public");
		
		$this->build_CSV_file($select, "php://output");
		ob_end_flush();

		// Restore previous max_execution_time
		ini_set('max_execution_time', $old_max_execution_time);
	}
	
	private function formatFileName($filename, $append_time) {
		if ($append_time) {
			$filename = trim($filename) . '_' . date('Md-Y-his'); 
		} 
		$filename = trim($filename) . '.csv';
		return $filename;
	}
	
	private function build_CSV_file(VGS_DB_Select $select, $filename) {
		$csv_stream = fopen($filename, 'w');
		// TODO: DUMMYZEND71 - Remove temporary code (false parm on toString)
		$this->table->execListQuery($select->toString(false), $select->parms);
		
		// Write column headings 
		$headings = array();
		for ($col = 0; $col < db2_num_fields($this->table->stmt); $col++) {
			$headings[] = db2_field_name( $this->table->stmt, $col );
		}

		fputcsv($csv_stream, $headings);
		
		// Write each row of the result set in CSV format
		while ( $row = db2_fetch_array( $this->table->stmt ) ) {
			fputcsv($csv_stream, $row);
		}
		
		fclose($csv_stream);
	}
	
}