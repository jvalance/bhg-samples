SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_GET_ORDERLINEITEMS ( 
	IN IN_ORDERNUM DECIMAL(8, 0) , 
	OUT OUT_RESULT CHAR(1) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	DYNAMIC RESULT SETS 1 
	LANGUAGE SQL 
	SPECIFIC SP_GET_ORDERLINEITEMS 
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
  
Retrieve line items for display on item search, order review, and order history pages. 
  
Returns: 
	Result set with multiple rows 
  
=================================================================================*/ 
  
DECLARE C1 CURSOR WITH RETURN FOR 
SELECT 
	LORD	AS OL_ORDER_NUM , 
		LLINE	AS OL_LINE_NUM , 
		LPROD	AS OL_ITEM_NUM , 
		IDESC	AS OL_ITEM_DESC , 
  
/*		dec((LQORD / IUMRC),11,3)	as OL_QTY_ORD, 
		dec((LNET / IUMRC),11,2)	as OL_NET_PRICE, 
		dec((LLIST / IUMRC),11,2)	as OL_LIST_PRICE, 
		dec( ((LNET * LQORD) / IUMRC), 13,2) as OL_EXT_PRICE, 
		dec( ((LQORD / IVULP) / IUMRC), 13, 4) as OL_PALLET_QTY, 
*/ 
		LQORD AS OL_QTY_ORD , 
		LNET AS OL_NET_PRICE , 
		LLIST AS OL_LIST_PRICE , 
		DEC ( LNET * LQORD , 13 , 2 ) AS OL_EXT_PRICE , 
		DEC ( LQORD / IVULP , 13 , 4 ) AS OL_PALLET_QTY , 
  
		IUMR	AS OL_SELL_UOM , 
		IUMRC	AS OL_SELL_UOM_CONV , 
		DEC ( ( IWGHT / IUMRC ) , 11 , 2 ) AS OL_ITEM_WGHT , 
		ICLAS	AS OL_ITEM_CLASS , 
		IVULP	AS OL_UNITS_PER_PALLET , 
  
		CASE 
			WHEN LQORD IS NOT NULL THEN DEC ( LQORD , 13 , 2 ) 
			ELSE DEC ( 0 , 13 , 2 ) 
		END AS ITM_CASE_QTY_ORD , 
  
		CASE 
			WHEN LQORD IS NOT NULL AND IUMR = '12' THEN DEC ( LQORD * 12 , 13 , 2 ) 
			WHEN LQORD IS NOT NULL AND IUMR = 'CS' THEN DEC ( LQORD * 24 , 13 , 2 ) 
			ELSE DEC ( 0 , 13 , 2 ) 
		END AS ITM_UNIT_QTY_ORD , 
  
		CASE 
			WHEN LQORD IS NOT NULL THEN DEC ( LQORD / IVULP , 13 , 4 ) 
			ELSE DEC ( 0 , 13 , 4 ) 
		END AS ITM_PALLET_QTY_ORD 
  
  
FROM			ECL_PLNK  -- Order Details 
LEFT JOIN	IIM  -- Item master 
ON			LPROD = IPROD 
  
WHERE		LORD = IN_ORDERNUM 
; 
