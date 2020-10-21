SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_GET_FACILITIES ( 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	DYNAMIC RESULT SETS 1 
	LANGUAGE SQL 
	SPECIFIC SP_GET_FACILITIES 
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
Retrieve the list of facilities for drop-down lists and validations 
=================================================================================*/ 
  
DECLARE C1 CURSOR WITH RETURN FOR 
	SELECT 
		MFFACL AS FACILITY_CODE , 
		TRIM ( MFFACL ) || ' - ' || TRIM ( MFDESC ) AS FACILITY_DESC		 
FROM ZMF 
	; 
	 
	 --================================================================ 
	 -- Open result set cursor being returned. 
	OPEN C1 ; 
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_GET_FACILITIES 
TO JVALANCE ; 
  
;
