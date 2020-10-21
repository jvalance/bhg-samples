SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_GET_ITEM_INQUIRY ( 
	IN IN_CUST_NUM DECIMAL(8, 0) , 
	IN IN_SHIPTO_NUM DECIMAL(4, 0) , 
	IN IN_BRAND CHAR(15) , 
	IN IN_SIZE CHAR(15) , 
	IN IN_FILTER VARCHAR(100) , 
	OUT OUT_RESULT CHAR(1) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	DYNAMIC RESULT SETS 1 
	LANGUAGE SQL 
	SPECIFIC SP_GET_ITEM_INQUIRY 
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
Created April 2017 by John Valance  
   
Returns:  
	Result set with multiple rows  
=================================================================================*/ 
	DECLARE WK_CHECK_EIX DEC ( 1 , 0 ) ; 
	DECLARE WK_EIX_CUSTNO DEC ( 8 , 0 ) ; 
	DECLARE WK_EIX_INCL_EXCL DEC ( 1 , 0 ) ; 
  
DECLARE CSRITEMSEARCH CURSOR WITH RETURN FOR 
  
	SELECT 
		ITM_NUMBER , 
		ITM_DESC , 
		ITM_BRAND , 
		BRAND_DESC , 
		ITM_SIZE , 
		SIZE_DESC , 
		ITM_PRICE , 
		ITM_UOM , 
		ITM_UOM_CONVERSION , 
		ITM_PACKAGE_CODE , 
		ITM_TYPE , 
		ITM_CLASS , 
		IIM . IVULP AS UNITS_PER_PALLET 
  
	FROM TABLE ( 
		UDTF_GET_ITEMAUTH ( WK_EIX_CUSTNO , IN_SHIPTO_NUM ) 
	) AUTH_ITEMS 
  
	JOIN IIM 
	ON IPROD = ITM_NUMBER 
  
	WHERE 
		( IN_BRAND = '' OR ( IN_BRAND <> '' AND ITM_BRAND = IN_BRAND ) ) 
	AND ( IN_SIZE = '' OR ( IN_SIZE <> '' AND ITM_SIZE = IN_SIZE ) ) 
	AND ( IN_FILTER = '' OR 
		( IN_FILTER <> '' AND 
			( 
				( ITM_DESC LIKE ( '%' || IN_FILTER || '%' ) ) OR 
				( ITM_NUMBER LIKE ( '%' || IN_FILTER || '%' ) ) 
			) 
		) ) 
  
	ORDER BY ITM_DESC 
	; 
  
--===================================================================== 
	 -- Initialize work variables 
	SET OUT_MESSAGE = '' ; 
	SET OUT_RESULT = '1' ; 
	SET IN_BRAND = TRIM ( IN_BRAND ) ; 
	SET IN_SIZE = TRIM ( IN_SIZE ) ; 
	SET IN_FILTER = TRIM ( IN_FILTER ) ; 
  
  
	 -- Get item authorization settings for this customer 
	SELECT CMITMX , CMIXCU , CMINEX 
	INTO WK_CHECK_EIX , WK_EIX_CUSTNO , WK_EIX_INCL_EXCL 
	FROM RCM 
	WHERE CCUST = IN_CUST_NUM ; 
  
	 -- If no override customer # for item auth, use cust# on this order 
	IF WK_EIX_CUSTNO = 0 THEN 
		SET WK_EIX_CUSTNO = IN_CUST_NUM ; 
	END IF ; 
  
	 -- If customer has CMINEX = 1 for exclude, return an empty result set by forcing customer/shipto #s to 0 
	IF WK_EIX_INCL_EXCL = 1 THEN 
		SET WK_EIX_CUSTNO = 0 ; 
		SET IN_SHIPTO_NUM = 0 ; 
	END IF ; 
  
	 -- --------------------------------------- 
	 -- Open result set cursor being returned. 
	 -- --------------------------------------- 
	OPEN CSRITEMSEARCH ; 
  
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_GET_ITEM_INQUIRY 
TO JVALANCE ; 
  
;
