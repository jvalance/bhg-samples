SET PATH *LIBL ;

CREATE OR REPLACE FUNCTION FN_GET_ITEM_PRICE ( 
	IN_ITEM CHAR(35) , 
	IN_QTY DECIMAL(7, 2) , 
	IN_CUST DECIMAL(8, 0) , 
	IN_SHIP DECIMAL(4, 0) , 
	IN_ORDER_NUM DECIMAL(8, 0) , 
	IN_LINE_NUM DECIMAL(4, 0) , 
	IN_ENTRY_DATE DECIMAL(8, 0) , 
	IN_REQUEST_DATE DECIMAL(8, 0) , 
	IN_PRICEBOOK_DATE DECIMAL(8, 0) ) 
	RETURNS DECIMAL(7, 2)   
	LANGUAGE SQL 
	SPECIFIC FN_GET_ITEM_PRICE 
	NOT DETERMINISTIC 
	MODIFIES SQL DATA 
	CALLED ON NULL INPUT 
	NOT FENCED 
	SET OPTION  ALWBLK = *ALLREAD , 
	ALWCPYDTA = *OPTIMIZE , 
	COMMIT = *NONE , 
	DECRESULT = (31, 31, 00) , 
	DFTRDBCOL = *NONE , 
	DYNDFTCOL = *NO , 
	DYNUSRPRF = *USER , 
	SRTSEQ = *HEX   
	BEGIN 
	DECLARE WK_ORDER DEC ( 8 , 0 ) DEFAULT 0 ; 
	DECLARE WK_LINE DEC ( 4 , 0 ) DEFAULT 0 ; 
	DECLARE WK_PGM CHAR ( 10 ) DEFAULT 'POLARLINK' ; 
	DECLARE OUT_LIST_PRICE		DEC ( 7 , 2 ) DEFAULT 0 ;	 -- List price 
	DECLARE OUT_NET_PRICE		DEC ( 7 , 2 ) DEFAULT 0 ;	 -- Net price 
	DECLARE OUT_LIST_PRICE_SRC	CHAR ( 2 ) DEFAULT '' ;	 -- List price source code 
	DECLARE OUT_NET_PRICE_SRC	CHAR ( 2 ) DEFAULT '' ;		 -- Net price source code 
	CALL SP_GET_ITEM_PRICE ( 
		IN_ITEM , 
		IN_QTY , 
		IN_CUST , 
		IN_SHIP , 
		IN_ORDER_NUM , 
		IN_LINE_NUM , 
		IN_ENTRY_DATE , 
		IN_REQUEST_DATE , 
		IN_PRICEBOOK_DATE , 
		 -- OUTPUT PARMS: 
		OUT_LIST_PRICE , 
		OUT_NET_PRICE , 
		OUT_LIST_PRICE_SRC , 
		OUT_NET_PRICE_SRC 
	) ; 
  
	RETURN OUT_NET_PRICE ; 
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC FUNCTION FN_GET_ITEM_PRICE 
TO JVALANCE ; 
  
;
