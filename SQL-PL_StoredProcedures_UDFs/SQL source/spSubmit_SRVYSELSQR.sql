drop procedure VGCUSTOM/spSubmit_SRVYSELSQR;
--@#
create procedure VGCUSTOM/spSubmit_SRVYSELSQR
(
    in ESTRESPCUS CHAR(5),
    in ESTRESPVGS CHAR(5),
    in ESTRESPRAT CHAR(3),
    in DROPSPERQ CHAR(3),
    in JOBD CHAR(10)
)
 RESULT SETS 0
 EXTERNAL NAME SrvySelSqC
 LANGUAGE  CL
 PARAMETER STYLE GENERAL
;