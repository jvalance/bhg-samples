SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_GET_PLINK_USER_SEARCH ( 
	IN IN_CUST_GROUP CHAR(10) , 
	IN IN_SEARCHFILTERS VARCHAR(100) , 
	IN IN_PLU_STATUS CHAR(1) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	DYNAMIC RESULT SETS 1 
	LANGUAGE SQL 
	SPECIFIC SP_GET_PLINK_USER_SEARCH 
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
	DECLARE WK_CUSTOMER DEC ( 8 , 0 ) ; 
	DECLARE WK_COMPANY DEC ( 2 , 0 ) ; 
	DECLARE WK_CUST_GROUP CHAR ( 4 ) ; 
  
DECLARE C1 CURSOR WITH RETURN FOR 
SELECT	PLU . * , 
			PLC . * 
			 
FROM		PLINK_USER AS PLU 
  
LEFT JOIN PLINK_CUSTOMER AS PLC 
ON PLU_CUST_GROUP = PLC_CUST_GRP 
  
	WHERE ( IN_PLU_STATUS = '' OR ( IN_PLU_STATUS <> '' AND IN_PLU_STATUS = PLU_STATUS ) ) 
		AND	( IN_CUST_GROUP = '' OR ( IN_CUST_GROUP <> '' AND IN_CUST_GROUP = PLU_CUST_GROUP ) ) 
	AND ( ( IN_SEARCHFILTERS = '' ) 
			OR 
			( IN_SEARCHFILTERS <> '' AND 
				( 
					UPPER ( PLU_USER_ID ) LIKE ( '%' || IN_SEARCHFILTERS || '%' ) OR 
					UPPER ( PLU_FIRST_NAME ) LIKE ( '%' || IN_SEARCHFILTERS || '%' ) OR 
					UPPER ( PLU_LAST_NAME ) LIKE ( '%' || IN_SEARCHFILTERS || '%' ) OR 
					DIGITS ( PLU_CUSTNO ) LIKE ( '%' || IN_SEARCHFILTERS || '%' ) OR 
					UPPER ( PLU_EMAIL_ADDRESS ) LIKE ( '%' || IN_SEARCHFILTERS || '%' ) 
				) 
			) 
		) 
	; 
	 
	 --================================================================ 
SET IN_CUST_GROUP = UPPER ( IN_CUST_GROUP ) ; 
SET IN_PLU_STATUS = UPPER ( IN_PLU_STATUS ) ; 
SET IN_SEARCHFILTERS = UPPER ( IN_SEARCHFILTERS ) ; 
  
	 -- Open result set cursor being returned. 
	OPEN C1 ; 
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_GET_PLINK_USER_SEARCH 
TO JVALANCE ; 
  
;
