drop procedure VGCUSTOM/spUpdateSurveyCounts;
--@#
create procedure VGCUSTOM/spUpdateSurveyCounts
(
	in inBatchId  integer
)
result sets 1
language sql

begin
    declare wkSampleCountVGS dec(5,0);
    declare wkSampleCountCUST dec(5,0);
    declare wkSOCount integer default 0;
    declare wkCMCount integer default 0;
    declare wkVGSCount integer default 0;
    declare wkCUSTCount integer default 0;
    declare wkCount integer default 0;

    ---------------------------------

    -- Update batch header with totals and completion status
    select count(*) into wkCount from SRVYDETAIL
    where SL_BATCHNO = inBatchId;

    select count(*) into wkSOCount from SRVYDETAIL
    where SL_BATCHNO = inBatchId and SL_Contact_Type = 'SO';

    select count(*) into wkCMCount from SRVYDETAIL
    where SL_BATCHNO = inBatchId and  SL_Contact_Type = 'CM';

    select count(*) into wkVGSCount
    from SRVYDETAIL
    left join srvycmtyps t1
      on SL_Contact_Type = 'CM' and SL_Contact_Code = t1.surveycmtype
    left join srvysotyps t2
      on SL_Contact_Type = 'SO' and SL_Contact_Code = t2.surveysotype
    where SL_BATCHNO = inBatchId
    and (t1.initiatedby = 'VGS ' or t2.initiatedby = 'VGS ' );

    select count(*) into wkCUSTCount
    from SRVYDETAIL
    left join srvycmtyps t1
      on SL_Contact_Type = 'CM' and SL_Contact_Code = t1.surveycmtype
    left join srvysotyps t2
      on SL_Contact_Type = 'SO' and SL_Contact_Code = t2.surveysotype
    where SL_BATCHNO = inBatchId
    and (t1.initiatedby = 'CUST' or t2.initiatedby = 'CUST' );

    update SRVYBATCH set
        sb_SOCount = wkSOCount,
        sb_CMCount = wkCMCount,
        sb_VGSCount = wkVGSCount,
        sb_CustCount = wkCustCount,
        sb_Count = wkCount
    where sb_BatchNo = inBatchId;

end
