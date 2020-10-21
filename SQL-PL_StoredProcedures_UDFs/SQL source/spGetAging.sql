drop procedure VGCUSTOM/spGetAging;
--@#
create procedure VGCUSTOM/spGetAging
(
	in  acctNo char(7)
)
 RESULT SETS 1
 EXTERNAL NAME  VGCUSTOM/WebAging
 LANGUAGE  RPGLE
 PARAMETER STYLE GENERAL
;