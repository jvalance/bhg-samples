<?php

class JOD_Reports {
	/**
	 * Defines the folder on report2 for storing the report templates used by JOD.
	 */
	const JOD_TEMPLATES_FOLDER = "http://report2.vermontgas.local:8080/jodreports-webapp-2.4.0/xml/";
	const TEMPORARY_PDF_FOLDER = "/www/zendsvr6/htdocs/????/temp/wo_pdfs/";
	
	/**
	 * Builds a simple, well-formed XML tag from a tag name and content value. 
	 * @param string $tagName The XML tag name
	 * @param string $value The value to be used as content for the XML tag
	 */
	function CreateXMLTag($tagName, $value) {
		$tagName = trim($tagName);
		$value = htmlentities($value); // escape any special chars to avoid xml validation errors
		
		if (trim($value) != '') {
			$xmlTag = "<$tagName>$value</$tagName>\n";
		} else {
			$xmlTag = "<$tagName />\n";
		}
		
		return $xmlTag;
	} 
	
	/**
	 * Creates and runs a CURL POST request to the JOD server, using the 
	 * URL passed in $request, and passing the XML document passed as $xml
	 * as a POST argument.  Response should be a PDF file.  
	 * @param string $xml The XML document which defines the variable content for the report.
	 * @param string $request The JOD-complient URL for requesting the appropriate report template. 
	 */
	public function retrievePDF($xml, $request) {
		
		// urlencode and concatenate the POST arguments 
		$postargs = 'outputFormat=pdf&model=' . urlencode($xml);
		
		$session = curl_init ( $request );
		// Tell curl to use HTTP POST
		curl_setopt ( $session, CURLOPT_POST, true );
		// Tell curl that this is the body of the POST
		curl_setopt ( $session, CURLOPT_POSTFIELDS, $postargs );
		// Tell curl to return the headers with the response
		curl_setopt ( $session, CURLOPT_HEADER, false );
		// Tell curl to return the transfer as a string of the return value of curl_exec()
		curl_setopt ( $session, CURLOPT_RETURNTRANSFER, true );
		// Tell curl to return the raw output with CURLOPT_RETURNTRANSFER
		curl_setopt ( $session, CURLOPT_BINARYTRANSFER, true );
		
		// Do the POST and close the session
		$response = curl_exec ( $session );
		curl_close ( $session );
				
		return $response;
	}
	

	/**
	 * Sends the binary PDF file content passed as $pdf to the browser with the
	 * proper HTTP headers, including the file name supplied as $filename. 
	 * @param binary $pdf
	 * @param string $filename
	 */
	public function SendPDF2Browser($pdf, $filename) {
		header ( 'Content-Type: application/pdf' );
		header ( "Content-Disposition: inline; filename=$filename;" );
		header("Cache-control: private");
		header("Pragma: public");
		ob_clean ();
		flush ();
		echo $pdf;
		ob_end_flush ();
	}

	/**
	 * Returns the temporary folder on the iSeries for storing PDFs returned by JOD server
	 * prior to merging during batch printing. This folder should be cleared out periodically.
	 */
	public function getTemporaryMergeFolder() {
		$tempFolder = '';
		$baseFlr = VGS_DB_Conn_Singleton::getEnvBaseFolder();
		$tempFolder = str_replace('????', $baseFlr, self::TEMPORARY_PDF_FOLDER);
		return $tempFolder;
	}

	/**
	 * Returns the folder on the JOD server (report2) where the Open Office doc templates are stored.
	 * For dev and test templates, we will add the filename prefix 'dev_' or 'test_' to the folder name.
	 */
	public function getJODTemplateFolder() {
		// DEV and TEST versions of JOD template files are stored in same folder as PROD, but 
		// prefixed by 'test_' or 'dev_'. This was needed as a work around due to the way 
		// JOD reports is programmed - i.e. templates could only be stored in one folder on JOD server.
		
		switch (VGS_DB_Conn_Singleton::getEnvironment()) {
			case VGS_DB_Conn_Singleton::DB_DEV:
				// Dev template file names start with 'dev_'
				$templateFolder = self::JOD_TEMPLATES_FOLDER."dev_";
				break;
			case VGS_DB_Conn_Singleton::DB_TEST:
				// Test template file names start with 'test_'
				$templateFolder = self::JOD_TEMPLATES_FOLDER."test_";
				break;
			case VGS_DB_Conn_Singleton::DB_PROD:
				// No prefix on production template file names
				$templateFolder = self::JOD_TEMPLATES_FOLDER;
				break;
		}
		
		return $templateFolder;
	}
}

?>