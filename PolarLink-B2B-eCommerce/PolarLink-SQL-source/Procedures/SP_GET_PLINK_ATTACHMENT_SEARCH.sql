SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_GET_PLINK_ATTACHMENT_SEARCH ( 
	IN IN_PLAT_ORDER_NO DECIMAL(8, 0) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	DYNAMIC RESULT SETS 1 
	LANGUAGE SQL 
	SPECIFIC SP_GET_PLINK_ATTACHMENT_SEARCH 
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
Created June 2016 by John Valance 
Retrieve attachments for an order. 
=================================================================================*/ 
  
DECLARE C1 CURSOR WITH RETURN FOR 
		SELECT	* 
		FROM		PLINK_ATTACHMENT 
		WHERE PLAT_ORDER_NO = IN_PLAT_ORDER_NO 
	; 
  
	 --================================================================ 
	 -- Open result set cursor being returned. 
	OPEN C1 ; 
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_GET_PLINK_ATTACHMENT_SEARCH 
TO JVALANCE ; 
  
;
