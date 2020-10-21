SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_GET_ORDERHISTORY_DOWNLOAD ( 
	IN IN_CURRUSERID CHAR(10) , 
	IN IN_CUSTGROUP CHAR(10) , 
	IN IN_FILTERUSERID CHAR(10) , 
	IN IN_FROMDATE DECIMAL(8, 0) , 
	IN IN_TODATE DECIMAL(8, 0) , 
	OUT OUT_RESULT CHAR(1) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	DYNAMIC RESULT SETS 1 
	LANGUAGE SQL 
	SPECIFIC SP_GET_ORDERHISTORY_DOWNLOAD 
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
		HORD			AS ORDER_NO , 
		DATE ( TIMESTAMP_FORMAT ( DIGITS ( HEDTE ) , 'YYYYMMDD' ) ) AS ENTRY_DATE , 
		TIME ( TIMESTAMP_FORMAT ( DIGITS ( CHENTM ) , 'HH24MISS' ) ) AS ENTRY_TIME , 
		CASE 
			WHEN HVIA = 'P' THEN 'Pickup' 
			WHEN HVIA = 'D' THEN 'Delivery (no backhaul)' 
			WHEN HVIA = 'B' THEN 'Delivery with Backhaul' 
			ELSE '' 
		END			AS SHIP_METHOD , 
		HCUST			AS CUST_NO , 
		HSHIP			AS SHIPTO_NUM , 
		HATN			AS SHIPTO_ATTN , 
		HNAME			AS SHIPTO_NAME , 
		HAD1			AS SHIPTO_ADDR1 , 
		HAD2			AS SHIPTO_ADDR2 , 
		HAD3			AS SHIPTO_ADDR3 , 
		HSTE			AS SHIPTO_STATE , 
		HPOST			AS SHIPTO_ZIP , 
		HCOUN			AS SHIPTO_COUNTRY , 
		DATE ( TIMESTAMP_FORMAT ( DIGITS ( HSDTE ) , 'YYYYMMDD' ) ) AS REQ_DELIVERY_DATE , 
		TIME ( TIMESTAMP_FORMAT ( DIGITS ( CHSSTM ) , 'HH24MISS' ) ) AS REQ_DELIVERY_TIME , 
		HCPO			AS PO_NUM , 
		HEAPO1			AS PO_NUM2 , 
		HEAPO2			AS PO_NUM3 , 
		CHENUS			AS ENTRY_USER , 
  
		LLINE	AS LINE_NUM , 
		LPROD	AS ITEM_NUM , 
		IDESC	AS ITEM_DESC , 
		INTEGER ( LQORD ) AS QTY_ORDERED , 
		DEC ( LNET , 11 , 2 ) AS NET_PRICE , 
		DEC ( LLIST , 11 , 2 ) AS LIST_PRICE , 
		DEC ( LNET * LQORD , 13 , 2 ) AS EXT_PRICE , 
  
		IUMR	AS SELL_UOM , 
		DEC ( ( IWGHT / IUMRC ) , 11 , 2 ) AS ITEM_WGHT_LBS , 
		ICLAS	AS ITEM_CLASS , 
		INTEGER ( IVULP ) AS UNITS_PER_PALLET , 
  
		CASE 
			WHEN LQORD IS NOT NULL THEN INTEGER ( LQORD ) 
			ELSE 0 
		END AS ITM_CASE_QTY_ORD , 
		CASE 
			WHEN LQORD IS NOT NULL AND IUMR = '12' THEN INTEGER ( LQORD * 12 ) 
			WHEN LQORD IS NOT NULL AND IUMR = 'CS' THEN INTEGER ( LQORD * 24 ) 
			ELSE 0 
		END AS ITM_UNIT_QTY_ORD , 
		CASE 
			WHEN LQORD IS NOT NULL THEN DEC ( LQORD / IVULP , 13 , 4 ) 
			ELSE DEC ( 0 , 13 , 4 ) 
		END AS ITM_PALLET_QTY_ORD 
  
  
FROM			ECL_PLNK  -- Order Details 
LEFT JOIN	IIM  -- Item master 
ON			LPROD = IPROD 
  
JOIN			ECH_PLNK  -- ORDER HEADER 
ON			LORD = HORD 
  
LEFT JOIN	ECHE_PLNK  -- ORDER HEADER EXTENSION 
ON			HEORD = HORD 
  
LEFT JOIN	ESTE  -- ship-to extension 
				ON HCUST = TECUST 
					AND HSHIP = TESHIP 
  
	LEFT JOIN		PLINK_USER ON CHENUS = PLU_USER_ID 
  
LEFT JOIN	RCME  -- customer master extension 
				ON HCUST = CECUST 
  
WHERE		CEHHPF = IN_CUSTGROUP 
AND			PLINK_STATUS = 'SUBM' 
-- If current user is a CSR, show all orders in history. 
	 -- If current user is not a CSR, do not show orders that were entered by a CSR. 
AND			( WK_CURRENTUSERISCSR = 'Y' OR ( WK_CURRENTUSERISCSR <> 'Y' AND PLU_POLAR_CSR <> 'Y' ) ) 
AND			( TRIM ( IN_FILTERUSERID ) = ''  -- show all user's orders 
				OR IN_FILTERUSERID = CHENUS )  -- filter by selected user ID 
	AND			IN_FROMDATE <= HEDTE 
	AND			IN_TODATE >= HEDTE 
  
	ORDER BY		HEDTE DESC , LX_ORDER_NUM DESC , LLINE 
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
ON SPECIFIC PROCEDURE SP_GET_ORDERHISTORY_DOWNLOAD 
TO JVALANCE ; 
  
;
