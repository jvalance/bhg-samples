SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_SAVE_PLINK_USER ( 
	IN IN_PLU_USER_ID CHAR(15) , 
	IN IN_PLU_CUST_GROUP CHAR(10) , 
	IN IN_PLU_FIRST_NAME VARCHAR(30) , 
	IN IN_PLU_LAST_NAME VARCHAR(40) , 
	IN IN_PLU_PASSWORD VARCHAR(60) , 
	IN IN_PLU_CUSTNO DECIMAL(8, 0) , 
	IN IN_PLU_DFT_UOM CHAR(2) , 
	IN IN_PLU_DFT_SHIPTO DECIMAL(4, 0) , 
	IN IN_PLU_DFT_SHIP_METHOD CHAR(1) , 
	IN IN_PLU_POLAR_CSR CHAR(1) , 
	IN IN_PLU_PLINK_ADMIN CHAR(1) , 
	IN IN_PLU_STATUS CHAR(1) , 
	IN IN_PLU_EMAIL_ADDRESS VARCHAR(100) , 
	IN IN_PLU_CRT_USER CHAR(10) , 
	OUT OUT_ID DECIMAL(10, 0) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	LANGUAGE SQL 
	SPECIFIC SP_SAVE_PLINK_USER 
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
Insert a record into the PLINK_USER table. 
=================================================================================*/ 
  
	SET IN_PLU_USER_ID = UPPER ( TRIM ( IN_PLU_USER_ID ) ) ; 
  
	IF EXISTS ( SELECT * FROM PLINK_USER WHERE PLU_USER_ID = IN_PLU_USER_ID ) THEN 
		SET OUT_MESSAGE = 
			'Record not added: User ID ' 
			|| TRIM ( IN_PLU_USER_ID ) 
			|| ' already exists in the PolarLink Users table (PLINK_USER).' ; 
		RETURN ; 
	END IF ; 
  
	 -- customer group is required if user type is not CSR 
	IF TRIM ( IN_PLU_CUST_GROUP ) = '' AND IN_PLU_POLAR_CSR <> 'Y' THEN 
		SET OUT_MESSAGE = 'Record not added: Customer Group is required if user type is not CSR' ; 
		RETURN ; 
	END IF ; 
  
	IF TRIM ( IN_PLU_CUST_GROUP ) <> ''  -- customer group is not required for CSR users, but if passed must be valid 
	AND NOT EXISTS ( SELECT * FROM PLINK_CUSTOMER WHERE PLC_CUST_GRP = IN_PLU_CUST_GROUP ) THEN 
		SET OUT_MESSAGE = 
			'Record not added: Customer Group ' 
			|| TRIM ( IN_PLU_CUST_GROUP ) 
			|| ' does not exist in the PolarLink Customers table (PLINK_CUSTOMER).' ; 
		RETURN ; 
	END IF ; 
  
	INSERT INTO PLINK_USER ( 
		PLU_USER_ID , 
		PLU_CUST_GROUP , 
		PLU_FIRST_NAME , 
		PLU_LAST_NAME , 
		PLU_PASSWORD , 
		PLU_CUSTNO , 
		PLU_DFT_UOM , 
		PLU_DFT_SHIPTO , 
		PLU_DFT_SHIP_METHOD , 
		PLU_POLAR_CSR , 
		PLU_PLINK_ADMIN , 
		PLU_STATUS , 
		PLU_EMAIL_ADDRESS , 
		PLU_CRT_TIME , 
		PLU_CRT_USER 
	) VALUES ( 
		IN_PLU_USER_ID , 
		IN_PLU_CUST_GROUP , 
		IN_PLU_FIRST_NAME , 
		IN_PLU_LAST_NAME , 
		IN_PLU_PASSWORD , 
		IN_PLU_CUSTNO , 
		IN_PLU_DFT_UOM , 
		IN_PLU_DFT_SHIPTO , 
		IN_PLU_DFT_SHIP_METHOD , 
		IN_PLU_POLAR_CSR , 
		IN_PLU_PLINK_ADMIN , 
		IN_PLU_STATUS , 
		IN_PLU_EMAIL_ADDRESS , 
		CURRENT TIMESTAMP , 
		IN_PLU_CRT_USER 
	) ; 
  
	SET OUT_ID = IDENTITY_VAL_LOCAL ( ) ; 
  
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_SAVE_PLINK_USER 
TO JVALANCE ; 
  
;
