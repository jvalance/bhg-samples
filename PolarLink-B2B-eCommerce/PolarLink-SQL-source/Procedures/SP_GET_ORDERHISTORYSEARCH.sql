SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_GET_ORDERHISTORYSEARCH ( 
	IN IN_CURRUSERID CHAR(10) , 
	IN IN_CUSTGROUP CHAR(10) , 
	IN IN_FILTERUSERID CHAR(10) , 
	IN IN_FROMDATE DECIMAL(8, 0) , 
	IN IN_TODATE DECIMAL(8, 0) , 
	OUT OUT_RESULT CHAR(1) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	DYNAMIC RESULT SETS 1 
	LANGUAGE SQL 
	SPECIFIC SP_GET_ORDERHISTORYSEARCH 
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
  
Retrieve result set for the Order History search page. 
  
Returns: 
	Result set with multiple rows, of order headers for the specified customer group 
	and optional from/to entry date range. 
=================================================================================*/ 
DECLARE WK_CURRENTUSERISCSR CHAR ( 1 ) ; 
  
DECLARE C1 CURSOR WITH RETURN FOR 
SELECT 
		HORD			AS OH_PLINK_ORDERNO , 
		LX_ORDER_NUM , 
		HEDTE			AS OH_DATE_SUBMITTED , 
		CHENTM			AS OH_TIME_SUBMITTED , 
		PLINK_STATUS , 
		HCUST			AS OH_CUSTNO , 
		HSHIP			AS OH_SHP2_NUM , 
		HCTYP			AS OH_CUST_TYPE , 
		HVIA			AS OH_SHIP_METHOD_CODE , 
		CASE 
			WHEN HVIA = 'P' THEN 'Pickup' 
			WHEN HVIA = 'D' THEN 'Delivery (no backhaul)' 
			WHEN HVIA = 'B' THEN 'Delivery with Backhaul' 
			ELSE '' 
		END	AS OH_SHIP_METHOD_TEXT , 
  
		 -- CESUBS = 'Y' means always require substitutes 
		 -- CESUBS = ' ' means check ship-to field TESUBS 
		CASE WHEN ( CESUBS IS NULL ) OR ( CESUBS = ' ' ) 
			THEN IFNULL ( TESUBS , ' ' ) 
			ELSE IFNULL ( CESUBS , ' ' ) 
		END	AS SUBS_REQUIRED ,  -- require substitutes flag 
		HATN			AS OH_SHP2_ATTN , 
		HNAME			AS OH_SHP2_NAME , 
		HAD1			AS OH_SHP2_ADDR1 , 
		HAD2			AS OH_SHP2_ADDR2 , 
		HAD3			AS OH_SHP2_ADDR3 , 
		HPOST			AS OH_SHP2_ZIP , 
		HCOUN			AS OH_SHP2_COUNTRY , 
		HSTE			AS OH_SHP2_STATE , 
		CHENUS			AS OH_ENTRY_USER , 
		PLINK_STATUS	AS OH_PLINK_STATUS , 
		HSDTE			AS OH_REQ_DELIV_DATE , 
		CHSSTM			AS OH_REQ_DELIV_TIME , 
		HCPO			AS OH_PO1 , 
		HEAPO1			AS OH_PO2 , 
		HEAPO2			AS OH_PO3 
  
	FROM		ECH_PLNK  -- ORDER HEADER 
	LEFT JOIN	ECHE_PLNK  -- ORDER HEADER EXTENSION 
	ON			HEORD = HORD 
  
	LEFT JOIN	ESTE  -- ship-to extension 
	ON HCUST = TECUST 
	AND HSHIP = TESHIP 
  
	LEFT JOIN	PLINK_USER 
	ON CHENUS = PLU_USER_ID 
  
	LEFT JOIN	RCME  -- customer master extension 
	ON HCUST = CECUST 
  
	WHERE		CEHHPF = IN_CUSTGROUP 
	AND			PLINK_STATUS = 'SUBM' 
	 -- If current user is a CSR, show all orders in history. 
	 -- If current user is not a CSR, do not show orders that were entered by a CSR. 
AND			( WK_CURRENTUSERISCSR = 'Y' OR ( WK_CURRENTUSERISCSR <> 'Y' AND PLU_POLAR_CSR <> 'Y' ) ) 
AND			( TRIM ( IN_FILTERUSERID ) = ''  -- show all user's orders 
			OR IN_FILTERUSERID = CHENUS )  -- filter by selected user ID 
	AND		IN_FROMDATE <= HEDTE 
	AND		IN_TODATE >= HEDTE 
  
	ORDER BY		HEDTE DESC , LX_ORDER_NUM DESC 
	; 
  
--================================================================ 
	 -- Initialize work variables 
	SET OUT_MESSAGE = '' ; 
	SET OUT_RESULT = '1' ; 
  
	 -- Determine if current user is a CSR. 
	 -- If current user is a CSR, show all orders in history. 
	 -- If current user is not a CSR, do not show orders that were entered by a CSR. 
	SELECT PLU_POLAR_CSR INTO WK_CURRENTUSERISCSR 
	FROM PLINK_USER WHERE PLU_USER_ID = IN_CURRUSERID ; 
  
	 -- If no from date specified, include only 3 months history 
	IF IN_FROMDATE = 0 THEN 
		SET IN_FROMDATE = FN_DATEDEC8 ( CURRENT_DATE - 3 MONTHS ) ; 
	END IF ; 
  
	IF IN_TODATE = 0 THEN 
		SET IN_TODATE = FN_DATEDEC8 ( CURRENT_DATE ) ; 
	END IF ; 
  
  
	 -- Open result set cursor being returned. 
	OPEN C1 ; 
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_GET_ORDERHISTORYSEARCH 
TO JVALANCE ; 
  
;
