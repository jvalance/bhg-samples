SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_GET_USERSBYCUSTGRP ( 
	IN IN_CUSTGROUP CHAR(10) , 
	IN IN_CURRUSERID CHAR(10) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	DYNAMIC RESULT SETS 1 
	LANGUAGE SQL 
	SPECIFIC SP_GET_USERSBYCUSTGRP 
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
Created March 2017 by John Valance 
  
Retrieve users for the given customer group, for selecting order history. 
This will automatically include the current logged-in user, since that may be a 
CSR which is not part of the customer group. 
  
=================================================================================*/ 
  
DECLARE C1 CURSOR WITH RETURN FOR 
		SELECT * FROM PLINK_USER WHERE PLU_CUST_GROUP = IN_CUSTGROUP 
		UNION 
		SELECT * FROM PLINK_USER WHERE PLU_USER_ID = IN_CURRUSERID 
		ORDER BY PLU_USER_ID 
	; 
  
--================================================================ 
	 -- Initialize work variables 
	SET OUT_MESSAGE = '' ; 
  
	 -- Open result set cursor being returned. 
	OPEN C1 ; 
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_GET_USERSBYCUSTGRP 
TO JVALANCE ; 
  
;
