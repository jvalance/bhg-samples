drop procedure VGCUSTOM/spAddAcctToSurveyBatch;
--@#
create procedure VGCUSTOM/spAddAcctToSurveyBatch
(
	in inAccountNo DECIMAL(7, 0),
	in inBatch  integer
)
result sets 0
language sql

begin
    INSERT INTO SRVYDETAIL
	   (SL_AcctNo, SL_Contact_Code, SL_LastName, SL_FirstName,
	   SL_Address1, SL_Address2, SL_Address3, SL_ZipCode,
	   SL_BatchNo, SL_RunDate, SL_ManualEntry, SL_Composite_Code)
	SELECT
	    inAccountNo, '*MNL', UMLNM, UMFNM, UMAD1, UMAD2, UMAD3, UMZIP,
		inBatch, current date, 'Y', digits(inAccountNo) || '-M'
		from VGFILES/UACT
		where UMACT = inAccountNo;
	
    -- Refresh batch header total record counts
    call spUpdateSurveyCounts(inBatch);
	
end
