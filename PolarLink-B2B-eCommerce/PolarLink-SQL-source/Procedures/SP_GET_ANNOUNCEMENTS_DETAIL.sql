SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_GET_ANNOUNCEMENTS_DETAIL ( 
	IN IN_PLA_ID INTEGER , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	DYNAMIC RESULT SETS 1 
	LANGUAGE SQL 
	SPECIFIC SP_GET_ANNOUNCEMENTS_DETAIL 
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
Created July 2015 by John Valance 
Retrieve announcements for maintenance. 
=================================================================================*/ 
  
DECLARE C1 CURSOR WITH RETURN FOR 
SELECT * 
FROM PLINK_ANNOUNCEMENTS 
	WHERE PLA_ID = IN_PLA_ID 
	; 
	 
	 --================================================================ 
	 -- Open result set cursor being returned. 
	OPEN C1 ; 
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_GET_ANNOUNCEMENTS_DETAIL 
TO JVALANCE ; 
  
;
