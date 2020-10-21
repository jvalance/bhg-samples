SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_GET_PLINK_CUSTOMER_DETAIL ( 
	IN IN_PLC_CUST_GRP CHAR(10) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	DYNAMIC RESULT SETS 1 
	LANGUAGE SQL 
	SPECIFIC SP_GET_PLINK_CUSTOMER_DETAIL 
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
Retrieve PLink_Customer for maintenance. 
=================================================================================*/ 
  
DECLARE C1 CURSOR WITH RETURN FOR 
SELECT * 
FROM PLINK_CUSTOMER 
	WHERE PLC_CUST_GRP = IN_PLC_CUST_GRP 
	; 
	 
	 --================================================================ 
	SET IN_PLC_CUST_GRP = UPPER ( TRIM ( IN_PLC_CUST_GRP ) ) ; 
	 
	 -- Open result set cursor being returned. 
	OPEN C1 ; 
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_GET_PLINK_CUSTOMER_DETAIL 
TO JVALANCE ; 
  
;
