drop procedure VGCUSTOM/spGetUsageForPeriod;
--@#
create procedure VGCUSTOM/spGetUsageForPeriod
(
	in inAcctNo char(7),
	in inPeriodStart char(6),
	in inPeriodEnd char(6)
)
result sets 1
language sql

begin
	declare c1 cursor with return for
	
	SELECT  BHVACT as AccountNo,
			BHVPRM as Premise,
			BHVMTR as Meter,
			BHVCRD as Read_Date,
			BHVBLD as Bill_Date,
			substring(char(BHVBLD),1,4) as Bill_Year,
			substring(char(BHVBLD),5,2) as Bill_Month,
			substring(char(BHVCRD),1,4) as Read_Year,
			substring(char(BHVCRD),5,2) as Read_Month,
			case
				when BHVUOR = 'R' then 'Actual'
				when BHVUOR = 'U' then 'Estimated'
			end as ReadType,
			BHVCUR as Curr_Read,
			case
				when BHVUOR = 'R' then BHVMUS
				when BHVUOR = 'U' then BHVESU
			end as Usage,
			BHVUOM as Units
				
 	FROM    UBLHRPRM

	WHERE 	(BHVACT  = inAcctNo)
	and 	substring(char(BHVBLD),1,6) >= inPeriodStart
	and 	substring(char(BHVBLD),1,6) <= inPeriodEnd
	ORDER BY BHVBLD;

	open c1;
end

