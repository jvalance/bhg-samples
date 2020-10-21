drop procedure VGCUSTOM/spGetCustConversionList;
--@#
create procedure VGCUSTOM/spGetCustConversionList()

result sets 1
language sql

begin
    declare wkISODate char(10);
    DEcLARE wkTwoWeeksAgo char(8);
    declare wkOneYearAgoChar char(8);
    declare wkOneYearAgoISO date;

    DECLARE c1 CURSOR WITH RETURN FOR
        SELECT      UMACT as "Account Number",
                    UMLNM as "Last Name",
                    UMFNM as "First Name",
                    UMAD1 as "Address Line 1",
                    UMAD2 as "Address Line 2",
                    UMAD3 as "Address Line 3",
                    UMZIP as "Zip Code",
                    SLSODT as "Turn On Date",
                    UMPRM as "Premise No.",
                    SLSCON as "Conversion Type"

        FROM        SLSAPP as sa

        JOIN        UACT as ac
         ON         UMPRM = SLSBK$
         AND        UMSTS = 'AC' -- Active account at premise

        LEFT JOIN   UCSR as uc
         ON         UMPRM = UCPRM
         AND        (uc.UCACT is null or uc.UCACT = 0)

        -- Don't send surveys to any excluded accounts
        LEFT JOIN   SRVYEXCLAC excl
         ON         UMACT = SrvyExclAcctNo

        LEFT JOIN   QUAIRE q1
         ON         UMACT = q1.QSTCST
         AND        q1.QSTMAL =
                        (SELECT MAX(QSTMAL)
                         FROM QUAIRE q2
                         WHERE q1.QSTCST = q2.QSTCST)

        LEFT JOIN   SRVYDETAIL sv
         ON         UMACT = sv.SL_ACCTNO
         AND        sv.SL_RUNDATE =
                        (SELECT MAX(SL_RUNDATE)
                         FROM SRVYDETAIL sv2
                         WHERE sv.SL_ACCTNO = sv2.SL_ACCTNO)

        WHERE       -- Existing construction only
                    SLSNCN = 'E'
                    -- Turned on in the past two weeks
         AND        SLSODT >= wkTwoWeeksAgo
                    -- exclude survey sent in past year (old system)
         AND        (q1.QSTMAL is null or q1.QSTMAL < wkOneYearAgoChar)
                    -- exclude survey sent in past year (new system)
         AND        (sv.SL_RUNDATE is null or sv.SL_RUNDATE < wkOneYearAgoISO)
                    -- Don't send surveys to any excluded accounts
         AND        excl.SrvyExclAcctNo is null
                    -- exclude interruptibles
         AND        UMTYP NOT IN ('T', 'I')
                    -- Residential only
         AND        UCSCH = 'R '
        ;

--================================================================

    set wkISODate = char((current date - 14 days),ISO);
    set wkTwoWeeksAgo = replace(wkISODate,'-','');

    set wkOneYearAgoISO = current date - 1 year;
    set wkOneYearAgoChar = replace(char(wkOneYearAgoISO,ISO),'-','');

    OPEN C1;

end

