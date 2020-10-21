drop procedure VGCUSTOM/spGetSOYears;
--@#
create procedure VGCUSTOM/spGetSOYears (
	in inAcctNo char(7)
)
result sets 1
language sql

begin
	declare year10 char(4);
	
	declare c1 cursor with return for
	select distinct substr(char(SMODT),1,4) as year
    FROM    SCMS so
    WHERE   substr(char(SMODT),1,4) >= year10
    AND     SMACT = inAcctNo
 	order by year desc;
	
	set year10 = char(year(current date) - 10);
	open c1;
end