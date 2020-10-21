drop procedure VGCUSTOM/spGetLast12MonthsUsageByPremise;
--@#
create procedure VGCUSTOM/spGetLast12MonthsUsageByPremise
(
	in inPremise char(15)
)
result sets 1
language sql

begin
	-- ============= DECLARATIONS ================
    declare wkEndPeriod dec(8,0);
    declare wkStartPeriod dec(8,0);
    declare wkYear dec(4,0);
    declare wkMonth dec(2,0);
    declare wkPeriodCount dec(3,0);
    declare wkUsage dec(6,0);

    -- Declare the result set to return the distinct work
    -- variables calculated in this procedure. Using the file
    -- sysibm/sysdummy1 allows us to return program variables
    -- in a result set, rather than record values from a file.
	DECLARE C1 CURSOR WITH RETURN FOR
	SELECT  wkStartPeriod as Start_Period,
	        wkEndPeriod as End_Period,
	        wkUsage as Usage,
	        wkPeriodCount as Usage_Periods
	FROM    sysibm/sysdummy1;

	-- ============= 'MAINLINE' ================
	
	-- Get last usage period available
    SELECT  DISTINCT BHVRYM
    INTO    wkEndPeriod
    FROM    UBLHRPRM
    WHERE   BHVPRM = inPremise
    AND     BHVRYM =
            (SELECT MAX(BHVRYM)
            FROM UBLHRPRM
            WHERE BHVPRM = inPremise);

    -- Compute starting period based on 12 periods
    -- ending with period just retrieved.
    set wkYear = wkEndPeriod / 100;
    set wkMonth = mod(wkEndPeriod, 100);
    if (wkMonth = 12) then
        set wkMonth = 01;
    else
        set wkMonth = wkMonth + 1;
        set wkYear = wkYear - 1;
    end if;
    set wkStartPeriod = (wkYear * 100) + wkMonth;


    -- Get number of distinct periods in database for this premise
    -- and date range. This is to determine if it's a new house,
    -- less than 12 months old. If so, we will display an explanatory
    -- message on the 'sorry' page.
    SELECT 	COUNT(*) INTO wkPeriodCount
    FROM    (SELECT DISTINCT BHVRYM
         	FROM    UBLHRPRM
        	WHERE 	BHVPRM  = inPremise
        	AND 	BHVRYM >= wkStartPeriod
        	AND 	BHVRYM <= wkEndPeriod)
    AS MYDUMMY;

    -- Run the usage summary query for the 12 month period
	SELECT 	sum(case
    		    when BHVUOR = 'R' then BHVMUS
    			when BHVUOR = 'U' then BHVESU
	   		end) into wkUsage
 	FROM    UBLHRPRM
	WHERE 	BHVPRM  = inPremise
	and 	BHVRYM >= wkStartPeriod
	and 	BHVRYM <= wkEndPeriod
	and     BHVRYM <> 0;

	-- Open the cursor to return all of
	-- the results back to the caller.
	open c1;
end

