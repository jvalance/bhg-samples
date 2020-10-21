drop procedure VGCUSTOM/spGetSurveyCMSamp;
--@#
create procedure VGCUSTOM/spGetSurveyCMSamp(
    inSampleCount dec(5,0),
    inInitiator char(4)
)
result sets 0
language sql

begin
    DECLARE wkUniqueCount dec(5,0);
    DECLARE wkInterval dec(5,0);
    DECLARE qcmd VARCHAR(512);

    DECLARE wkUMLNM CHAR(30);
    DECLARE wkUMFNM CHAR(20);
    DECLARE wkUMAD1 CHAR(35);
    DECLARE wkUMAD2 CHAR(35);
    DECLARE wkUMAD3 CHAR(35);
    DECLARE wkUMZIP CHAR(9);
    DECLARE wkCMTYPE CHAR(5);
    DECLARE wkCMCLD DEC(8,0);
    DECLARE wkUMACT DEC(7,0);
    DECLARE wkSWCRW CHAR(5);

    DECLARE at_end INT DEFAULT 0;
    DECLARE not_found CONDITION FOR '02000';
    DECLARE c1 DYNAMIC SCROLL CURSOR FOR
        SELECT UMACT, wk.CMTYPE, CMCLD, UMLNM, UMFNM,
               UMAD1, UMAD2, UMAD3, UMZIP, SWCRW
        FROM   SRVYCMWORK wk
        JOIN   SRVYCMTYPS tp
          on   wk.CMTYPE = tp.SurveyCMType
        WHERE  tp.InitiatedBy = inInitiator
        ORDER BY UMLNM, UMFNM, UMAD1, UMAD2, UMAD3, UMZIP;
    DECLARE CONTINUE HANDLER FOR not_found SET at_end = 1;

--================================================================
    -- Parameter 'inSampleCount' tells us how many records
    -- should be selected at random from the work file.
    -- Variable 'wkUniqueCount' is the count of records in the work file.
    -- Variable 'wkInterval' is the number of records to skip while
    -- reading the work file sequentially in order to obtain a
    -- random sampling. The value for 'wkInterval' will be derived using:
    --      wkInterval = wkUniqueCount / inSampleCount

    SELECT COUNT(*)
    INTO   wkUniqueCount
    FROM   SRVYCMWORK wk
    JOIN   SRVYCMTYPS tp
      on   wk.CMTYPE = tp.SurveyCMType
    WHERE  tp.InitiatedBy = inInitiator;

    SET wkInterval = decimal(round(wkUniqueCount / inSampleCount,0),5,0);
    if wkInterval < 1 then SET wkInterval = 1; end if;

--    set qcmd = 'CM/' || inInitiator || ': wkUniqueCount = ' || char(wkUniqueCount) ||
--               '; inSampleCount = ' || char(inSampleCount) ||
--               '; wkInterval = ' || char(wkInterval);
--    call spsndmsg('JVALANCE', qcmd);

    set at_end = 0;
    OPEN c1;

    FETCH RELATIVE wkInterval FROM c1
    INTO   wkUMACT, wkCMTYPE, wkCMCLD, wkUMLNM, wkUMFNM,
           wkUMAD1, wkUMAD2, wkUMAD3, wkUMZIP, wkSWCRW;

    WHILE at_end = 0 DO
        INSERT INTO SRVYCMSAMP
               (UMACT, CMTYPE, CMCLD, UMLNM, UMFNM,
                UMAD1, UMAD2, UMAD3, UMZIP,SWCRW)
        VALUES (wkUMACT, wkCMTYPE, wkCMCLD, wkUMLNM, wkUMFNM,
                wkUMAD1, wkUMAD2, wkUMAD3, wkUMZIP, wkSWCRW);

        FETCH RELATIVE wkInterval FROM c1
        INTO   wkUMACT, wkCMTYPE, wkCMCLD, wkUMLNM, wkUMFNM,
               wkUMAD1, wkUMAD2, wkUMAD3, wkUMZIP, wkSWCRW;
    END WHILE;

    CLOSE c1;

end
