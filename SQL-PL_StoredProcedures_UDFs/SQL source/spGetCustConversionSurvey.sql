drop procedure VGCUSTOM/spGetCustConversionSurvey;
--@#
create procedure VGCUSTOM/spGetCustConversionSurvey()

result sets 0
language sql

begin
    DECLARE msg VARCHAR(512);
    declare wkBatchId integer default 0;
    declare wkCount integer default 0;

    DECLARE wkUMLNM CHAR(30);
    DECLARE wkUMFNM CHAR(20);
    DECLARE wkUMAD1 CHAR(35);
    DECLARE wkUMAD2 CHAR(35);
    DECLARE wkUMAD3 CHAR(35);
    DECLARE wkUMZIP CHAR(9);
    DECLARE wkTURNONDATE DEC(8,0);
    DECLARE wkUMACT DEC(7,0);
    DECLARE wkPRM CHAR(15);
    DECLARE wkPRM_ADDR VARCHAR(100);
    DECLARE wkConvType CHAR(1);

    declare wkISODate char(10);
    declare wkTwoWeeksAgo char(8);
    declare wkOneYearAgoChar char(8);
    declare wkOneYearAgoISO date;

    declare wkLastRunDateChar char(8);
    declare wkLastRunDate date;

    DECLARE wkAddrConcat CHAR(200) default '';
    DECLARE prvAddrConcat CHAR(200) default '';

    DECLARE at_end INT DEFAULT 0;
    DECLARE not_found CONDITION FOR '02000';

--    declare rs RESULT_SET_LOCATOR VARYING;
--    declare c1 cursor;

--================================================================
    DECLARE c1 CURSOR FOR
        SELECT      UMACT as "Account Number",
                    UMLNM as "Last Name",
                    UMFNM as "First Name",
                    UMAD1 as "Address Line 1",
                    UMAD2 as "Address Line 2",
                    UMAD3 as "Address Line 3",
                    UMZIP as "Zip Code",
                    SLSODT as "Turn On Date",
                    UMPRM as "Premise No.",
                    SLSCON as "Conversion Type",
                      trim(pr.UPSAD) || ', ' ||
                      trim(pr.UPCTC)
                    as "Service Address"

        FROM        SLSAPP as sa

        JOIN        UACT as ac
         ON         UMPRM = SLSBK$
         AND        UMSTS = 'AC' -- Active account at premise

        JOIN        UPRM as pr
         on         pr.UPPRM = SLSBK$

        LEFT JOIN   UCSR as uc
         ON         UMPRM = UCPRM
         AND        (uc.UCACT is null or uc.UCACT = 0)

        -- Don't send surveys to any excluded accounts
        LEFT JOIN   SRVYEXCLAC as excl
         ON         UMACT = SrvyExclAcctNo

        LEFT JOIN   QUAIRE as q1
         ON         UMACT = q1.QSTCST
         AND        q1.QSTMAL =
                        (SELECT MAX(QSTMAL)
                         FROM QUAIRE as q2
                         WHERE q1.QSTCST = q2.QSTCST)

        LEFT JOIN   SRVYDETAIL as sv
         ON         UMACT = sv.SL_ACCTNO
         AND        sv.SL_RUNDATE =
                        (SELECT MAX(SL_RUNDATE)
                         FROM SRVYDETAIL as sv2
                         WHERE sv.SL_ACCTNO = sv2.SL_ACCTNO)

        LEFT JOIN   SRVYCNVDET as cnv
         ON         UMACT = cnv.CD_ACCTNO
         AND        cnv.CD_RUNDATE =
                        (SELECT MAX(CD_RUNDATE)
                         FROM SRVYCNVDET cnv2
                         WHERE cnv.CD_ACCTNO = cnv2.CD_ACCTNO)

        WHERE       -- Existing construction only
                    SLSNCN = 'E'
                    -- Turned on since the last survey mailing batch run
         AND        SLSODT >= wkLastRunDateChar
                    -- exclude survey sent in past year (old quarterly satisfaction survey system)
         AND        (q1.QSTMAL is null or q1.QSTMAL < wkOneYearAgoChar)
                    -- exclude survey sent in past year (new quarterly satisfaction survey system)
         AND        (sv.SL_RUNDATE is null or sv.SL_RUNDATE < wkOneYearAgoISO)
                    -- exclude survey sent in past year (previous conversion surveys)
         AND        (cnv.CD_RUNDATE is null or cnv.CD_RUNDATE < wkOneYearAgoISO)
                    -- Don't send surveys to any excluded accounts
         AND        excl.SrvyExclAcctNo is null
                    -- exclude interruptibles
         AND        UMTYP NOT IN ('T', 'I')
                    -- Residential only
         AND        UCSCH = 'R '

        ORDER BY UMLNM, UMFNM, UMAD1, UMAD2, UMAD3, UMZIP, SLSODT DESC
        ;

    DECLARE CONTINUE HANDLER FOR not found SET at_end = 1;

--================================================================

--    set wkISODate = char((current date - 14 days),ISO);
--    set wkTwoWeeksAgo = replace(wkISODate,'-','');

    -- Changed to use last run date as start, instead of two weeks ago.
    select max(cb_rundate) into wkLastRunDate from SrvyCnvBat;
    set wkLastRunDateChar = replace(char(wkLastRunDate,ISO),'-','');

--    set msg = 'wkTwoWeeksAgo = ' || wkTwoWeeksAgo ||
--            '; wkLastRunDateChar = ' || wkLastRunDateChar;
--    call spsndmsg('JVALANCE', msg);

    set wkOneYearAgoISO = current date - 1 year;
    set wkOneYearAgoChar = replace(char(wkOneYearAgoISO,ISO),'-','');

--================================================================

    -- Create the batch header record
    INSERT INTO SRVYCNVBAT (
    	CB_RUNDATE,
	    CB_RUNTIME,
    	CB_STATUS,
	    CB_STARTDATE,
    	CB_ENDDATE)
	VALUES (
	    CURRENT DATE,
	    CURRENT TIME,
	    'RUNNING',
	    CURRENT DATE - 14 DAYS,
	    current date);
	
	-- Retrieve batch number automatically generated by DB2 identity column
	SET wkBatchId = IDENTITY_VAL_LOCAL();
    set AT_END = 0; -- insert (above) may set this to 1
	
    ---------------------------------
--    call spGetCustConversionList();
--    associate result set locator (rs) with procedure spGetCustConversionList;
--    allocate c1 cursor for result set rs;

--================================================================

    OPEN C1;

    FETCH NEXT FROM c1
    INTO  wkUMACT, wkUMLNM, wkUMFNM, wkUMAD1, wkUMAD2,
          wkUMAD3, wkUMZIP, wkTurnOnDate, wkPRM, wkConvType, wkPRM_ADDR;

    WHILE at_end <> 1 DO
        SET wkAddrConcat = wkUMLNM || wkUMFNM || wkUMAD1
                        || wkUMAD2 || wkUMAD3 || wkUMZIP;
        -- If new address, write a record to work file
        IF (wkAddrConcat <> prvAddrConcat) THEN
            set prvAddrConcat = wkAddrConcat;

            INSERT INTO SRVYCNVDET(
            	CD_BATCHNO,
            	CD_RUNDATE,
        	    CD_RUNTIME,
            	CD_TURNON_DATE,
            	CD_ACCTNO,
            	CD_LASTNAME,
	            CD_FIRSTNAME,
            	CD_ADDRESS1,
        	    CD_ADDRESS2,
    	        CD_ADDRESS3,
	            CD_ZIPCODE,
            	CD_PREMISE,
            	CD_PREM_ADDR,
	            CD_COMPOSITE_CODE)
            VALUES
               (wkBatchId,
                current date,
                current time,
                wkTurnOnDate,
                wkUMACT,
                wkUMLNM,
                wkUMFNM,
                wkUMAD1,
                wkUMAD2,
                wkUMAD3,
                wkUMZIP,
                wkPRM,
                wkPRM_ADDR,
                wkConvType
                || '-' || trim(char(wkUMACT))
                || '-' || trim(char(wkTurnOnDate))  -- Composite code
            );
        END IF;

        FETCH NEXT FROM c1
        INTO  wkUMACT, wkUMLNM, wkUMFNM, wkUMAD1, wkUMAD2,
              wkUMAD3, wkUMZIP, wkTurnOnDate, wkPRM, wkConvType, wkPRM_ADDR;
    END WHILE;

    CLOSE c1;

    -- Update batch header with record count and complete status.
    update SrvyCnvBat set
        cb_Count = (select count(*) from SrvyCnvDet where CD_BATCHNO = wkBatchId),
        cb_Status = 'FINISH'
    where cb_BatchNo = wkBatchId;

end

