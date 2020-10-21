SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_GET_ORDERHEADER ( 
	IN IN_ORDERNUM DECIMAL(8, 0) , 
	OUT OUT_RESULT CHAR(1) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	DYNAMIC RESULT SETS 1 
	LANGUAGE SQL 
	SPECIFIC SP_GET_ORDERHEADER 
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
  
DECLARE C1 CURSOR WITH RETURN FOR 
SELECT 
		CASE 
			WHEN LX_ORDER_NUM = 0 
			THEN HORD 
			ELSE LX_ORDER_NUM 
		END AS OH_ORDERNO , 
		HEDTE AS OH_ENTRY_DATE , 
		CHENTM AS OH_ENTRY_TIME , 
		HCUST AS OH_CUSTNO , 
		CNME AS OH_CUSTNAME , 
		HSHIP AS OH_SHP2_NUM , 
		HCTYP AS OH_CUST_TYPE , 
		HVIA AS OH_SHIP_METHOD_CODE , 
		CASE 
			WHEN HVIA = 'P' THEN 'Pickup' 
			WHEN HVIA = 'D' THEN 'Delivery (no backhaul)' 
			WHEN HVIA = 'B' THEN 'Delivery with Backhaul' 
			ELSE '' 
		END AS OH_SHIP_METHOD_TEXT , 
  
		 -- CESUBS = 'Y' means always require substitutes 
		 -- CESUBS = ' ' means check ship-to field TESUBS 
		CASE WHEN ( CESUBS IS NULL ) OR ( CESUBS = ' ' ) 
			THEN IFNULL ( TESUBS , ' ' ) 
			ELSE IFNULL ( CESUBS , ' ' ) 
		END AS SUBS_REQUIRED ,  -- require substitutes flag 
		HATN AS OH_SHP2_ATTN , 
		HNAME AS OH_SHP2_NAME , 
		HAD1 AS OH_SHP2_ADDR1 , 
		HAD2 AS OH_SHP2_ADDR2 , 
		HAD3 AS OH_SHP2_ADDR3 , 
		HPOST AS OH_SHP2_ZIP , 
		HCOUN AS OH_SHP2_COUNTRY , 
		HSTE AS OH_SHP2_STATE , 
		CHENUS AS OH_ENTRY_USER , 
		PLINK_STATUS AS OH_PLINK_STATUS , 
		HSDTE AS OH_REQ_DELIV_DATE , 
		CHSSTM AS OH_REQ_DELIV_TIME , 
		HCPO AS OH_PO1 , 
		HEAPO1 AS OH_PO2 , 
		HEAPO2 AS OH_PO3 , 
		IFNULL ( CEPLSPRC , ' ' ) AS SUPPRESS_PRICING , 
		PLINK_ENTRY_STEP 
  
FROM		ECH_PLNK  -- ORDER HEADER 
LEFT JOIN	ECHE_PLNK  -- ORDER HEADER EXTENSION 
			ON HEORD = HORD 
  
LEFT JOIN	ESTE  -- ship-to extension 
			ON HCUST = TECUST 
			AND HSHIP = TESHIP 
  
LEFT JOIN	RCME  -- customer master extension 
			ON HCUST = CECUST 
  
LEFT JOIN	RCM  -- customer master 
			ON HCUST = CCUST 
  
WHERE		HORD = IN_ORDERNUM 
; 
