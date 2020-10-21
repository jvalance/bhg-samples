<?php
namespace User\Service;

/**
 *
 * @author jaziel
 *        
 */
class CSV_Builder
{

    /**
	 * Downloads a CSV file to the user immediately, based on the passed prepared statement resource ($stmt).
	 * 
	 * @param $stmt Prepared statement to execute
	 * @param string $filename Default file name of downloaded file. DO NOT include '.csv' on the end - it is added automatically. 
	 * @param bool $append_time If true, adds timestamp on end of file name (before .csv) in format 'Md-Y-his'.
	 */
	public static function download_CSV($stmt, $filename, $append_time = true) 
	{
		
		$filename = CSV_Builder::formatFileName($filename, $append_time);
		ob_start(); // start output buffering
		header("Content-type: application/csv;");
		header("Content-Disposition: attachment; filename=\"$filename\"");
		// These headers are needed for IE
		header("Cache-control: private");
		header("Pragma: public");
		
		CSV_Builder::build_CSV_file($stmt, "php://output");
		ob_end_flush();
	}
	
	private static function formatFileName($filename, $append_time) {
		if ($append_time) {
			$filename = trim($filename) . '_' . date('Md-Y-his'); 
		} 
		$filename = trim($filename) . '.csv';
		return $filename;
	}
	
	private static function build_CSV_file($stmt, $filename) {
		$csv_stream = fopen($filename, 'w');
		
		db2_execute($conn, $stmt);
		
		// Write column headings 
		$headings = array();
		for ($col = 0; $col < db2_num_fields($stmt); $col++) {
			$headings[] = db2_field_name( $stmt, $col );
		}

		fputcsv($csv_stream, $headings);
		
		// Write each row of the result set in CSV format
		while ( $row = db2_fetch_array( $stmt ) ) {
			fputcsv($csv_stream, $row);
		}
		
		fclose($csv_stream);
	}
}

