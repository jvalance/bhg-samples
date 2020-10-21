SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_GET_PL_SHIPTO_REC ( 
	IN IN_CUST_GRP CHAR(10) , 
	IN IN_CUSTNO DECIMAL(8, 0) , 
	IN IN_SHIPTO DECIMAL(4, 0) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	DYNAMIC RESULT SETS 1 
	LANGUAGE SQL 
	SPECIFIC SP_GET_PL_SHIPTO_REC 
	NOT DETERMINISTIC 
	MODIFIES SQL DATA 
	CALLED ON NULL INPUT 
	SET OPTION  ALWBLK = *ALLREAD , 
	ALWCPYDTA = *OPTIMIZE , 
	COMMIT = *NONE , 
	DECRESULT = (31, 31, 00) , 
	DFTRDBCOL = *NONE , 
	DYNDFTCOL = *NO , 
	DYNUSRPRF = *USER , 
	SRTSEQ = *HEX   
	BEGIN 
/*================================================================================  
Created June 2017 by John Valance  
=================================================================================*/ 
  
DECLARE C1 CURSOR WITH RETURN FOR 
SELECT PLST . * , 
		TNAME AS ST_NAME , 
		TATN AS ST_ATTN , 
		TADR1 AS ST_ADR1 , 
		TADR2 AS ST_ADR2 , 
		TADR3 AS ST_ADR3 , 
		TSTE AS ST_STATE , 
		TPOST AS ST_ZIP 
  
FROM PL_SHIPTO AS PLST 
  
JOIN EST  -- ship-to 
	ON	TCUST = PLST_CUSTNO 
	AND	TSHIP = PLST_SHIPTO 
  
	WHERE 
		PLST_CUST_GRP = IN_CUST_GRP AND 
		PLST_CUSTNO = IN_CUSTNO AND 
		PLST_SHIPTO = IN_SHIPTO 
	; 
  
	 --================================================================ 
	IF NOT EXISTS ( SELECT * FROM PL_SHIPTO 
				WHERE PLST_CUST_GRP = IN_CUST_GRP AND 
						PLST_CUSTNO = IN_CUSTNO AND 
						PLST_SHIPTO = IN_SHIPTO 
	) THEN 
			SET OUT_MESSAGE = 
				'Record not found on PL_SHIPTO for ' 
				|| ' Customer Group = ' || TRIM ( IN_CUST_GRP ) 
				|| ', Account# = ' || TRIM ( IN_CUSTNO ) 
				|| ', ShipTo# = ' || TRIM ( IN_SHIPTO ) 
				|| '.' ; 
			RETURN ; 
END IF ; 
  
	 -- Open result set cursor being returned. 
	OPEN C1 ; 
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_GET_PL_SHIPTO_REC 
TO JVALANCE ; 
  
;
