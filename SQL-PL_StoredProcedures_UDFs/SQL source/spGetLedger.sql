DROP PROCEDURE VGCUSTOM/spGetLedger;
--@#
CREATE PROCEDURE VGCUSTOM/spGetLedger (
	in inAcctNo char(7),
	in inYear char(4),
	in inTranType char(4)
)
RESULT SETS 1
LANGUAGE SQL

BEGIN
	DECLARE c1 CURSOR WITH RETURN FOR
	
	SELECT 	    gr.ULACT,
	            gr.ULPRG,
			    gr.ULPRM,
			    ifnull(bl.BHSCUS,'0') as BHSCUS,
			    ifnull(bl.BHSCCK,'0') as BHSCCK,
			    gr.ULDAT,
      		    gr.ULSEQ,
      		    gr.ULTPC,
      		    ifnull(BHSGBC, '') as Group_Bill_Code,
      		    case
      		        when ULTPD = 'Charge' then 'Bill'
       		        when ULTPD = 'Cash Remittance' then 'Payment'
     		        else tp.ULTPD
      		    end as ULTPD,
      		    gr.ULTR#,
      		    sum(gr.ULTAM) as amount,
      		    ifnull(BHSCUR, 0) as current,
      		    ifnull(BHSPAS, 0) as pastdue,
      		    (BHSBGA+BHSBGB) as budbal,
                    ifnull(BHSTOT, 0) as totaldue,
       		    sum(gr.ULBUD) as budgetAmount,
       		    ifnull(BHSBUD, '') as budgetBill,
     		    ifnull(BHSPDD,0) as pastduedte
	FROM        ULGRCUS as gr

	LEFT JOIN   ULTP as tp
	ON          gr.ULTPC = tp.ULTPC

	LEFT JOIN   UBLH as bl
	ON          gr.ULTR# = bl.BHSTR#
	
    WHERE       gr.ULACT = inAcctNo
    AND         substring(char(gr.ULDAT),1,4)<= inYear
    AND         (( inTranType = 'BILL' AND gr.ULTPC IN (10, 40, 90, 180) )
                OR( inTranType = 'PMT' AND gr.ULTPC IN (20, 22, 150, 155) )
                OR( inTranType = 'ALL' AND gr.ULTPC NOT IN (50, 170) )
                )

    GROUP BY    gr.ULACT,
                gr.ULPRG,
                gr.ULPRM,
			    bl.BHSCUS,
			    bl.BHSCCK,
                gr.ULDAT,
                gr.ULSEQ,
                gr.ULTPC,
      		    BHSPDD,
                    BHSGBC,
      		    BHSBUD,
      		    BHSCUR,
      		    BHSPAS,
      		    BHSBGA,
      		    BHSBGB,
      		    BHSTOT,
                tp.ULTPD,
                gr.ULTR#

    ORDER BY    gr.ULDAT desc
    ;	

	OPEN c1;
END

