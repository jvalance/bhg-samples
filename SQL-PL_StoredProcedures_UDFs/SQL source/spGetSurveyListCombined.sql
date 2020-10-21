drop procedure VGCUSTOM/spGetSurveyListCombined;
--@#
create procedure VGCUSTOM/spGetSurveyListCombined(
    inBatchNo integer
)
result sets 0
language sql

begin
    DECLARE wkLastNm CHAR(30);
    DECLARE wkFirstNm CHAR(20);
    DECLARE wkAddr1 CHAR(35);
    DECLARE wkAddr2 CHAR(35);
    DECLARE wkAddr3 CHAR(35);
    DECLARE wkZIP CHAR(9);
    DECLARE wkType CHAR(2);
    DECLARE wkCode CHAR(5);
    DECLARE wkDate DEC(8,0);
    DECLARE wkCompBy CHAR(15);
    DECLARE wkAcct DEC(7,0);
    DECLARE wkCSRid CHAR(5);
    
    DECLARE wkAddrConcat CHAR(200) default '';
    DECLARE prvAcct  DEC(7,0) default 0;

    DECLARE at_end INT DEFAULT 0;
    DECLARE not_found CONDITION FOR '02000';
    DECLARE c1 CURSOR FOR
      SELECT   'SO', UMACT as ACCTNO, SMSCD, SMCPD as CDATE, SMCPB as SMCPB,
               UMLNM, UMFNM, UMAD1, UMAD2, UMAD3, UMZIP, SWCRW
      FROM     SRVYSOSAMP
      UNION
      SELECT   'CM', UMACT as ACCTNO, CMTYPE, CMCLD as CDATE, ' '  as SMCPB,
               UMLNM, UMFNM, UMAD1, UMAD2, UMAD3, UMZIP, SWCRW 
      FROM     SRVYCMSAMP
      ORDER BY ACCTNO, CDATE desc;

    DECLARE CONTINUE HANDLER FOR not found SET at_end = 1;

--================================================================
    OPEN c1;
    FETCH NEXT FROM c1
   INTO  wkType, wkAcct, wkCode, wkDate, wkCompBy, wkLastNm, wkFirstNm,
          wkAddr1, wkAddr2, wkAddr3, wkZip, wkCSRid;

    WHILE at_end <> 1 DO
        -- If new account#, write a record to file
        if (wkAcct <> prvAcct) then
            set prvAcct = wkAcct;
            INSERT INTO SRVYDETAIL
               (SL_Contact_Type, SL_AcctNo, SL_Contact_Code, SL_Contact_Date,
               SL_LastName, SL_FirstName, SL_Address1, SL_Address2, SL_Address3,
               SL_ZipCode, SL_BatchNo, SL_RunDate, SL_ManualEntry, SL_Completed_By,SL_CSRid)
            VALUES
               (wkType, wkAcct, wkCode, wkDate,
                wkLastNm, wkFirstNm, wkAddr1, wkAddr2, wkAddr3,
                wkZip, inBatchNo, current date, 'N', wkCompBy,wkCSRid);
        end if;

        FETCH NEXT FROM c1
        INTO  wkType, wkAcct, wkCode, wkDate, wkCompBy, wkLastNm, wkFirstNm,
              wkAddr1, wkAddr2, wkAddr3, wkZip, wkCSRid;
    END WHILE;

    CLOSE c1;

--================================================================
    -- Update the composite code for phone contacts
    UPDATE SRVYDETAIL AS s1
    SET SL_COMPOSITE_CODE =
       (SELECT s2.SL_Contact_Type || '-' || trim(s2.SL_Contact_Code) || '-' ||
               s2.SL_Contact_Date || '-' || s2.SL_ACCTNO ||
               CASE
                  WHEN (tp.InitiatedBy IS NOT NULL AND tp.InitiatedBy = 'CUST')
                    THEN '-C'
                  WHEN (tp.InitiatedBy IS NOT NULL AND tp.InitiatedBy = 'VGS ')
                    THEN '-V'
                  ELSE ''
               END ||
               CASE
                  WHEN (length(trim(SL_CSRid)) = 0)
                    THEN ''
                  WHEN (length(trim(SL_CSRid)) <= 3)
                    THEN '-CC' || trim(SL_CSRid)
                  ELSE ''
                END
        FROM SRVYDETAIL s2
        LEFT JOIN SRVYCMTYPS tp ON s2.SL_Contact_Code = tp.SURVEYCMTYPE
        WHERE s1.SL_SEQUENCE = s2.SL_SEQUENCE)
    WHERE s1.SL_Contact_Type = 'CM'
    AND   s1.SL_BATCHNO = inBatchNo;

    -- Update the composite code for service contacts
    UPDATE SRVYDETAIL AS s1
    SET SL_COMPOSITE_CODE =
       (SELECT s2.SL_Contact_Type || '-' || trim(s2.SL_Contact_Code) || '-' ||
               s2.SL_Contact_Date || '-' || s2.SL_ACCTNO ||
               CASE
                  WHEN (tp.InitiatedBy IS NOT NULL AND tp.InitiatedBy = 'CUST')
                    THEN '-C'
                  WHEN (tp.InitiatedBy IS NOT NULL AND tp.InitiatedBy = 'VGS ')
                    THEN '-V'
                  ELSE ''
               END ||
               CASE
                  WHEN (length(trim(SL_CSRid)) = 0)
                    THEN ''
                  WHEN (length(trim(SL_CSRid)) <= 3)
                    THEN '-CC' || trim(SL_CSRid)
                  ELSE ''
               END ||
               CASE
                  WHEN (length(trim(SL_Completed_By)) = 0)
                    THEN ''
                  WHEN (length(trim(SL_Completed_By)) <= 3)
                    THEN '-SR' || trim(SL_Completed_By)
                  ELSE ''
                END
        FROM SRVYDETAIL s2
        LEFT JOIN SRVYSOTYPS tp ON s2.SL_Contact_Code = tp.SURVEYSOTYPE
        WHERE s1.SL_SEQUENCE = s2.SL_SEQUENCE)
    WHERE s1.SL_Contact_Type = 'SO'
    AND   s1.SL_BATCHNO = inBatchNo;

end
