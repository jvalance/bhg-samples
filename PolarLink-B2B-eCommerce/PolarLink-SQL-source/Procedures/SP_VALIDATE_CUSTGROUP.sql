SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_VALIDATE_CUSTGROUP ( 
	IN IN_PLC_CUST_GRP CHAR(10) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	LANGUAGE SQL 
	SPECIFIC SP_VALIDATE_CUSTGROUP 
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
Retrieve the list of customer types for drop-down lists and validations 
=================================================================================*/ 
	DECLARE WK_CUSTCOUNT INTEGER ; 
  
	SET OUT_MESSAGE = '' ; 
  
	 -- Validate the customer group code before adding record 
	SELECT COUNT ( * ) INTO WK_CUSTCOUNT FROM RCME WHERE CEHHPF = IN_PLC_CUST_GRP GROUP BY CEHHPF ; 
  
	IF WK_CUSTCOUNT IS NULL OR WK_CUSTCOUNT = 0 THEN 
		SET OUT_MESSAGE = 
			'Customer Group ' || TRIM ( IN_PLC_CUST_GRP ) 
			|| ' does not exist in the Customers Extension table (RCME).' ; 
		RETURN ; 
	END IF ; 
  
	 -- Validate customer group not already used in the table 
	SELECT COUNT ( * ) INTO WK_CUSTCOUNT FROM PLINK_CUSTOMER WHERE PLC_CUST_GRP = IN_PLC_CUST_GRP ; 
  
	IF WK_CUSTCOUNT > 0 THEN 
		SET OUT_MESSAGE = 
			'Customer Group ' || TRIM ( IN_PLC_CUST_GRP ) 
			|| ' is already in the PolarLink Customers table. Use EDIT to update data.' ; 
		RETURN ; 
	END IF ; 
  
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_VALIDATE_CUSTGROUP 
TO JVALANCE ; 
  
;
