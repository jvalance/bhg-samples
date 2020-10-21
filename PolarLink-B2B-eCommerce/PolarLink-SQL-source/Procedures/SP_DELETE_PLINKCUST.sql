SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_DELETE_PLINKCUST ( 
	IN IN_PLC_CUST_GRP CHAR(10) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	LANGUAGE SQL 
	SPECIFIC SP_DELETE_PLINKCUST 
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
Created Sept 2015 by John Valance 
Delete an announcement in the PLINK_ANNOUNCEMENTS table. 
=================================================================================*/ 
	DECLARE WK_USER_COUNT INTEGER ; 
  
	IF NOT EXISTS ( SELECT * FROM PLINK_CUSTOMER WHERE PLC_CUST_GRP = IN_PLC_CUST_GRP ) THEN 
		SET OUT_MESSAGE = 
			'Record not deleted: Customer Group ' 
			|| TRIM ( IN_PLC_CUST_GRP ) 
			|| ' not found in the Polar Link Customers table.' ; 
		RETURN ; 
	END IF ; 
	 
	SELECT COUNT ( * ) INTO WK_USER_COUNT FROM PLINK_USER WHERE PLU_CUST_GROUP = IN_PLC_CUST_GRP ; 
	 
	IF WK_USER_COUNT > 0 THEN 
		SET OUT_MESSAGE = 
			'Customer ' || TRIM ( IN_PLC_CUST_GRP ) || 
			' not deleted: there are ' || TRIM ( CHAR ( WK_USER_COUNT ) ) || 
			' users for this customer group.' ; 
		RETURN ; 
	END IF ; 
	 
	 
	DELETE FROM PLINK_CUSTOMER 
	WHERE PLC_CUST_GRP = IN_PLC_CUST_GRP ; 
	 
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_DELETE_PLINKCUST 
TO JVALANCE ; 
  
;
