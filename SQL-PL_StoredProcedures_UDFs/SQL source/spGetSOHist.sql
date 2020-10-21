DROP PROCEDURE VGCUSTOM/spGetSOHist;
--@#
CREATE PROCEDURE VGCUSTOM/spGetSOHist
(
	in inAcctNo char(7),
	in inYear char(4)
)
RESULT SETS 1
LANGUAGE SQL

BEGIN
    DECLARE wkFrom char(8);
    DECLARE wkTo char(8);

    DECLARE c1 CURSOR WITH RETURN FOR
	
    SELECT
        SMSO# as Service_Order_No,
        SMSCD as Service_Type_Code,
        SCDES as Service_Type_Description,
        case
        when SMCLO = 'Y' then 'Closed'
        when SMCLO = 'C' then 'Cancelled'
        when SMCLO = ' ' then 'Open'
        end  as SO_Closed,
        SMSDT as Schedule_Date,
        SMAPX as Time_Code,
        SADES as Time_Desc,
        SMODT as SO_Order_Date,
        SMOTM as SO_Order_Time,
        SMSOS as SO_Status_Code,
        SMSTD as Start_Date,
        SMSTT as Start_Time,
        SMCPD as Completed_Date,
        SMCPT as Completed_Time,
        SMCP# as Completed_AM_PM_Code

    FROM    SCMS so

    JOIN    SOCD cd
    ON      so.SMSCD = cd.SCSCD

    LEFT JOIN SAPX ap
    ON      so.SMAPX = ap.SAAPX

    WHERE   SMODT between wkFrom and wkTo
    AND     SMACT = inAcctNo

    ORDER BY SMSO# DESC
    ;	

    set wkFrom = inYear || '0000';
    set wkTo = inYear || '1231';

	OPEN c1;
END
