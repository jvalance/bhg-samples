drop procedure VGCUSTOM/spGetSurveySOList;
--@#
create procedure VGCUSTOM/spGetSurveySOList(
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
    DECLARE wkSMSCD CHAR(5);
    DECLARE wkSMCPD DEC(8,0);
    DECLARE wkUMACT DEC(7,0);
    DECLARE wkSMCPB CHAR(15);
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
        SELECT  UMACT, so.SMSCD, so.SMCPD, so.SMCPB,UMLNM, UMFNM, UMAD1, UMAD2, UMAD3, UMZIP, SWCRW

        FROM   SCMS   so

        JOIN  SRVYSOTYPS sot
         ON   so.SMSCD = SurveySoType

        JOIN  UACT ac
         ON   so.SMACT = UMACT
         AND  ac.UMSTS = 'AC'

        JOIN  UPRM pr
         ON   UMPRM = UPPRM

        JOIN SCRW cr
        ON so.SMEBU = SWNAM 

        -- exclude conversion in specified period
        LEFT JOIN SLSAPP
        ON   UMPRM = SLSBK$
        AND  SLSODT between  inFromDate and inToDate
        AND  SLSNCN = 'E'

        LEFT JOIN UCSR as uc
         ON   UMPRM = UCPRM
         AND  (uc.UCACT is null or uc.UCACT = 0)

        -- Don't send surveys to any excluded accounts
        LEFT JOIN SRVYEXCLAC excl
         ON so.SMACT = SrvyExclAcctNo

        LEFT JOIN QUAIRE q1
         ON   so.SMACT = q1.QSTCST
         AND  q1.QSTMAL =
                (SELECT MAX(QSTMAL)
                 FROM QUAIRE q2
                 WHERE q1.QSTCST = q2.QSTCST)

        LEFT JOIN SRVYDETAIL sv
         ON  so.SMACT = sv.SL_ACCTNO
         AND sv.SL_RUNDATE =
                (SELECT MAX(SL_RUNDATE)
                 FROM SRVYDETAIL sv2
                 WHERE sv.SL_ACCTNO = sv2.SL_ACCTNO)

        -- ensure not counting same acct/sotype more than once in the past year
        LEFT JOIN   SCMS   so2
         ON   so.SMACT = so2.SMACT
         AND  so.SMSCD = so2.SMSCD
         -- prior record for acct/sotype in past year?
         AND  so2.SMCPD between inPrevMailDate and so.SMCPD
         -- not same exact service order record
           AND  so.SMSO# <> so2.SMSO#
      

        WHERE
         -- transaction closed in past month
         so.SMCPD between inFromDate and inToDate
         -- only completed service orders
           AND so.SMCLO = 'Y'
         -- Don't send surveys to any excluded accounts
         AND  excl.SrvyExclAcctNo is null
         -- ensure not counting same acct/cmtype more than once in the past year
         AND  so2.SMSCD is null
         -- exclude employees
         AND  so.SMRQB <> 'V '
         -- exclude survey sent in past year
         AND  (q1.QSTMAL is null or q1.QSTMAL < inPrevMailDate)
         -- exclude survey sent in past year
         AND  (sv.SL_RUNDATE is null or sv.SL_RUNDATE < wkPrevMailDateISO)
         -- exclude employees and interruptibles
         AND  UCSCH not in ('RE', '  ')
         AND  UMTYP NOT IN ('T', 'I')
         -- exclude conversions in specified period
         AND  SLSBK$ is null

        ORDER BY UMLNM, UMFNM, UMAD1, UMAD2, UMAD3, UMZIP, so.SMCPD DESC;

    DECLARE CONTINUE HANDLER FOR not found SET at_end = 1;

    set wkIsoDateChar = substr(inPrevMailDate,1,4) || '-' ||
                        substr(inPrevMailDate,5,2) || '-' ||
                        substr(inPrevMailDate,7,2);

    set wkPrevMailDateISO = date(wkIsoDateChar);

--================================================================
    -- Clear the work file first.
    DELETE FROM SRVYSOWORK;
    set AT_END = 0; -- delete (above) will set this to 1

    set wkISODate = char((current date - 6 month),ISO);
    set wkSixMonthsAgo = replace(wkISODate,'-','');

    OPEN c1;
    FETCH NEXT FROM c1
    INTO  wkUMACT, wkSMSCD, wkSMCPD,wkSMCPB,wkUMLNM, wkUMFNM,
          wkUMAD1, wkUMAD2, wkUMAD3, wkUMZIP,wkSWCRW;

    WHILE at_end <> 1 DO
        SET wkAddrConcat = wkUMLNM || wkUMFNM || wkUMAD1
                        || wkUMAD2 || wkUMAD3 || wkUMZIP;
        -- If new address, write a record to work file
        if (wkAddrConcat <> prvAddrConcat) then
            set prvAddrConcat = wkAddrConcat;
            INSERT INTO SRVYSOWORK
               (UMACT, SMSCD, SMCPD,SMCPB,UMLNM, UMFNM, UMAD1, UMAD2, UMAD3, UMZIP, SWCRW)
            VALUES
               (wkUMACT, wkSMSCD, wkSMCPD,wkSMCPB, wkUMLNM, wkUMFNM,
                wkUMAD1, wkUMAD2, wkUMAD3, wkUMZIP,wkSWCRW);
        end if;

        FETCH NEXT FROM c1
        INTO  wkUMACT, wkSMSCD, wkSMCPD,wkSMCPB, wkUMLNM, wkUMFNM,
              wkUMAD1, wkUMAD2, wkUMAD3, wkUMZIP,wkSWCRW;
    END WHILE;

    CLOSE c1;

end

