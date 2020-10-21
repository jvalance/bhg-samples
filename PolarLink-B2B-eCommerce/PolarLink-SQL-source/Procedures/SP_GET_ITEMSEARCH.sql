SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_GET_ITEMSEARCH ( 
	IN IN_CURR_ORDER_NUM DECIMAL(8, 0) , 
	IN IN_BRAND CHAR(15) , 
	IN IN_SIZE CHAR(15) , 
	IN IN_FILTER VARCHAR(100) , 
	OUT OUT_RESULT CHAR(1) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	DYNAMIC RESULT SETS 1 
	LANGUAGE SQL 
	SPECIFIC SP_GET_ITEMSEARCH 
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
  
Returns: 
	Result set with multiple rows 
=================================================================================*/ 
	DECLARE WK_CUST_NUM DEC ( 8 , 0 ) ; 
	DECLARE WK_SHIP2 DEC ( 4 , 0 ) ; 
	DECLARE WK_CHECK_EIX DEC ( 1 , 0 ) ; 
	DECLARE WK_EIX_CUSTNO DEC ( 8 , 0 ) ; 
	DECLARE WK_CURRDATE8 DEC ( 8 , 0 ) ; 
	DECLARE WK_REQUESTDATE DEC ( 8 , 0 ) ; 
	DECLARE WK_EIX_INCL_EXCL DEC ( 1 , 0 ) ; 
  
DECLARE CSRITEMSEARCH CURSOR WITH RETURN FOR 
  
	SELECT 
		ITM_NUMBER , 
		ITM_DESC , 
		ITM_BRAND , 
		BRAND_DESC , 
		ITM_SIZE , 
		SIZE_DESC , 
		LQORD AS ITM_QTY_ORD , 
		ITM_PRICE , 
		ITM_UOM , 
		ITM_UOM_CONVERSION , 
		CASE 
			WHEN LQORD IS NOT NULL THEN LQORD 
			ELSE 0 
		END AS ITM_CASE_QTY_ORD , 
		CASE 
			WHEN LQORD IS NOT NULL AND IUMR = '12' THEN LQORD * 12 
			WHEN LQORD IS NOT NULL AND IUMR = 'CS' THEN LQORD * 24 
			ELSE 0 
		END AS ITM_UNIT_QTY_ORD , 
		CASE 
			WHEN LQORD IS NOT NULL THEN LQORD / IVULP 
			ELSE 0 
		END AS ITM_PALLET_QTY_ORD , 
  
		ITM_PACKAGE_CODE , 
		ITM_TYPE , 
		ITM_CLASS , 
		PLS_ITEM_NO 
  
	FROM TABLE ( 
		UDTF_GET_ITEMAUTH ( WK_EIX_CUSTNO , WK_SHIP2 ) 
	) AUTH_ITEMS 
  
	JOIN IIM 
	ON IPROD = ITM_NUMBER 
  
	LEFT JOIN ECL_PLNK ON 
		ITM_NUMBER = LPROD AND 
		LORD = IN_CURR_ORDER_NUM 
  
	LEFT JOIN PLINK_SUBSTITUTES ON 
		PLS_ITEM_NO = ITM_NUMBER AND 
		PLS_ORDER_NO = IN_CURR_ORDER_NUM 
  
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
  
	 -- Get customer # for this order 
	SELECT HCUST , HSHIP , FN_CURRDATE8 ( ) , HSDTE 
	INTO WK_CUST_NUM , WK_SHIP2 , WK_CURRDATE8 , WK_REQUESTDATE 
	FROM ECH_PLNK 
	WHERE HORD = IN_CURR_ORDER_NUM ; 
  
	 -- Get item authorization settings for this customer 
	SELECT CMITMX , CMIXCU , CMINEX 
	INTO WK_CHECK_EIX , WK_EIX_CUSTNO , WK_EIX_INCL_EXCL 
	FROM RCM 
	WHERE CCUST = WK_CUST_NUM ; 
  
	 -- If no override customer # for item auth, use cust# on this order 
	IF WK_EIX_CUSTNO = 0 THEN 
		SET WK_EIX_CUSTNO = WKCUST_NUM ; 
	END IF ; 
  
	 -- If customer has CMINEX = 1 for exclude, return an empty result set by forcing customer/shipto #s to 0 
	IF WK_EIX_INCL_EXCL = 1 THEN 
		SET WK_EIX_CUSTNO = 0 ; 
		SET WK_SHIP2 = 0 ; 
	END IF ; 
  
	 -- --------------------------------------- 
	 -- Open result set cursor being returned. 
	 -- --------------------------------------- 
	OPEN CSRITEMSEARCH ; 
  
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_GET_ITEMSEARCH 
TO JVALANCE ; 
  
;
