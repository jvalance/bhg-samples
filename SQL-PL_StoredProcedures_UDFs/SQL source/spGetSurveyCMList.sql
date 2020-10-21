drop procedure VGCUSTOM/spGetSurveyCMList;
--@#
create procedure VGCUSTOM/spGetSurveyCMList(
    inFromDate char (8),
    inToDate char (8),
    inPrevMailDate char (8)
)
result sets 0
language sql

begin
    DECLARE wkUMLNM CHAR(30);
    DECLARE wkUMFNM CHAR(20);
    DECLARE wkUMAD1 CHAR(35);
    DECLARE wkUMAD2 CHAR(35);
    DECLARE wkUMAD3 CHAR(35);
    DECLARE wkUMZIP CHAR(9);
    DECLARE wkCMCTC CHAR(5);
    DECLARE wkCMCLD DEC(8,0);
    DECLARE wkUMACT DEC(7,0);
    DECLARE wkSWCRW CHAR(5);
    declare wkIsoDateChar char(10);
    declare wkPrevMailDateISO date;

    DEcLARE wkSixMonthsAgo char(8);
    declare wkISODate char(10);

    DECLARE wkAddrConcat CHAR(200) default '';
    DECLARE prvAddrConcat CHAR(200) default '';

    DECLARE at_end INT DEFAULT 0;
    DECLARE not_found CONDITION FOR '02000';

    DECLARE c1 CURSOR FOR
        SELECT      UMACT, cm.CMCTC, cm.CMCRD,
                    UMLNM, UMFNM, UMAD1, UMAD2, UMAD3, UMZIP, SWCRW

        FROM  CCCM as cm

        JOIN  SRVYCMTYPS sct
         ON   cm.CMCTC = SurveyCmType

        JOIN UACT ac
         ON  cm.CMACT = UMACT
         AND UMSTS = 'AC'

        JOIN UPRM pr
         ON  UMPRM = UPPRM

       JOIN SCRW cr
        ON cm.CMCRB = SWNAM 

        -- exclude conversion in specified period
        LEFT JOIN SLSAPP
        ON   UMPRM = SLSBK$
        AND  SLSODT between  inFromDate and inToDate
        AND  SLSNCN = 'E'

        LEFT JOIN   UCSR as uc
         ON  UMPRM = UCPRM
         AND (uc.UCACT is null or uc.UCACT = 0)

        -- Don't send surveys to any excluded accounts
        LEFT JOIN SRVYEXCLAC excl
         ON  cm.CMACT = SrvyExclAcctNo

        LEFT JOIN QUAIRE q1
         ON  cm.CMACT = q1.QSTCST
         AND q1.QSTMAL =
                (SELECT MAX(QSTMAL)
                 FROM QUAIRE q2
                 WHERE q1.QSTCST = q2.QSTCST)

        LEFT JOIN SRVYDETAIL sv
         ON  cm.CMACT = sv.SL_ACCTNO
         AND sv.SL_RUNDATE =
                (SELECT MAX(SL_RUNDATE)
                 FROM SRVYDETAIL sv2
                 WHERE sv.SL_ACCTNO = sv2.SL_ACCTNO)

        -- ensure not counting same acct/cmtype more than once in the past year
        LEFT JOIN CCCM as cm2
         ON   cm.CMACT = cm2.CMACT
         AND  cm.CMCTC = cm2.CMCTC
         -- prior record for acct/cmtype in past year?
         AND  cm2.CMCLD between inPrevMailDate and cm.CMCLD
         -- not same exact CCCM record
         AND  cm.CMCT# <> cm2.CMCT#

        WHERE
         -- transaction created in past month
         cm.CMCRD between inFromDate and inToDate
         -- ensure not counting same acct/cmtype more than once in the past year
         AND  cm2.CMACT is null
         -- Don't send surveys to any excluded accounts
         AND  excl.SrvyExclAcctNo is null
         -- exclude survey sent in past year (old system)
         AND  (q1.QSTMAL is null or q1.QSTMAL < inPrevMailDate)
         -- exclude survey sent in past year (new system)
         AND  (sv.SL_RUNDATE is null or sv.SL_RUNDATE < wkPrevMailDateISO)
         -- exclude employees and interruptibles
         AND  UCSCH not in ('RE', '  ')
         AND  UMTYP NOT IN ('T', 'I')
         -- exclude conversions in specified period
         AND  SLSBK$ is null

        ORDER BY UMLNM, UMFNM, UMAD1, UMAD2, UMAD3, UMZIP, cm.CMCRD DESC;

    DECLARE CONTINUE HANDLER FOR not found SET at_end = 1;

    set wkIsoDateChar = substr(inPrevMailDate,1,4) || '-' ||
                        substr(inPrevMailDate,5,2) || '-' ||
                        substr(inPrevMailDate,7,2);
    set wkPrevMailDateISO = date(wkIsoDateChar);

--================================================================
    DELETE FROM SRVYCMWORK;
    set AT_END = 0; -- delete (above) will set this to 1

    set wkISODate = char((current date - 6 month),ISO);
    set wkSixMonthsAgo = replace(wkISODate,'-','');

    OPEN c1;
    FETCH NEXT FROM c1
    INTO  wkUMACT, wkCMCTC, wkCMCLD, wkUMLNM, wkUMFNM,
          wkUMAD1, wkUMAD2, wkUMAD3, wkUMZIP,wkSWCRW;

    WHILE at_end <> 1 DO
        SET wkAddrConcat = wkUMLNM || wkUMFNM || wkUMAD1
                        || wkUMAD2 || wkUMAD3 || wkUMZIP;
        -- If new address, write a record to work file
        if (wkAddrConcat <> prvAddrConcat) then
            set prvAddrConcat = wkAddrConcat;
            INSERT INTO SRVYCMWORK
               (UMACT, CMTYPE, CMCLD, UMLNM, UMFNM,
                UMAD1, UMAD2, UMAD3, UMZIP,SWCRW )
            VALUES
               (wkUMACT, wkCMCTC, wkCMCLD, wkUMLNM, wkUMFNM,
                wkUMAD1, wkUMAD2, wkUMAD3, wkUMZIP,wkSWCRW);
        end if;

        FETCH NEXT FROM c1
        INTO  wkUMACT, wkCMCTC, wkCMCLD, wkUMLNM, wkUMFNM,
              wkUMAD1, wkUMAD2, wkUMAD3, wkUMZIP, wkSWCRW;
    END WHILE;

    CLOSE c1;

end

