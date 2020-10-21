SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_GET_WORKINGDAYS ( 
	IN IN_FROMDATE DECIMAL(8, 0) , 
	IN IN_TODATE DECIMAL(8, 0) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	DYNAMIC RESULT SETS 1 
	LANGUAGE SQL 
	SPECIFIC SP_GET_WORKINGDAYS 
	NOT DETERMINISTIC 
	MODIFIES SQL DATA 
	CALLED ON NULL INPUT 
	SET OPTION  ALWBLK = *ALLREAD , 
	ALWCPYDTA = *OPTIMIZE , 
	COMMIT = *NONE , 
	DECRESULT = (31, 31, 00) , 
	DFTRDBCOL = *NONE , 
	DYNDFTCOL = *NO , 
	DYNUSRPRF = *USER , 
	SRTSEQ = *HEX   
	BEGIN 
/*================================================================================ 
Created August 2016 by John Valance 
  
Retrieve information from the ECAL file to use in determining whether delivery date 
entered on the order header page is a valid working date. 
  
Returns: 
	Result set with records from ECAL table for the date range specified by 
	the input parameters: in_FromDate and in_ToDate 
  
=================================================================================*/ 
	DECLARE CSR1 CURSOR WITH RETURN FOR 
SELECT 
		ECDATE	AS	DATE , 
		ECDOW	AS DAY_OF_WEEK , 
		ECWRK	AS WORKING_DAY 
	FROM ECAL 
	WHERE ECDATE BETWEEN IN_FROMDATE AND IN_TODATE 
ORDER BY ECDATE ; 
  
	 -- Initialize work variables 
	SET OUT_MESSAGE = '' ; 
  
OPEN CSR1 ; 
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_GET_WORKINGDAYS 
TO JVALANCE ; 
  
;
