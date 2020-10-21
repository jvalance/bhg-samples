drop procedure VGCUSTOM/spGetUnpostedAmts;
--@#
create procedure VGCUSTOM/spGetUnpostedAmts
(
	in  acctNo char(7)
)
 RESULT SETS 1
 EXTERNAL NAME  VGCUSTOM/WebUnpAdj
 LANGUAGE  RPGLE
 PARAMETER STYLE GENERAL
;