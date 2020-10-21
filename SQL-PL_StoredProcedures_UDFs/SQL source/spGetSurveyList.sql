drop procedure VGCUSTOM/spGetSurveyList;
--@#
create procedure VGCUSTOM/spGetSurveyList
(
    inAnticipatedResponsesVGS dec(5,0),
    inAnticipatedResponsesCUST dec(5,0),
    inEstResponseRate dec(2,2),
    inDropsPerQuarter dec(3,0)
)
result sets 0
language sql

begin
    declare wkSampleCountVGS dec(5,0);
    declare wkSampleCountCUST dec(5,0);
    declare wkmsg varchar(500);
    declare wkISODate char(10);
    declare wkStartDateISO date;
    declare wkStartDate char(8);
    declare wkEndDateISO date;
    declare wkEndDate char(8);
    declare wkOneYearAgo char(8);
    DECLARE qcmd VARCHAR(512);
    declare wkBatchId integer default 0;
    declare wkSOCount integer default 0;
    declare wkCMCount integer default 0;
    declare wkVGSCount integer default 0;
    declare wkCUSTCount integer default 0;
    declare wkCount integer default 0;

    set wkStartDateISO = current date - 1 month;
    set wkISODate = char((wkStartDateISO),ISO);
    set wkStartDate = replace(wkISODate,'-','');

    set wkEndDateISO = current date;
    set wkISODate = char((wkEndDateISO),ISO);
    set wkEndDate = replace(wkISODate,'-','');

    set wkISODate = char((current date - 1 year),ISO);
    set wkOneYearAgo = replace(wkISODate,'-','');

--    set qcmd = 'Start = ' || wkStartDate ||
--               '; End = ' || wkEndDate ||
--               '; Year Ago = ' || wkOneYearAgo;
--    call spsndmsg('JVALANCE', qcmd);


    set wkSampleCountVGS = ( inAnticipatedResponsesVGS
                           / inEstResponseRate
                           / inDropsPerQuarter
                           / 2); -- divide by 2 to get even split between CM & SO.

    set wkSampleCountCUST = ( inAnticipatedResponsesCUST
                            / inEstResponseRate
                            / inDropsPerQuarter
                            / 2); -- divide by 2 to get even split between CM & SO.


--    set wkmsg = 'wkSampleCountVGS = ' || char(wkSampleCountVGS);
--    call spsndmsg('JVALANCE', wkmsg);
--    set wkmsg = 'wkSampleCountCust = ' || char(wkSampleCountCust);
--    call spsndmsg('JVALANCE', wkmsg);

    -- Create the batch header record
    INSERT INTO SRVYBATCH (
    	sb_RunDate,
	    sb_RunTime,
    	sb_Status,
	    sb_Est_Responses_VGS,
    	sb_Est_Responses_Cust,
	    sb_Est_Response_Rate,
    	sb_Drops_Per_Quarter,
	    sb_StartDate,
    	sb_EndDate)
	VALUES (
	    CURRENT DATE,
	    CURRENT TIME,
	    'NEW',
	    inAnticipatedResponsesVGS,
	    inAnticipatedResponsesCUST,
	    inEstResponseRate,
	    inDropsPerQuarter,
	    wkStartDateISO,
	    wkEndDateISO);
	
	-- Retrieve batch number automatically generated by DB2 identity column
	SET wkBatchId = IDENTITY_VAL_LOCAL();
	
    ---------------------------------

    -- Get a list of accounts that qualify for survey based on service
    -- order contacts in the past month (store in table SRVYSOWORK).
    CALL spGetSurveySOList(wkStartDate, wkEndDate, wkOneYearAgo);
    -- Update status on batch header.
    UPDATE SRVYBATCH set sb_Status = 'SOWORK'
        WHERE sb_BatchNo = wkBatchId;

    -- Extract a random sample of the service contacts in SRVYSOWORK.
    -- Store the sample in table SRVYSOSAMP.
    DELETE FROM SRVYSOSAMP; -- Clear the SO sample output file first
    -- Get the random sample of SO contacts where VT Gas initiated the contact.
    CALL spGetSurveySOSamp(wkSampleCountVGS,  'VGS ');
    -- Get the random sample of SO contacts where customer initiated the contact.
    CALL spGetSurveySOSamp(wkSampleCountCUST, 'CUST');

    -- Update status on batch header.
    UPDATE SRVYBATCH set sb_Status = 'SOSAMP'
        WHERE sb_BatchNo = wkBatchId;

    ---------------------------------

    -- Get a list of accounts that qualify for survey based on phone
    -- contacts in the past month (store in table SRVYCMWORK).
    CALL spGetSurveyCMList(wkStartDate, wkEndDate, wkOneYearAgo);

    -- Update status on batch header.
    UPDATE SRVYBATCH set sb_Status = 'CMWORK'
        WHERE sb_BatchNo = wkBatchId;

    -- Extract a random sample of the phone contacts in SRVYCMWORK.
    -- Store the sample in table SRVYCMSAMP.
    DELETE FROM SRVYCMSAMP; -- Clear the CM sample output file first
    -- Get the random sample of CM contacts where VT Gas initiated the contact.
    CALL spGetSurveyCMSamp(wkSampleCountVGS,  'VGS ');
    -- Get the random sample of CM contacts where customer initiated the contact.
    CALL spGetSurveyCMSamp(wkSampleCountCUST, 'CUST');

    -- Update status on batch header.
    UPDATE SRVYBATCH set sb_Status = 'CMSAMP'
        WHERE sb_BatchNo = wkBatchId;

    ---------------------------------

    -- Combine the results from SRVYSOSAMP and SRVYCMSAMP into table SRVYDETAIL.
    -- This will be the file that is used to print the survey questionaires and
    -- cover letter, and will also be the survey history.
    CALL spGetSurveyListCombined(wkBatchId);

    ---------------------------------

    -- Update batch header with totals and completion status
    call spUpdateSurveyCounts(wkBatchId);

    update SRVYBATCH set sb_Status = 'FINISH'
    where sb_BatchNo = wkBatchId;

end