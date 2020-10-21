SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_GET_PLINK_CUSTOMERS ( 
	IN IN_SEARCHFILTERS VARCHAR(100) ) 
	DYNAMIC RESULT SETS 1 
	LANGUAGE SQL 
	SPECIFIC SP_GET_PLINK_CUSTOMERS 
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
Created September 2015 by John Valance 
  
Retrieve polar link customers for the CSR customer selection for order entry. 
=================================================================================*/ 
  
DECLARE C1 CURSOR WITH RETURN FOR 
SELECT 
		PLC_CUST_GRP , 
		PLC_CUST_NAME , 
		CASE 
			WHEN ( PLC_STATUS = 'E' ) THEN 'Enabled' 
			WHEN ( PLC_STATUS = 'D' ) THEN 'Disabled' 
			ELSE '' 
		END AS PLC_STATUS_TEXT , 
		( SELECT COUNT ( * ) FROM PLINK_USER WHERE PLU_CUST_GROUP = PLC_CUST_GRP ) AS PLC_USER_COUNT , 
		DATE ( PLC_CRT_TIME ) AS PLC_DATE_CREATED , 
		DATE ( PLC_LAST_USE ) AS PLC_LAST_LOGIN 
  
FROM	PLINK_CUSTOMER 
  
	WHERE 
		PLC_STATUS <> 'D' AND 
		( 
			( IN_SEARCHFILTERS = '' ) 
			OR 
			( IN_SEARCHFILTERS <> '' AND 
				( 
					UPPER ( PLC_CUST_GRP ) LIKE ( '%' || IN_SEARCHFILTERS || '%' ) OR 
					UPPER ( PLC_CUST_NAME ) LIKE ( '%' || IN_SEARCHFILTERS || '%' ) 
				) 
			) 
		) 
	ORDER BY PLC_CUST_NAME 
	; 
  
	 --================================================================ 
SET IN_SEARCHFILTERS = TRIM ( UPPER ( IN_SEARCHFILTERS ) ) ; 
  
	 -- Open result set cursor being returned. 
	OPEN C1 ; 
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_GET_PLINK_CUSTOMERS 
TO JVALANCE ; 
  
;
