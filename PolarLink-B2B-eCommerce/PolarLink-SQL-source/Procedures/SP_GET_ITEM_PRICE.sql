SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_GET_ITEM_PRICE ( 
	IN IN_ITEM CHAR(35) , 
	IN IN_QTY DECIMAL(7, 2) , 
	IN IN_CUST DECIMAL(8, 0) , 
	IN IN_SHIP DECIMAL(4, 0) , 
	IN IN_ORDER_NUM DECIMAL(8, 0) , 
	IN IN_LINE_NUM DECIMAL(4, 0) , 
	IN IN_ENTRY_DATE DECIMAL(8, 0) , 
	IN IN_REQUEST_DATE DECIMAL(8, 0) , 
	IN IN_PRICEBOOK_DATE DECIMAL(8, 0) , 
	OUT OUT_LIST_PRICE DECIMAL(7, 2) , 
	OUT OUT_NET_PRICE DECIMAL(7, 2) , 
	OUT OUT_LIST_PRICE_SRC CHAR(2) , 
	OUT OUT_NET_PRICE_SRC CHAR(2) ) 
	LANGUAGE SQL 
	SPECIFIC SP_GET_ITEM_PRICE 
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
--	declare wk_pricebook_date dec(8, 0); 
--	declare wk_net_price dec(7, 2) default 0; 
--	declare wk_list_price dec(7, 2) default 0; 
--	declare wk_qty dec(7, 2) default 0; 
--	declare wk_list_price_src char(2) default '', 
--	declare wk_net_price_src char(2) default '' 
--	set wk_pricebook_date = in_request_date; 
--	declare wk_order dec(8, 0) default 33318701; 
--	declare wk_line dec(4, 0) default 1; 
	DECLARE WK_PGM CHAR ( 10 ) DEFAULT 'POLARLINK' ; 
  
	 -- Initialize output parms to avoid null value exception 
	SET OUT_NET_PRICE = 0 ; 
	SET OUT_LIST_PRICE = 0 ; 
	SET OUT_LIST_PRICE_SRC = '' ; 
	SET	OUT_NET_PRICE_SRC = '' ; 
  
	CALL SYS664 ( 'SYS664' ) ;  -- load LDA 
	CALL PROG40 ( IN_ITEM , 
				IN_CUST , 
				IN_SHIP , 
				IN_ENTRY_DATE , 
				IN_REQUEST_DATE , 
				IN_PRICEBOOK_DATE , 
				OUT_NET_PRICE , 
				OUT_LIST_PRICE , 
  
				IN_ORDER_NUM , 
				IN_LINE_NUM , 
				IN_QTY , 
				WK_PGM , 
				OUT_LIST_PRICE_SRC , 
				OUT_NET_PRICE_SRC 
		) ; 
  
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_GET_ITEM_PRICE 
TO JVALANCE ; 
  
;
