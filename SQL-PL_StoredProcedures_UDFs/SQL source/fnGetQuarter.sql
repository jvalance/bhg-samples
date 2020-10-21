DROP FUNCTION VGCUSTOM/fnGetQuarter;
--@#
CREATE FUNCTION VGCUSTOM/fnGetQuarter
(
   inDate dec(8,0)
)
RETURNS CHAR(6)

LANGUAGE SQL
DETERMINISTIC
MODIFIES SQL DATA

BEGIN
 -- =====================================================================
 --  Procedure:  fnGetQuarter
 --  Created:    3/27/08
 --  Author:     John Valance
 --  Purpose:    Returns the year and quarter for a passed date. Quarter
 --              is based on calendar month.
 --  Example:	 fnGetQuarter(20080327) returns '2008Q1'
 -- ======================================================================
    --declare wkYearQuarter char(6) default '';
    declare wkQuarter char(2) default '';
    declare wkMonth char(2) default '';
    declare wkYear char(4) default '';

    set wkYear = digits(dec(inDate / 10000,4,0));
    set wkMonth = substr(digits(inDate),5,2);

    set wkQuarter =
        case
            when wkMonth between '01' and '03' then 'Q1'
            when wkMonth between '04' and '06' then 'Q2'
            when wkMonth between '07' and '09' then 'Q3'
            when wkMonth between '10' and '12' then 'Q4'
            else  '??'
        end;

   return wkYear || wkQuarter;

END;
