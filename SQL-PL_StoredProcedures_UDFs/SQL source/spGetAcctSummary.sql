drop procedure VGCUSTOM/spGetAcctSummary;
--@#
create procedure VGCUSTOM/spGetAcctSummary
(
	in inAcctNo char(7)
)
RESULT SETS 1
LANGUAGE SQL

BEGIN
   DECLARE C1 CURSOR WITH RETURN FOR
   SELECT  DISTINCT
                  A.UMACT AS ACCT_NO,
                  A.UMCKD AS CHECK_DIGIT,
                  A.UMNAM AS ACCT_NAME,
                  A.UMPRM AS PREMISE_NO,
                  A.UMISD AS INIT_SVC_DATE,
                  A.UMDSD AS DISCONTINUED_DATE,
                  A.UMLBD AS LAST_BILL_DATE,
                  A.UMBAL AS AR_BALANCE,
                     case
                    when A.UMOPH = 0 then
                        A.UMBPH
                    else
                        A.UMOPH
                    end
                  as PHONE,
                  B.UAAG1 AS AGING_AMT_1,
                  B.UAAG2 + B.UAAG3 + B.UAAG4
                    + B.UAAG5 + B.UAAG6 AS AGING_TOTAL,
                  C.UCSCH AS RATE_SCHED,
                  (select distinct RCRDS from usch rs1
                   where rs1.rcsch = C.UCSCH
                    and  rs1.rcefd =
                      (select max(rs2.rcefd)
                       from usch rs2
                       where rs2.rcsch = C.UCSCH)
                  ) as RATE_SCHED_DESC,
                  D.UIASSD AS SVC_DESC,
                  E.UPSAD AS SVC_ADDRESS,
                  F.UBEFD AS EFT_EFF_DATE

   FROM           vgfiles/uact as A

    LEFT JOIN     vgfiles/uagn as B
    ON            A.UMACT = B.UAACT

    LEFT JOIN     vgfiles/ucsr as C
    ON            A.UMPRM = C.UCPRM

    LEFT JOIN     vgfiles/uast as D
    ON            A.UMSTS = D.UIASC

    LEFT JOIN     vgfiles/uprm as E
    ON            A.UMPRM = E.UPPRM

    LEFT JOIN     vgfiles/ueft as F
    ON            A.UMACT = F.UBACT

   WHERE          A.UMACT = inAcctNo
    AND           (B.UAPRM is null or B.UAPRM = ' ')
    and           (UAUTL is null or UAUTL = ' ')
    and           (C.UCACT is null or C.UCACT = 0)
   ;

   OPEN C1;
END
