SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_SUBMITORDER ( 
	IN IN_ORDERNUM DECIMAL(8, 0) , 
	IN IN_USER VARCHAR(15) , 
	IN IN_EMAIL_ADDR VARCHAR(100) , 
	OUT OUT_ORDERNO DECIMAL(8, 0) , 
	OUT OUT_RESULT CHAR(1) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	LANGUAGE SQL 
	SPECIFIC SP_SUBMITORDER 
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
  
Submit PolarLink order to the LX files and update status in PolarLink to "submitted". 
=================================================================================*/ 
	DECLARE WK_SHIPMETHOD CHAR ( 1 ) DEFAULT ' ' ; 
	DECLARE WK_HVIA CHAR ( 10 ) DEFAULT ' ' ; 
	DECLARE LX_HVIA CHAR ( 10 ) DEFAULT ' ' ; 
	DECLARE WK_HEPUDEL CHAR ( 1 ) DEFAULT ' ' ; 
	DECLARE WK_HEPTRK CHAR ( 1 ) DEFAULT ' ' ; 
	DECLARE WK_ZPA_DATA CHAR ( 54 ) DEFAULT ' ' ; 
	DECLARE WK_ZPA_DATA_PREFIX CHAR ( 1 ) DEFAULT ' ' ; 
	 --declare wk_next_ordno_alpha char(8) default ' '; 
	 --declare wk_next_ordno dec(8,0) default 0; 
	DECLARE WK_NUM_SUBS INTEGER DEFAULT 0 ; 
	DECLARE WK_CRDH CHAR ( 2 ) DEFAULT '00' ; 
	DECLARE WK_ERRFLG CHAR ( 1 ) DEFAULT ' ' ; 
	DECLARE WKCURRDATE8 DEC ( 8 , 0 ) DEFAULT 0 ; 
	DECLARE WKCURRTIME6 DEC ( 6 , 0 ) DEFAULT 0 ; 
  
	 --========================================= 
	 -- ECH defaults 
	 --========================================= 
	DECLARE WK_LINECOUNT DEC ( 4 , 0 ) DEFAULT 0 ; 
	DECLARE WK_TOTALAMOUNT DEC ( 15 , 2 ) DEFAULT 0 ; 
	DECLARE WK_TOTALCOST DEC ( 15 , 2 ) DEFAULT 0 ; 
	DECLARE WK_TOTALQTY DEC ( 11 , 3 ) DEFAULT 0 ; 
	DECLARE WK_TOTALVOL DEC ( 11 , 3 ) DEFAULT 0 ; 
	DECLARE WK_TOTALWGT DEC ( 11 , 3 ) DEFAULT 0 ; 
	DECLARE WK_TOTALPAL DEC ( 11 , 3 ) DEFAULT 0 ; 
	DECLARE WK_CHOCLS DEC ( 3 , 0 ) DEFAULT 0 ; 
	DECLARE WK_CEOCLS DEC ( 3 , 0 ) DEFAULT 0 ; 
	DECLARE WK_CM1CLS DEC ( 3 , 0 ) DEFAULT 0 ; 
	DECLARE WK_HSHIP DEC ( 4 , 0 ) DEFAULT 0 ; 
  
	DECLARE WK_HSDTE DEC ( 8 , 0 ) DEFAULT 0 ; 
	DECLARE WK_REQDATE DEC ( 8 , 0 ) DEFAULT 0 ; 
	DECLARE WK_HCUST DEC ( 8 , 0 ) DEFAULT 0 ; 
	DECLARE WK_CCOM CHAR ( 2 ) DEFAULT ' ' ; 
	DECLARE WK_CMBANK CHAR ( 3 ) DEFAULT ' ' ; 
	DECLARE WK_CPHON CHAR ( 25 ) DEFAULT ' ' ; 
	DECLARE WK_TPHON CHAR ( 25 ) DEFAULT ' ' ; 
	DECLARE WK_CMAPPR DEC ( 1 , 0 ) DEFAULT 0 ; 
	DECLARE WK_CMDFOT CHAR ( 1 ) DEFAULT ' ' ; 
	DECLARE WK_CCCUS DEC ( 8 , 0 ) DEFAULT 0 ; 
	DECLARE WK_HROUT CHAR ( 6 ) DEFAULT '      ' ; 
	DECLARE WK_WHS CHAR ( 3 ) DEFAULT '   ' ; 
	DECLARE WK_FACILITY CHAR ( 3 ) DEFAULT '   ' ; 
	DECLARE WK_CHSSTM DEC ( 6 , 0 ) ; 
  
	DECLARE WK_HREG CHAR ( 6 ) DEFAULT ' ' ;  -- from TREG in EST 
	DECLARE WK_CCOUN CHAR ( 2 ) DEFAULT ' ' ;  -- from CCOUN in RCM 
	DECLARE WK_TCOUN CHAR ( 2 ) DEFAULT ' ' ;  -- from TCOUN in EST 
	DECLARE WK_HEDEPWHS CHAR ( 3 ) DEFAULT ' ' ;  -- from TEDEPWHS in ESTE 
	DECLARE WK_CHDYR DEC ( 2 , 0 ) DEFAULT 0 ; 
	DECLARE WK_HTERM CHAR ( 2 ) DEFAULT ' ' ;  -- from CTERM in RCM 
	DECLARE WK_HTDES CHAR ( 15 ) DEFAULT ' ' ;  -- from TMDESC in RTM 
	DECLARE WK_CTYPE CHAR ( 4 ) DEFAULT ' ' ;  -- from CTYPE in RCM 
	DECLARE WK_DROPSHIPCUST DEC ( 1 , 0 ) DEFAULT 0 ;  -- from RCM: CDSALW  Drop Ship Allowed = 1  
	DECLARE WK_DROPSHIPITEM DEC ( 1 , 0 ) DEFAULT 0 ;  -- from IIM: IDSALW  Drop Ship Allowed = 1  
	 -- ECL work fields 
	DECLARE WK_LQORD DEC ( 11 , 3 ) ; 
	DECLARE WKWGTORD DEC ( 11 , 4 ) DEFAULT 0 ; 
	DECLARE WK_LNET DEC ( 19 , 7 ) DEFAULT 0 ; 
	DECLARE WK_LLIST DEC ( 19 , 7 ) DEFAULT 0 ; 
	DECLARE WK_CLLPAV CHAR ( 1 ) DEFAULT ' ' ; 
	DECLARE WK_SELLING_PRICE DEC ( 14 , 4 ) DEFAULT 0 ; 
	DECLARE WK_SELLING_QTY DEC ( 11 , 3 ) DEFAULT 0 ; 
  
	 --====================================================================== 
	 -- Initialize work variables 
	 --====================================================================== 
	SET OUT_MESSAGE = '' ; 
	SET OUT_RESULT = '1' ; 
	SET WKCURRDATE8 = FN_CURRDATE8 ( ) ; 
	SET WKCURRTIME6 = FN_CURRTIME6 ( ) ; 
	 -- Get 2 digit current year 
	SET WK_CHDYR = DEC ( SUBSTR ( CHAR ( CURRENT DATE , ISO ) , 3 , 2 ) , 2 , 0 ) ; 
  
  
	 --====================================================================== 
	 -- Confirm order not already submitted or cancelled 
	 --====================================================================== 
	IF NOT EXISTS ( 
		SELECT * FROM ECH_PLNK 
		WHERE HORD = IN_ORDERNUM AND PLINK_STATUS = 'CURR' ) 
	THEN 
		SET OUT_RESULT = '0' ; 
		SET OUT_MESSAGE = 'PLINK Order Number ' || DIGITS ( IN_ORDERNUM ) || ' is not a current active order, and cannot be submitted' ; 
		RETURN ; 
	END IF ; 
  
  
	 --====================================================================== 
	 -- Retrieve default values for ECH 
	 --====================================================================== 
	 -- Order header information 
	SELECT HSDTE , HCUST , CHOCLS , HRDTE , HSHIP , CHSSTM , HVIA 
	INTO WK_HSDTE , WK_HCUST , WK_CHOCLS , WK_REQDATE , WK_HSHIP , WK_CHSSTM , WK_HVIA 
	FROM ECH_PLNK 
	WHERE HORD = IN_ORDERNUM ; 
  
	IF WK_HVIA = 'B' THEN 
		 -- 'B' is delivery with backhaul. Convert to 'D' for delivery in LX's HVIA field 
		SET LX_HVIA = 'D' ; 
	ELSE 
		SET LX_HVIA = WK_HVIA ; 
	END IF ; 
  
  
	 -- Customer information 
	SELECT	CASE WHEN CMDFOT = ' ' THEN '1' ELSE CMDFOT END , 
			CCOM , CMBANK , CMAPPR , CCCUS , 
			CM1CLS , CEOCLS , CPHON , CTYPE , 
			CCOUN , TMTERM , TMDESC , CDSALW 
	INTO	WK_CMDFOT , 
			WK_CCOM , WK_CMBANK , WK_CMAPPR , WK_CCCUS , 
			WK_CM1CLS , WK_CEOCLS , WK_CPHON , WK_CTYPE , 
			WK_CCOUN , WK_HTERM , WK_HTDES , WK_DROPSHIPCUST 
	FROM RCM 
	LEFT JOIN RCME ON CCUST = CECUST 
	LEFT JOIN RTM ON CTERM = TMTERM 
	WHERE CCUST = WK_HCUST ; 
  
  
	 -- get ship-to dfaults from EST 
	SELECT TPHONE , STFWHS , TREG , TCOUN 
	INTO WK_TPHON , WK_WHS , WK_HREG , WK_TCOUN 
	FROM EST WHERE TCUST = WK_HCUST AND TSHIP = WK_HSHIP ; 
  
	 -- get depot warehouse from ship-to extension table 
	SELECT TEDEPWHS 
	INTO WK_HEDEPWHS 
	FROM ESTE WHERE TECUST = WK_HCUST AND TESHIP = WK_HSHIP ; 
  
	 -- get facility based on warehouse code 
	SELECT WMFAC INTO WK_FACILITY FROM IWM WHERE LWHS = WK_WHS ; 
  
	 -- If ship-to phone# not blank, use it, instead of customer master phone# 
	IF TRIM ( WK_TPHON ) <> '' THEN 
		SET WK_CPHON = WK_TPHON ; 
	END IF ; 
  
	SET WK_CHOCLS = 
		CASE WHEN WK_CEOCLS > 0 AND WK_CEOCLS <> WK_CM1CLS 
			THEN WK_CEOCLS 
			ELSE WK_CM1CLS 
		END ; 
  
	IF WK_CHOCLS = 420 THEN 
		SET WK_HROUT = DIGITS ( WK_CHSSTM ) ; 
	END IF ; 
  
	 -- Line item totals 
	SELECT	COUNT ( * ) , SUM ( LQORD * LNET ) , SUM ( LQORD * ISCST ) , SUM ( LQORD ) , 
			SUM ( LQORD * IVULI ) , SUM ( LQORD / IVULP ) , SUM ( IWGHT * LQORD ) 
	INTO	WK_LINECOUNT , WK_TOTALAMOUNT , WK_TOTALCOST , WK_TOTALQTY , 
			WK_TOTALVOL , WK_TOTALPAL , WK_TOTALWGT 
	FROM ECL_PLNK 
	JOIN IIM ON LPROD = IPROD 
	WHERE LORD = IN_ORDERNUM ; 
  
	 --====================================================================== 
	 -- Call ORDD99 to retrieve next order number for this customer 
	 --====================================================================== 
	 --CALL ORDD99(wk_next_ordno, wk_HCUST); -- Jul2016 - now this is done at start of order cycle 
	 --====================================================================== 
	 -- Call ORD503B to determine if customer is on credit hold 
	 --====================================================================== 
	CALL SYS664 ( 'SYS664' ) ;  -- load LDA 
	CALL CREDHOLD ( DIGITS ( WK_HCUST ) , WK_CRDH , WK_ERRFLG ) ; 
  
  
	 --====================================================================== 
	 -- OLD LOGIC FOR RETRIEVING ORDER NUMBER FROM ZPA 
	 -- above is correct - call ordd99 
	 -- ------------------------------------------ 
	 -- ZPA - Retrieve/increment next order number 
	 --====================================================================== 
/* 
	-- get last order number used 
	select DATA into wk_zpa_data 
	from ZPA where pkey = 'ORD500ON'; 
  
	set wk_zpa_data_prefix = substr(wk_zpa_data, 1, 1); 
	set wk_next_ordno_alpha = substr(wk_zpa_data, 2, 8); 
	set wk_next_ordno = dec(wk_next_ordno_alpha, 8, 0) + 1; 
	set wk_zpa_data = wk_zpa_data_prefix || digits(wk_next_ordno); 
  
	-- update ZPA with new value 
	update ZPA set DATA = wk_zpa_data 
	where pkey = 'ORD500ON'; 
*/ 
  
	 --====================================================================== 
	 -- ECH - Order Header 
	 --====================================================================== 
	INSERT INTO ECH ( 
		HORD ,			HEDTE ,			HCUST ,			HCTYP , 
		HVIA ,			HATN ,			HNAME ,			HAD1 , 
		HAD2 ,			HAD3 ,			HPOST ,			HSHIP , 
		HCOUN ,			HSTE ,			CHENTM ,			CHENUS ,		CHPRCC , 
		HSDTE ,			CHSSTM ,			HCPO ,			CHCWO ,		CHCLOS , 
		CHMNDT ,		CHMNTM ,			CHMNUS ,			CHVDAT , 
  
		HID , HLINS , HDTOT , HSTAT , HSAL , HTERM , HTDES , HCOST , HDTYP , HPTOT , 
		HCRED , HWHSE , HRDTE , HCOMP , HPRF , HREG , HPAYC , 
		HTAX , HINVN , HCURR , HCNVFC , HBTOT , HGCNFC , HCOM , HRCDT , CHREAS , 
		CHCOM , CHBANK , CHCURT , HBO , HVOLUM , HWEIGT , HPALET , 
		CHCPHN , CHPHON , CHCRDH , CHCUSH , CHMRGH , CHUSRH , CHIRES , 
		CHAPPR , CHPRBD , CHPRSC , CHBOCL , CHOCLS , CHUTYP , 
		CHSTS1 , CHTOEA , CHQORL , CHARCU , CHPRCU , CHDLCU , CHINCU , 
		CHIXCU , CHLBCU , CHPPCU , CHSHCU , CHSLCU , CHDYR , 
		CHNVF2 , CHCNG2 , CHCNC2 , CHCMTB , CHCMTG , CHCMTC , 
		CHPPB , CHPFAC , CHPQB , CHAPB , CHAQB , HROUT 
	) 
	SELECT 
		IN_ORDERNUM ,	WKCURRDATE8 ,	HCUST ,			WK_CTYPE , 
		LX_HVIA ,		HATN ,			HNAME ,			HAD1 , 
		HAD2 ,			HAD3 ,			HPOST ,			HSHIP , 
		WK_TCOUN ,		HSTE ,			CHENTM ,			'POLARLINK' ,	WK_CCOUN , 
		HSDTE ,			CHSSTM ,			HCPO ,			'0' ,		'0' , 
		WKCURRDATE8 ,	WKCURRTIME6 ,	'POLARLINK' ,	WKCURRDATE8 , 
  
		'CH' , WK_LINECOUNT , WK_TOTALAMOUNT , 'E' , 40 , WK_HTERM , WK_HTDES , WK_TOTALCOST , WK_CMDFOT , WK_TOTALQTY , 
		'0' , WK_WHS , HRDTE , 1 , '001' , WK_HREG , 'C' , 
		'EX  ' , IN_ORDERNUM , 'USD' , 1 , WK_TOTALAMOUNT , 1 , '01' , WKCURRDATE8 , 'BILNG' , 
		WK_CCOM , WK_CMBANK , 1 , 4 , WK_TOTALVOL , WK_TOTALWGT , WK_TOTALPAL , 
		WK_CPHON , WK_CPHON , WK_CRDH , '00' , '00' , '00' , 'AA' , 
		WK_CMAPPR , WK_HSDTE , '02' , 4 , WK_CHOCLS , WK_CMDFOT , 
		1 , WK_TOTALAMOUNT , WK_TOTALQTY , WK_CCCUS , WK_CCCUS , WK_HCUST , WK_CCCUS , 
		WK_HCUST , WK_CCCUS , WK_HCUST , WK_HCUST , WK_HCUST , WK_CHDYR , 
		1.0 , 1.0 , 1.0 , '8' , '8' , '8' , 
		WK_TOTALAMOUNT , ' ' , WK_TOTALQTY , WK_TOTALAMOUNT , WK_TOTALQTY , WK_HROUT 
  
		FROM ECH_PLNK 
		WHERE HORD = IN_ORDERNUM 
	; 
  
-- Update Customer master 
UPDATE RCM SET COPEN = ( COPEN + WK_TOTALAMOUNT ) WHERE CCUST = WK_HCUST ; 
  
IF WK_HCUST <> WK_CCCUS THEN 
UPDATE RCM SET COPEN = ( COPEN + WK_TOTALAMOUNT ) WHERE CCUST = WK_CCCUS ; 
END IF ; 
  
	 --====================================================================== 
	 -- ECHE - Order Header extension 
	 --====================================================================== 
	 -- Determine pickup/delivery and backhaul flags for ECHE 
	SELECT HVIA INTO WK_HVIA FROM ECH_PLNK WHERE HORD = IN_ORDERNUM ; 
  
	SET WK_HEPUDEL = CASE 
		WHEN WK_HVIA IN ( 'D' , 'B' ) THEN 'D' 
		ELSE 'P' 
	END ; 
  
	IF WK_HVIA = 'B' THEN 
		SET WK_HEPTRK = 'Y' ; 
	ELSE 
		SET WK_HEPTRK = 'N' ; 
	END IF ; 
  
	INSERT INTO ECHE ( 
		HEORD ,		 --	Order Number 
		HEDEPWHS ,	 -- Depot Warehouse 
		HEAPO1 ,	 -- Alt PO 1 
		HEAPO2 ,	 -- Alt PO 2 
		 -- HEPULOC, -- Pickup Location 
		HEPUDEL ,	 -- Pickup/Delivery (P/D) 
		HEPTRK		 -- Polar truck (backhaul) 
	) SELECT 
			IN_ORDERNUM , 
			WK_HEDEPWHS ,  -- Depot Warehouse -- from ESTE field TEDEPWHS 
			HEAPO1 ,	 -- Alt PO 1 
			HEAPO2 ,	 -- Alt PO 2 
			 -- HEPULOC, -- Pickup Location -- leave it blank 
			WK_HEPUDEL ,	 -- Pickup/Delivery (P/D) 
			WK_HEPTRK		 -- Polar truck (backhaul) 
	FROM ECHE_PLNK WHERE HEORD = IN_ORDERNUM 
	; 
  
  
	 --====================================================================== 
	 -- ESN - Order comments 
	 --====================================================================== 
	IF EXISTS ( SELECT * FROM ESN_PLNK WHERE SNTYPE = 'O' AND SNCUST = IN_ORDERNUM ) THEN 
		INSERT INTO ESN ( 
			SNID ,		SNTYPE ,		SNCUST ,		SNSHIP , 
			SNSEQ ,		SNDESC ,		SNDOCR , 
			SNPRT ,		SNPIC ,			SNINV ,		SNSTMT , 
			SNENDT ,		SNENTM ,		SNENUS , 
			SNMNDT ,		SNMNTM ,		SNMNUS 
		) SELECT 
			'SN' ,		SNTYPE ,		IN_ORDERNUM ,	SNSHIP , 
			SNSEQ ,		UPPER ( SNDESC ) ,	SNDOCR , 
			'Y' ,		'Y' ,			'Y' ,	'N' , 
			WKCURRDATE8 , WKCURRTIME6 ,	'POLARLINK' , 
			0 ,			0 ,				' ' 
		FROM ESN_PLNK WHERE SNTYPE = 'O' AND SNCUST = IN_ORDERNUM 
		; 
	END IF ; 
  
	 --====================================================================== 
	 -- ESN - Add substitutes as notes in ESN 
	 --====================================================================== 
	SELECT COUNT ( * ) INTO WK_NUM_SUBS 
	FROM PLINK_SUBSTITUTES 
	WHERE PLS_ORDER_NO = IN_ORDERNUM ; 
  
	 -- Only add subs to ESN if any substitutes found for this order 
	IF WK_NUM_SUBS > 0 THEN 
		CALL SP_SUBMIT_SUBS ( IN_ORDERNUM , IN_USER , IN_ORDERNUM ) ; 
	END IF ; 
  
	 --====================================================================== 
	 -- ECL - Order line items 
	 --====================================================================== 
	FOR IROW AS CSR_ITEMS CURSOR FOR 
		SELECT		* 
		FROM ECL_PLNK 
		JOIN ECH AS ECH ON HORD = IN_ORDERNUM 
		JOIN IIM AS IIM ON LPROD = IPROD 
		WHERE LORD = IN_ORDERNUM 
	DO 
		SET WK_LQORD = IROW . LQORD * IROW . IUMRC ; 
		SET WKWGTORD = IROW . IWGHT * WK_LQORD ; 
--		set wk_LNET = irow.LNET; 
		SET WK_LNET = IROW . LNET / IROW . IUMRC ; 
		SET WK_LLIST = IROW . LLIST / IROW . IUMRC ; 
		 -- Field CLLPAV in ECL - if net price <> list price then '1' else '0' 
		IF WK_LNET <> IROW . ILIST THEN 
			SET WK_CLLPAV = '1' ; 
		ELSE 
			SET WK_CLLPAV = '0' ; 
		END IF ; 
  
		 -- Set drop ship flag 
		IF WK_DROPSHIPCUST = 1 AND IROW . IDSALW = 1 THEN 
			SET WK_DROPSHIPITEM = 1 ; 
		ELSE 
			SET WK_DROPSHIPITEM = 0 ; 
		END IF ; 
  
		SET WK_SELLING_PRICE = IROW . LNET ; 
		SET WK_SELLING_QTY = IROW . LQORD ; 
  
  
		INSERT INTO ECL ( 
			LID ,	LORD ,	LLINE ,	LPROD ,	LCLAS ,	LPFDV ,	LQORD ,	LUM ,	LRDTE , 
			LSDTE ,	LLIST ,	LSTAT ,	LDESC ,	LCUST ,	LODTE ,	LCONT ,	LICOM ,	LSHIP , 
			CLORDT ,	CLORQT ,	CLOSQT ,	CLSLUM ,	CLENTM ,	CLENUS ,	CLWORD ,	CLOORW ,	CLWOSU , 
			CLRDVD ,	CLRDVT , LNET , LOVRP , LWHS , LSAL , 
  
			LDROP , LORPR , LBNET , LBLST , LCPNT , 
			LBO , LALOPR , LITEM , LIRES , LICFAC , CLTFAC , LQSALL , 
			CLTRNN , CLALRQ , CLBOCL , CLOCLS , CLBTYP , CLUTYP , 
			CLSTS1 , CLSTS2 , CLSTS3 , CLSTS4 , CLSTS5 , CLBBTL , 
  
			CLLPSC , CLNPSC , CLNPBL , CLNPTL , CLSPTL , CLPBTS , 
			CLPRBD , CLSHCU , CLPRUM , 
			CLQPRC , CLLPAV , 
			CLCRQD , CLORQD , CLCRDD , 
  
			CLFTYP , CLCTPU , CLDCKS , CLOLAQ , CLPFAC , 
			CLORTM , CLCRTM , CLCRDT , LDSTY 
  
		) VALUES ( 
			'CL' ,	IN_ORDERNUM ,	IROW . LLINE ,	IROW . LPROD ,	IROW . LCLAS ,	IROW . IPFDV ,	WK_LQORD ,	IROW . LUM ,	IROW . HSDTE , 
			IROW . HSDTE ,		WK_LLIST ,	'E' ,	IROW . IDESC ,	IROW . HCUST ,	WKCURRDATE8 ,	IROW . TAXC1 ,	IROW . IMCOM ,	IROW . HSHIP , 
			WKCURRDATE8 ,	WK_SELLING_QTY , WK_SELLING_QTY ,	IROW . LUM , WKCURRTIME6 ,	'POLARLINK' , WKWGTORD , WKWGTORD ,	WKWGTORD , 
			IROW . HSDTE , IROW . CHSSTM , WK_LNET , WK_LNET , WK_WHS , IROW . HSAL , 
  
			'N' , WK_LNET , WK_LNET , IROW . ILIST , 'N' , 
			4 , 50 , ' ' , 'AA' , WK_FACILITY , ' ' , WK_LQORD , 
			10 , 1 , IROW . CHBOCL , IROW . CHOCLS , WK_CMDFOT , WK_CMDFOT , 
			1 , 0 , 0 , 0 , 0 , WK_LNET , 
  
			IROW . CLLPSC , IROW . CLNPSC , WK_SELLING_PRICE , WK_SELLING_PRICE , WK_SELLING_PRICE , WK_LNET , 
			WKCURRDATE8 , IROW . HCUST , 'CS' , 
			WK_LQORD , WK_CLLPAV , 
			IROW . CHORQD , IROW . CHORQD , IROW . CHCRDD , 
  
			'0' , '0' , '0' , '0' , ' ' , 
			WKCURRTIME6 , WKCURRTIME6 , WKCURRTIME6 , WK_DROPSHIPITEM 
		) ; 
		 
		 -- If this is drop ship item, write record to EDS 
		IF WK_DROPSHIPITEM = 1 THEN 
			INSERT INTO EDS ( 
				DSRID , DSTY , DSCUST , DSSHTO , DSORD , 
				DSCOLN , DSITEM , DSOQTY , DSCUOM , DSORDT , 
				DSOLST , DSWHS , DSEDT , DSBO , DSLMDT ) 
			VALUES ( 
				'DS' , 1 , WK_HCUST , WK_HSHIP , IN_ORDERNUM ,	 
				IROW . LLINE ,	IROW . LPROD , WK_LQORD ,	IROW . LUM , IROW . HSDTE , 
				'E' , WK_WHS , WKCURRDATE8 , 4 , WKCURRDATE8 ) ; 
		END IF ;	 
		 
	END FOR ; 
  
  
	 --====================================================================== 
	 -- Pallets - Call the Pallets program (ORDE20 - RPG) to add pallet 
	 --           line(s) to ECL if customer gets them. 
	 --====================================================================== 
--	call sp_Util_QCMDEXC('DSPLIBL OUTPUT(*PRINT)' ); 
--	call sp_Util_QCMDEXC('DSPJOB OUTPUT(*PRINT) OPTION(*ALL)' ); 
--	call sp_Util_QCMDEXC('SNDMSG TOUSR(JVALANCE) MSG(''wk_next_ordno_alpha = ' || wk_next_ordno_alpha || ''')' ); 
	CALL SPE_PALLADD_R ( DIGITS ( IN_ORDERNUM ) ) ; 
  
	 --====================================================================== 
	 -- ECH_PLNK - Update PLINK order header with status = SBM (submitted) 
	 --====================================================================== 
	UPDATE ECH_PLNK 
	SET PLINK_STATUS = 'SUBM' , LX_ORDER_NUM = IN_ORDERNUM , 
	HEDTE = WKCURRDATE8 , CHENTM = WKCURRTIME6 , CHENUS = IN_USER 
	WHERE HORD = IN_ORDERNUM ; 
  
	 --====================================================================== 
	 -- PLINK_USER - Update email address as user's default if it was passed. 
	 --====================================================================== 
	IF TRIM ( IN_EMAIL_ADDR ) <> '' THEN 
		UPDATE PLINK_USER 
		SET PLU_EMAIL_ADDRESS = IN_EMAIL_ADDR 
		WHERE PLU_USER_ID = IN_USER ; 
	END IF ; 
  
	 --====================================================================== 
	 -- Call Order Tracking program - writes to EOTPF (Order Tracking file) 
	 --====================================================================== 
	CALL ORDD08 ( IN_ORDERNUM , WK_LINECOUNT , WKCURRDATE8 , WKCURRTIME6 , 
				'POLARLINK ' , 'POLARLINK ' , 'E' , 'POLARLINK ' ) ; 
  
	 --====================================================================== 
	 -- Return the LX order # to caller 
	 --====================================================================== 
	SET OUT_ORDERNO = IN_ORDERNUM ; 
  
	RETURN ; 
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_SUBMITORDER 
TO JVALANCE ; 
  
;
