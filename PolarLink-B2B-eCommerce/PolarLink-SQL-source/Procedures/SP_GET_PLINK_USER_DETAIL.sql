SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_GET_PLINK_USER_DETAIL ( 
	IN IN_PLU_USER_ID CHAR(15) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	DYNAMIC RESULT SETS 1 
	LANGUAGE SQL 
	SPECIFIC SP_GET_PLINK_USER_DETAIL 
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
Retrieve PLink_User for maintenance. 
=================================================================================*/ 
  
DECLARE C1 CURSOR WITH RETURN FOR 
SELECT * 
FROM PLINK_USER 
	WHERE PLU_USER_ID = IN_PLU_USER_ID 
	; 
	 
	 --================================================================ 
	SET IN_PLU_USER_ID = UPPER ( TRIM ( IN_PLU_USER_ID ) ) ; 
	 
	 -- Open result set cursor being returned. 
	OPEN C1 ; 
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_GET_PLINK_USER_DETAIL 
TO JVALANCE ; 
  
;
