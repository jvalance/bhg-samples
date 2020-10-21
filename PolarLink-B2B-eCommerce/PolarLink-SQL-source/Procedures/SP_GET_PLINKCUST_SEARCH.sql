SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_GET_PLINKCUST_SEARCH ( 
	IN IN_SEARCHFILTERS VARCHAR(100) , 
	IN IN_PLC_STATUS CHAR(1) , 
	IN IN_CUST_TYPE CHAR(4) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	DYNAMIC RESULT SETS 1 
	LANGUAGE SQL 
	SPECIFIC SP_GET_PLINKCUST_SEARCH 
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
Retrieve PolarLink customers list for CSR customer maintenance. 
=================================================================================*/ 
	DECLARE WK_CUSTOMER DEC ( 8 , 0 ) ; 
	DECLARE WK_COMPANY DEC ( 2 , 0 ) ; 
	DECLARE WK_CUST_TYPE CHAR ( 4 ) ; 
  
DECLARE C1 CURSOR WITH RETURN FOR 
SELECT	PLC . * , 
			( SELECT COUNT ( * ) 
				FROM PLINK_USER 
				WHERE PLU_CUST_GROUP = PLC_CUST_GRP ) 
			AS USERS_COUNT 
  
FROM		PLINK_CUSTOMER AS PLC 
  
	WHERE ( IN_PLC_STATUS = '' OR ( IN_PLC_STATUS <> '' AND IN_PLC_STATUS = PLC_STATUS ) ) 
		AND	( IN_CUST_TYPE = '' OR ( IN_CUST_TYPE <> '' AND IN_CUST_TYPE = PLC_CUST_TYPE ) ) 
	AND ( ( IN_SEARCHFILTERS = '' ) 
			OR 
			( IN_SEARCHFILTERS <> '' AND 
				( 
					UPPER ( PLC_CUST_GRP ) LIKE ( '%' || IN_SEARCHFILTERS || '%' ) OR 
					UPPER ( PLC_CUST_NAME ) LIKE ( '%' || IN_SEARCHFILTERS || '%' ) OR 
					DIGITS ( PLC_CUSTNO ) LIKE ( '%' || IN_SEARCHFILTERS || '%' ) OR 
					UPPER ( PLC_EMAILS ) LIKE ( '%' || IN_SEARCHFILTERS || '%' ) 
				) 
			) 
		) 
	ORDER BY PLC_CUST_NAME 
	; 
  
	 --================================================================ 
SET IN_CUST_TYPE = UPPER ( IN_CUST_TYPE ) ; 
SET IN_PLC_STATUS = UPPER ( IN_PLC_STATUS ) ; 
SET IN_SEARCHFILTERS = UPPER ( IN_SEARCHFILTERS ) ; 
  
	 -- Open result set cursor being returned. 
	OPEN C1 ; 
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_GET_PLINKCUST_SEARCH 
TO JVALANCE ; 
  
;
