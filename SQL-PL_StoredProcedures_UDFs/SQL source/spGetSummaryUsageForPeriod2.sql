drop procedure VGCUSTOM/spGetSummaryUsageForPeriod2;
--@#
create procedure VGCUSTOM/spGetSummaryUsageForPeriod2
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
			BHVRYM as BILL_PERIOD,
			substring(char(BHVRYM),1,4) as BILL_YEAR,
			substring(char(BHVRYM),5,2) as BILL_MONTH,
			sum(
			case
				when BHVUOR = 'R' then BHVMUS
				when BHVUOR = 'U' then BHVESU
			end) as Usage,
			(select dec(sum(dgadd),7,0)
			 from udgd
			 where DGGEZ = 'VT'
			 and DGDDT > usg.BHVPRD
			 and DGDDT <= usg.BHVCRD) as Degree_Days
				
 	FROM    UBLHRPRM usg

	WHERE 	BHVACT  = inAcctNo
	and 	BHVRYM >= inPeriodStart
	and 	BHVRYM <= inPeriodEnd
	and     BHVRYM <> 0
	
	GROUP BY BHVACT, BHVPRM, BHVMTR, BHVRYM,
			substring(char(BHVRYM),1,4),
			substring(char(BHVRYM),5,2),
            (select dec(sum(dgadd),7,0)
			 from udgd
			 where DGGEZ = 'VT'
			 and DGDDT > usg.BHVPRD
			 and DGDDT <= usg.BHVCRD) -- Degree days subselect
			
	ORDER BY BHVACT, BHVRYM;
	
	open c1;
end

