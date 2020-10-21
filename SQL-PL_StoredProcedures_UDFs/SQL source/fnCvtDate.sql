DROP FUNCTION VGCUSTOM/fnCVTDATE;
--@#
CREATE FUNCTION VGCUSTOM/fnCVTDATE
(
   INDATE VARCHAR(8)
)
RETURNS VARCHAR(20)

LANGUAGE SQL
DETERMINISTIC
MODIFIES SQL DATA

BEGIN
 -- =====================================================================
 --  Procedure:  fnCvtDate
 --  Created:    3/29/07
 --  Author:     John Valance
 --  Purpose:
 --  Returns:	
 --  Parameters:
 -- ======================================================================

   DECLARE WKDATERESULT CHAR ( 20 ) ;
   DECLARE WKDATEIN CHAR ( 10 ) ;

   SET WKDATERESULT = ' ' ;
   SET WKDATEIN = TRIM ( INDATE ) ;

   CALL CVTDATE ( WKDATEIN , WKDATERESULT ) ;

   RETURN TRIM ( WKDATERESULT ) ;

END;
