SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_SAVE_ORDERLINEITEM ( 
	IN IN_ORDERNUM DECIMAL(8, 0) , 
	IN IN_ITEMNO CHAR(35) , 
	IN IN_QTY DECIMAL(11, 3) , 
	IN IN_UOM CHAR(2) , 
	IN IN_USER VARCHAR(15) , 
	OUT OUT_RESULT CHAR(1) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	LANGUAGE SQL 
	SPECIFIC SP_SAVE_ORDERLINEITEM 
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
  
Save information entered on the Order Header page. 
=================================================================================*/ 
	DECLARE WK_ENTRYSTEP INTEGER ; 
	DECLARE WK_PO CHAR ( 23 ) ; 
	DECLARE WK_ITEM CHAR ( 35 ) ; 
	DECLARE WK_LINEITEMEXISTS CHAR ( 1 ) DEFAULT 'N' ; 
	DECLARE WK_LINE_NUM DEC ( 4 , 0 ) ; 
	DECLARE OH_CUSTNO DEC ( 8 , 0 ) ; 
	DECLARE OH_SHIPTO DEC ( 4 , 0 ) ; 
  
	DECLARE WK_ENTRYDATE DEC ( 8 , 0 ) ; 
	DECLARE WK_REQUESTDATE DEC ( 8 , 0 ) ; 
	DECLARE WK_SCHEDDATE DEC ( 8 , 0 ) ; 
--	declare wk_NetPrice dec(14,4); 
	DECLARE WK_LIST_PRICE		DEC ( 7 , 2 ) ; 
	DECLARE WK_NET_PRICE		DEC ( 7 , 2 ) ; 
	DECLARE WK_LIST_PRICE_SRC	CHAR ( 2 ) ;	 -- List price source code 
	DECLARE WK_NET_PRICE_SRC	CHAR ( 2 ) ;	 -- Net price source code 
	DECLARE WK_DATE8 DEC ( 8 , 0 ) ; 
	DECLARE WK_TIME6 DEC ( 6 , 0 ) ; 
	DECLARE WK_IDESC CHAR ( 30 ) ; 
	DECLARE WK_ICLAS CHAR ( 2 ) ; 
	DECLARE WK_IUMR CHAR ( 2 ) ; 
	DECLARE WK_IUMRC DEC ( 11 , 5 ) ; 
	DECLARE WK_IWGHT DEC ( 7 , 3 ) ; 
	DECLARE WK_ILIST DEC ( 11 , 3 ) ; 
	DECLARE WK_IITYP CHAR ( 1 ) ; 
	 -- ------------------------------------------- 
	 -- Initialize work variables 
	SET OUT_MESSAGE = '' ; 
	SET OUT_RESULT = '1' ; 
  
	SELECT LPROD , LLINE 
	INTO WK_ITEM , WK_LINE_NUM 
	FROM ECL_PLNK 
	WHERE LORD = IN_ORDERNUM 
	AND LPROD = IN_ITEMNO ; 
  
	IF WK_ITEM = IN_ITEMNO THEN 
		SET WK_LINEITEMEXISTS = 'Y' ; 
	ELSE 
		SET WK_LINEITEMEXISTS = 'N' ; 
		 -- Get next line item number 
		SELECT ( IFNULL ( MAX ( LLINE ) , 0 ) + 1 ) INTO WK_LINE_NUM 
		FROM ECL_PLNK 
		WHERE LORD = IN_ORDERNUM ; 
	END IF ; 
  
	 -- Get current date/time formatted values 
	SELECT FN_CURRDATE8 ( ) , FN_CURRTIME6 ( ) 
	INTO WK_DATE8 , WK_TIME6 
	FROM SYSIBM . SYSDUMMY1 ; 
  
  
	 -- Get Order Header info 
	SELECT HCUST , HSHIP , HEDTE , HRDTE , HSDTE , HCPO 
	INTO OH_CUSTNO , OH_SHIPTO , WK_ENTRYDATE , WK_REQUESTDATE , WK_SCHEDDATE , WK_PO 
	FROM ECH_PLNK 
	LEFT JOIN ECHE_PLNK ON HORD = HEORD 
	WHERE HORD = IN_ORDERNUM ; 
  
  
	 -- Get Item Master info 
	SELECT IDESC , ICLAS , IUMR , IUMRC , 
			IWGHT , ILIST , IITYP  -- Item Type (0,1,2,3,4,5,...User Def.) 0=Phantom,5=T 
	INTO	WK_IDESC , WK_ICLAS , WK_IUMR , WK_IUMRC , 
			WK_IWGHT , WK_ILIST , WK_IITYP 
	FROM	IIM 
	WHERE	IPROD = IN_ITEMNO ; 
  
  
	 -- Get pricing 
	 --========================================== 
--	select 
--		fn_Get_Item_Price(in_ItemNo, in_Qty, oh_CustNo, oh_ShipTo, in_OrderNum, wk_line_num, wk_SchedDate, wk_SchedDate) as Price 
--		into wk_net_price 
--	from sysibm.sysdummy1; 
	CALL SP_GET_ITEM_PRICE ( 
		IN_ITEMNO , 
		IN_QTY , 
		OH_CUSTNO , 
		OH_SHIPTO , 
		IN_ORDERNUM , 
		WK_LINE_NUM , 
		WK_ENTRYDATE , 
		WK_REQUESTDATE , 
		WK_SCHEDDATE , 
		 -- OUTPUT PARMS: 
		WK_LIST_PRICE , 
		WK_NET_PRICE , 
		WK_LIST_PRICE_SRC , 
		WK_NET_PRICE_SRC 
	) ; 
  
	IF WK_LINEITEMEXISTS = 'Y' THEN 
		IF IN_QTY <= 0 THEN 
			DELETE FROM ECL_PLNK 
				WHERE LORD = IN_ORDERNUM 
				AND LPROD = IN_ITEMNO ; 
		ELSE 
			UPDATE ECL_PLNK 
			SET 
--				LQORD = (in_Qty * wk_IUMRC), 
				LLIST = ( WK_LIST_PRICE * WK_IUMRC ) , 
				LNET = ( WK_NET_PRICE * WK_IUMRC ) , 
				LQORD = IN_QTY , 
--				LLIST = wk_list_price, 
--				LNET = wk_net_price, 
				CLLPSC = WK_LIST_PRICE_SRC , 
				CLNPSC = WK_NET_PRICE_SRC , 
				CLMNDT = WK_DATE8 ,  --	Last Maintenance Date 
				CLMNTM = WK_TIME6 ,  --	Last Maintenance Time 
				CLMNUS = IN_USER  --	Last Maintenance User 
			WHERE LORD = IN_ORDERNUM 
			AND LPROD = IN_ITEMNO ; 
		END IF ; 
	ELSE  -- Add this item to the order 
		IF IN_QTY <= 0 THEN 
			 -- Quantity must be greater than zero 
			SET OUT_MESSAGE = 'Invalid quantity (' || CHAR ( IN_QTY ) || ') for item# ' || IN_ITEMNO ; 
			SET OUT_RESULT = '0' ; 
			RETURN ; 
		END IF ; 
  
		INSERT INTO ECL_PLNK ( 
			LORD ,  --	Order Number 
			LLINE ,  --	Order Line Number 
			LPROD ,  --	Item Number 
			LCLAS ,  --	Item Class 
			LQORD ,  --	Quantity Ordered 
			LNET ,  -- 	Net Price 
			LLIST ,  --   List Price 
			CLLPSC ,  -- List price source code 
			CLNPSC ,  -- Net price source code 
			LUM ,  --	Selling Unit of Measure 
			LRDTE ,  --	Requested Date 
			LSTAT ,  --	Line Status E, I, P 
			LDESC ,  --	Item Description 
			LCUST ,  --	Customer Number 
			LSHIP ,  --	Ship To Number 
			CLSTS1 ,  --	Ready for Pick Release 
			LODTE ,  --	Date Entered 
			CLENTM ,  --	Created At Time 
			CLENUS  --	Created By User 
		) VALUES ( 
			IN_ORDERNUM ,  --	Order Number 
			WK_LINE_NUM ,  --	Order Line Number 
			IN_ITEMNO ,  --	Item Number 
			WK_ICLAS ,  --	Item Class 
			( IN_QTY ) ,  --	Quantity Ordered 
			( WK_NET_PRICE * WK_IUMRC ) ,  -- 	Net Price 
			( WK_LIST_PRICE * WK_IUMRC ) ,  -- list price 
--			wk_net_price,  -- 	Net Price 
--			wk_list_price,  -- list price 
			WK_LIST_PRICE_SRC , 
			WK_NET_PRICE_SRC , 
			WK_IUMR ,  --	Selling Unit of Measure 
			WK_REQUESTDATE ,  --	Requested Date 
			'E' ,  --	Line Status E, I, P 
			WK_IDESC ,  --	Item Description 
			OH_CUSTNO ,  --	Customer Number 
			OH_SHIPTO ,  --	Ship To Number 
			1 ,  --	Ready for Pick Release 
			WK_DATE8 ,  --	Date Entered 
			WK_TIME6 ,  --	Created At Time 
			IN_USER  --	Created By User 
		) ; 
  
	END IF ; 
  
  
	 -- Ensure last entry step is at least 3 (i.e., line item added to order) 
	SELECT PLINK_ENTRY_STEP INTO WK_ENTRYSTEP 
		FROM ECH_PLNK WHERE HORD = IN_ORDERNUM ; 
	IF WK_ENTRYSTEP < 3 THEN 
		SET WK_ENTRYSTEP = 3 ; 
	END IF ; 
  
	UPDATE ECH_PLNK SET 
		CHMNDT = WK_DATE8 , 
		CHMNTM = WK_TIME6 , 
		CHMNUS = IN_USER , 
		PLINK_ENTRY_STEP = WK_ENTRYSTEP 
	WHERE HORD = IN_ORDERNUM ; 
  
	RETURN ; 
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_SAVE_ORDERLINEITEM 
TO JVALANCE ; 
  
;
