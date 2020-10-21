SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_GET_CUSTOMER_TYPES ( 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	DYNAMIC RESULT SETS 1 
	LANGUAGE SQL 
	SPECIFIC SP_GET_CUSTOMER_TYPES 
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
Created November 2015 by John Valance 
Retrieve the list of customer types for drop-down lists and validations 
=================================================================================*/ 
  
DECLARE C1 CURSOR WITH RETURN FOR 
	SELECT 
		CTCSTP AS CUST_TYPE_CODE , 
		TRIM ( CTCSTP ) || ' - ' || TRIM ( CTDESC ) AS CUST_TYPE_DESC 
FROM RCT 
	; 
	 
	 --================================================================ 
	 -- Open result set cursor being returned. 
	OPEN C1 ; 
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_GET_CUSTOMER_TYPES 
TO JVALANCE ; 
  
;
