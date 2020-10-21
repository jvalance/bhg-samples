drop procedure VGCUSTOM/spGetUsageYears;
--@#
create procedure VGCUSTOM/spGetUsageYears (
	in inAcctNo char(7) 
)
result sets 1
language sql

begin
	declare year10 char(4);
	
	declare c1 cursor with return for
	select distinct substr(char(BHVBLD),1,4) as year
	from UBLHRACT as hr
	where hr.BHVACT = inAcctNo 
	and substr(char(BHVBLD),1,4) >= year10
	order by year desc;
	
	set year10 = char(year(current date) - 10);
	open c1;
end