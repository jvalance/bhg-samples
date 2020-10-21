SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_SAVE_PLINKCUST ( 
	IN IN_USER_ID CHAR(15) , 
	IN IN_PLC_CUST_GRP CHAR(10) , 
	IN IN_PLC_CUST_NAME VARCHAR(50) , 
	IN IN_PLC_EMAILS VARCHAR(1500) , 
	IN IN_PLC_STATUS CHAR(1) , 
	IN IN_PLC_DFT_UOM CHAR(2) , 
	IN IN_PLC_DFT_SHIP_METHOD CHAR(1) , 
	IN IN_PLC_CUSTNO DECIMAL(8, 0) , 
	IN IN_PLC_DFT_SHIPTO DECIMAL(4, 0) , 
	OUT OUT_ID DECIMAL(10, 0) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	LANGUAGE SQL 
	SPECIFIC SP_SAVE_PLINKCUST 
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
Procedure: sp_Save_PlinkCust 
Insert or update a record in the PLINK_CUSTOMER table. 
  
Created Sept 2015 by John Valance 
Update Oct 2016 - now using one stored proc to do both insert and update, and includes 
all fields from both details and defaults tabs (which are being merged into one tab). 
=================================================================================*/ 
	DECLARE WK_CUSTCOUNT INTEGER ; 
  
	SET IN_PLC_CUST_GRP = UPPER ( TRIM ( IN_PLC_CUST_GRP ) ) ; 
  
	IF EXISTS ( SELECT * FROM PLINK_CUSTOMER WHERE PLC_CUST_GRP = IN_PLC_CUST_GRP ) THEN 
		 -- PolarLink customer already exists - perform UPDATE 
		UPDATE PLINK_CUSTOMER SET 
			PLC_CUST_NAME = IN_PLC_CUST_NAME , 
			PLC_EMAILS = IN_PLC_EMAILS , 
			PLC_STATUS = IN_PLC_STATUS , 
			PLC_DFT_UOM = IN_PLC_DFT_UOM , 
			PLC_DFT_SHIP_METHOD = IN_PLC_DFT_SHIP_METHOD , 
			PLC_CUSTNO = IN_PLC_CUSTNO , 
			PLC_DFT_SHIPTO = IN_PLC_DFT_SHIPTO , 
			PLC_CHG_TIME = CURRENT TIMESTAMP , 
			PLC_CHG_USER = IN_USER_ID 
		WHERE PLC_CUST_GRP = IN_PLC_CUST_GRP ; 
  
	ELSE 
		 -- Record not found for the customer group - perform INSERT 
		 -- Validate the customer group code before adding record 
		SELECT COUNT ( * ) INTO WK_CUSTCOUNT FROM RCME WHERE CEHHPF = IN_PLC_CUST_GRP GROUP BY CEHHPF ; 
  
		IF WK_CUSTCOUNT IS NULL OR WK_CUSTCOUNT = 0 THEN 
			SET OUT_MESSAGE = 
				'Record not added: Customer Group ' 
				|| TRIM ( IN_PLC_CUST_GRP ) 
				|| ' does not exist in the Customers Extension table (RCME).' ; 
			RETURN ; 
		END IF ; 
  
		INSERT INTO PLINK_CUSTOMER ( 
			PLC_CUST_GRP , 
			PLC_CUST_NAME , 
			PLC_EMAILS , 
			PLC_STATUS , 
			PLC_DFT_UOM , 
			PLC_DFT_SHIP_METHOD , 
			PLC_CUSTNO , 
			PLC_DFT_SHIPTO , 
			PLC_CRT_TIME , 
			PLC_CRT_USER 
		) VALUES ( 
			IN_PLC_CUST_GRP , 
			IN_PLC_CUST_NAME , 
			IN_PLC_EMAILS , 
			IN_PLC_STATUS , 
			IN_PLC_DFT_UOM , 
			IN_PLC_DFT_SHIP_METHOD , 
			IN_PLC_CUSTNO , 
			IN_PLC_DFT_SHIPTO , 
			CURRENT TIMESTAMP , 
			IN_USER_ID 
		) ; 
		 -- Return unique key (even though we don't use it anywhere) 
		SET OUT_ID = IDENTITY_VAL_LOCAL ( ) ; 
  
	END IF ; 
  
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_SAVE_PLINKCUST 
TO JVALANCE ; 
  
;
