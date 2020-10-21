drop procedure VGCUSTOM/spGetLedgerYears;
--@#
create procedure VGCUSTOM/spGetLedgerYears (
	in inAcctNo char(7) 
)
result sets 1
language sql

begin
	declare year10 char(4);
	
	declare c1 cursor with return for
	select distinct substr(char(ULDAT),1,4) as year
	from ulgrcus as gr
	where gr.ULACT = inAcctNo 
	and substr(char(ULDAT),1,4) >= year10
	order by year desc;
	
	set year10 = char(year(current date) - 10);
	open c1;
end