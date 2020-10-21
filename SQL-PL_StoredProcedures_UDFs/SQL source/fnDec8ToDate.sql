DROP FUNCTION VGCUSTOM/fnDec8ToDate;
--@#
CREATE FUNCTION VGCUSTOM/fnDec8ToDate
(
   inDate8 dec(8,0)
)
RETURNS DATE

LANGUAGE SQL
DETERMINISTIC
MODIFIES SQL DATA
set option datfmt = *ISO

BEGIN
 -- =====================================================================
 --  Procedure:  fnDec8ToDate
 --  Created:    5/23/08
 --  Author:     John Valance
 --  Purpose:    Convert an 8-digit numeric date to DATE data type.
 --  Returns:	 DATE
 -- ======================================================================
   declare wkDateResult date default null;
   declare wkIsoDateChar char(10);
   declare wkDateChar char(8);

    DECLARE bad_date CONDITION FOR '01534';
    DECLARE CONTINUE HANDLER FOR bad_date return date('0001-01-01');

   if inDate8 = 0 then
      return date('2008-01-01');
   end if;

   set wkDateChar = trim(char(inDate8));

   set wkIsoDateChar = substr(wkDateChar,1,4) || '-' ||
                       substr(wkDateChar,5,2) || '-' ||
                       substr(wkDateChar,7,2);

   set wkDateResult = date(wkIsoDateChar);

   return wkDateResult;

END;