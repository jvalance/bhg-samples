drop procedure VGCUSTOM/spGetLast12MonthsUsage;
--@#
create procedure VGCUSTOM/spGetLast12MonthsUsage
(
	in inAcctNo char(7)
)
result sets 1
language sql

begin
    declare endPeriod dec(8,0);
    declare startPeriod dec(8,0);
    declare wkYear dec(4,0);
    declare wkMonth dec(2,0);

	DECLARE C1 CURSOR WITH RETURN FOR
	SELECT 	startPeriod,
	        endPeriod,
	        sum(case
				    when BHVUOR = 'R' then BHVMUS
    				when BHVUOR = 'U' then BHVESU
	    		end)
	    	as Usage
 	FROM    UBLHRPRM
	WHERE 	BHVACT  = inAcctNo
	and 	BHVRYM >= startPeriod
	and 	BHVRYM <= endPeriod
	and     BHVRYM <> 0;

	-- Get last usage period available
    SELECT  BHVRYM INTO endPeriod
    FROM    UBLHRPRM
    WHERE   BHVACT = inAcctNo
    AND     BHVRYM =
            (SELECT MAX(BHVRYM)
            FROM UBLHRPRM
            WHERE BHVACT = inAcctNo);

    -- Compute starting period based on 12 periods
    -- ending with period just retrieved.
    set wkYear = endPeriod / 100;
    set wkMonth = mod(endPeriod, 100);
    if (wkMonth = 12) then
        set wkMonth = 01;
    else
        set wkMonth = wkMonth + 1;
        set wkYear = wkYear - 1;
    end if;
    set startPeriod = (wkYear * 100) + wkMonth;

    -- Run the usage summary query for the 12 month period
	open c1;
end

