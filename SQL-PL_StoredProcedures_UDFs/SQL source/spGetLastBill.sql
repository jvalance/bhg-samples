DROP PROCEDURE VGCUSTOM/spGetLastBill;
--@#
CREATE PROCEDURE VGCUSTOM/spGetLastBill
(
	IN inAcctNo CHAR(7)
)
RESULT SETS 1
LANGUAGE SQL

BEGIN
    declare wkBillTran# dec(15,0);
    declare wkMaxBillDate dec(8,0);

    DECLARE C1 CURSOR WITH RETURN FOR
    SELECT  BHSBLD,
            BHSPDD,
            BHSPDD,
            BHSTOT,
            BHSBGA,
            BHSBGB,
            BHSCUR,
            BHSPAS,
            BHSPAY
    FROM    UBLH bl
    WHERE   BHSTR# = wkBillTran#;

    SELECT MAX(ULDAT) into wkMaxBillDate
    FROM ULGRCUS as gr
    LEFT JOIN ULTP as tp ON gr.ULTPC = tp.ULTPC
    WHERE ULACT = inAcctNo
    AND ULTPD = 'Charge';

    -- Must use MAX(ULTR#) because we may have more than one
    -- charge transaction on the same day.
    SELECT MAX(ULTR#) into wkBillTran#
    FROM ULGRCUS as gr
    LEFT JOIN ULTP as tp ON gr.ULTPC = tp.ULTPC
    WHERE ULACT = inAcctNo
    AND ULTPD = 'Charge'
    AND ULDAT = wkMaxBillDate
    AND ULUTL= 'G';

	OPEN C1;
END
