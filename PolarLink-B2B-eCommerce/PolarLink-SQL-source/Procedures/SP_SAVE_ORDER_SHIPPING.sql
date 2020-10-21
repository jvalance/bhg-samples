SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_SAVE_ORDER_SHIPPING ( 
	IN IN_CUSTNO DECIMAL(8, 0) , 
	IN IN_SHIPTO DECIMAL(8, 0) , 
	IN IN_SHIPMETHOD CHAR(1) , 
	IN IN_PLINKUSER CHAR(15) , 
	INOUT INOUT_ORDNUM DECIMAL(8, 0) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	LANGUAGE SQL 
	SPECIFIC SP_SAVE_ORDER_SHIPPING 
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
Created July 2015 by John Valance 
  
Insert or update Order Header with shipping information. 
  
Returns: 
	inout_OrdNum - If inout_OrdNum is passed in as zero, this will generate a 
		new order# and insert record in ECH_PLNK, passing back the order# generated. 
		If not passed in as zero, then update this order on ECH_PLNK. 
  
Parameters: 
	in_CustNo: Customer Number for this order. 
	in_ShipTo: ShipTo Number for this order. 
	in_ShipMethod: Shipping method for this order. 
	in_PLinkUser: Polar Link user id for user creating this order. 
  
=================================================================================*/ 
DECLARE WK_CURRDATE DEC ( 8 , 0 ) ; 
DECLARE WK_CURRTIME DEC ( 6 , 0 ) ; 
DECLARE WK_CREATEORDER CHAR ( 1 ) ; 
DECLARE WK_MSG VARCHAR ( 150 ) ; 
  
DECLARE WK_TCUST	DEC ( 8 , 0 ) ; 
DECLARE WK_TSHIP	DEC ( 4 , 0 ) ; 
DECLARE WK_TNAME	CHAR ( 50 ) ; 
DECLARE WK_TATN	CHAR ( 30 ) ; 
DECLARE WK_TADR1	CHAR ( 50 ) ; 
DECLARE WK_TADR2	CHAR ( 50 ) ; 
DECLARE WK_TADR3	CHAR ( 50 ) ; 
DECLARE WK_TSTE	CHAR ( 3 ) ; 
DECLARE WK_TPOST	CHAR ( 9 ) ; 
DECLARE WK_TCOUN	CHAR ( 4 ) ; 
DECLARE WK_CTYPE CHAR ( 4 ) ; 
DECLARE WK_ENTRYSTEP INTEGER ; 
  
	 --================================================================ 
	SET OUT_MESSAGE = '' ; 
  
	IF INOUT_ORDNUM = 0 THEN 
		 -- Call ORDD99 to retrieve next order number for this customer 
		CALL ORDD99 ( INOUT_ORDNUM , IN_CUSTNO ) ; 
		 --------------------------------- 
		 -- OLD logic for next order #: 
		 -- Get highest PLink Ref# and add 1 for new order# 
		 --select ifnull( dec( (max(HORD) + 1), 8, 0), 1) into inout_OrdNum from ECH_PLNK; 
		SET WK_CREATEORDER = 'Y' ; 
		SET WK_ENTRYSTEP = 1 ;  -- 1st step done 
	ELSE 
		SELECT PLINK_ENTRY_STEP INTO WK_ENTRYSTEP 
			FROM ECH_PLNK WHERE HORD = INOUT_ORDNUM ; 
		SET WK_CREATEORDER = 'N' ; 
	END IF ; 
  
	SET WK_CURRDATE = FN_CURRDATE8 ( ) ; 
	SET WK_CURRTIME = FN_CURRTIME6 ( ) ; 
  
	 -- get customer type from RCM 
	SELECT CTYPE INTO WK_CTYPE 
	FROM RCM WHERE CCUST = IN_CUSTNO ; 
  
	 -- Get ship-to fields from EST 
	SELECT TNAME , TATN , TADR1 , TADR2 , TADR3 , TSTE , TCOUN , TPOST 
	INTO WK_TNAME , WK_TATN , WK_TADR1 , WK_TADR2 , WK_TADR3 , WK_TSTE , WK_TCOUN , WK_TPOST 
	FROM EST 
	WHERE TCUST = IN_CUSTNO 
	AND TSHIP = IN_SHIPTO ; 
  
	IF WK_CREATEORDER = 'Y' THEN 
		INSERT INTO ECH_PLNK ( 
			HORD ,			HEDTE ,			HCUST ,			HCTYP ,			PLINK_STATUS , 
			HVIA ,			HATN ,			HNAME ,			HAD1 , 
			HAD2 ,			HAD3 ,			HPOST ,			HSHIP , 
			HCOUN ,			HSTE ,			CHENTM ,			CHENUS ,			PLINK_ENTRY_STEP 
		) VALUES ( 
			INOUT_ORDNUM ,	WK_CURRDATE ,	IN_CUSTNO ,		WK_CTYPE ,		'CURR' , 
			IN_SHIPMETHOD ,	WK_TATN ,		WK_TNAME ,		WK_TADR1 , 
			WK_TADR2 ,		WK_TADR3 ,		WK_TPOST ,		IN_SHIPTO , 
			WK_TCOUN ,		WK_TSTE ,		WK_CURRTIME ,	IN_PLINKUSER ,	WK_ENTRYSTEP 
		) ; 
	ELSE 
		UPDATE ECH_PLNK SET 
			HVIA = IN_SHIPMETHOD , 
			HCUST = IN_CUSTNO , 
			HSHIP = IN_SHIPTO , 
			HNAME = WK_TNAME , 
			HATN = WK_TATN , 
			HAD1 = WK_TADR1 , 
			HAD2 = WK_TADR2 , 
			HAD3 = WK_TADR3 , 
			HPOST = WK_TPOST , 
			HSTE = WK_TSTE , 
			HCOUN = WK_TCOUN , 
			CHMNDT = WK_CURRDATE , 
			CHMNTM = WK_CURRTIME , 
			CHMNUS = IN_PLINKUSER , 
			PLINK_ENTRY_STEP = WK_ENTRYSTEP 
		WHERE HORD = INOUT_ORDNUM ; 
	END IF ; 
  
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_SAVE_ORDER_SHIPPING 
TO JVALANCE ; 
  
;
