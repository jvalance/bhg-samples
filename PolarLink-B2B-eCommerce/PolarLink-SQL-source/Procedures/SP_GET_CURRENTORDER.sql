SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_GET_CURRENTORDER ( 
	IN IN_CUSTNUM DECIMAL(8, 0) , 
	OUT OUT_ORDERNUM DECIMAL(8, 0) , 
	OUT OUT_ORDERINGSTEP CHAR(1) , 
	OUT OUT_RESULT CHAR(1) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	LANGUAGE SQL 
	SPECIFIC SP_GET_CURRENTORDER 
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
Created August 2015 by John Valance 
  
Retrieve information for the Order Header page. 
  
Returns:  
	Result set with one row  
  
Paramters: 
	in_OrderNum: Current order number for this session. 
	OUT out_result char(1) 
	OUT out_message varchar(100) 
=================================================================================*/ 
	 -- Initialize work variables 
	SET OUT_MESSAGE = '' ; 
	SET OUT_RESULT = '1' ; 
  
	SELECT HORD , PLINK_ENTRY_STEP 
	INTO OUT_ORDERNUM , OUT_ORDERINGSTEP 
	FROM ECH_PLNK 
	WHERE HCUST = IN_CUSTNUM AND PLINK_STATUS = 'CURR' ; 
	 
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_GET_CURRENTORDER 
TO JVALANCE ; 
  
;
