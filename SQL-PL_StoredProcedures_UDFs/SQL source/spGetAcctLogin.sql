drop procedure VGCUSTOM/spGetAcctLogin;
--@#
create procedure VGCUSTOM/spGetAcctLogin
(
   in inAcctNo char(7)
)
RESULT SETS 1
LANGUAGE SQL

BEGIN

   DECLARE C1 CURSOR WITH RETURN FOR

   SELECT         UMACT AS ACCOUNT_NUM,
                  UMCKD AS CHECK_DIGIT,
                  UMCUS	AS CUSTOMER_NUM,
                  UMNAM AS ACCOUNT_NAME,
                  UMLNM AS LAST_NAME,
                  UMFNM AS FIRST_NAME,
                  UMZIP AS ZIP_CODE,
                  case
                    when UMOPH = 0 then
                        UMBPH
                    else
                        UMOPH
                    end
                  as PHONE,
                  UMPRM AS PREMISE_NUM,
                  UMISD AS INITIAL_SERVICE_DATE,
                  UMDSD AS DISCONTINUED_DATE,
                  UMLBD AS LAST_BILL_DATE,
                  UMBAL AS AR_BALANCE,
                  UMSTS AS ACCT_STATUS,
                  UMTYP AS ACCT_TYPE,
                  ifnull( UIASSD, '** Account Status unknown **') as ACCT_STS_DESCR,
                  ifnull( UPSAD, '') as SERVICE_ADDRESS,
                  ifnull( UPCTC, '') as SERVICE_CITY,
                  ifnull( UPSTC, '') as SERVICE_STATE,
                  ifnull( UPZIP, '') as SERVICE_ZIP

   FROM           UACT as act

   LEFT JOIN      UPRM as prm
   ON             act.UMPRM = prm.UPPRM

   LEFT JOIN      UAST as ast
   ON             UMSTS = UIASC

   WHERE          UMACT = inAcctNo
   ;

   OPEN C1;
END


