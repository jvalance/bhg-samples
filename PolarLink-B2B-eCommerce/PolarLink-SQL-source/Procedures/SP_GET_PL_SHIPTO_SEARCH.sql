SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_GET_PL_SHIPTO_SEARCH ( 
	IN IN_CUSTGROUP CHAR(10) , 
	IN IN_SEARCHFILTERS VARCHAR(100) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	DYNAMIC RESULT SETS 1 
	LANGUAGE SQL 
	SPECIFIC SP_GET_PL_SHIPTO_SEARCH 
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
SELECT 
		PLST_CUST_GRP , 
		PLST_CUSTNO , 
		PLST_SHIPTO , 
		PLST_EMAILS , 
		TNAME AS ST_NAME , 
		TATN AS ST_ATTN , 
		TADR1 AS ST_ADR1 , 
		TADR2 AS ST_ADR2 , 
		TADR3 AS ST_ADR3 , 
		TSTE AS ST_STATE , 
		TPOST AS ST_ZIP , 
		PLST_CRT_TIME , 
		PLST_CRT_USER , 
		PLST_CHG_TIME , 
		PLST_CHG_USER 
  
  
FROM		PL_SHIPTO AS PLST 
  
JOIN EST  -- ship-to 
	ON	TCUST = PLST_CUSTNO 
	AND	TSHIP = PLST_SHIPTO 
  
LEFT JOIN	ESTE  -- ship-to extension 
	ON PLST_CUSTNO = TECUST 
		AND PLST_SHIPTO = TESHIP 
  
LEFT JOIN RCME AS CME  -- customer master extension 
	ON PLST_CUSTNO = CME . CECUST 
  
	WHERE 
		PLST_CUST_GRP = IN_CUSTGROUP 
  
	AND ( ( IN_SEARCHFILTERS = '' ) 
			OR 
			( IN_SEARCHFILTERS <> '' AND 
				( 
					UPPER ( TNAME ) LIKE ( '%' || IN_SEARCHFILTERS || '%' ) OR 
					UPPER ( TATN ) LIKE ( '%' || IN_SEARCHFILTERS || '%' ) OR 
					UPPER ( TADR1 ) LIKE ( '%' || IN_SEARCHFILTERS || '%' ) OR 
					UPPER ( TADR2 ) LIKE ( '%' || IN_SEARCHFILTERS || '%' ) OR 
					UPPER ( TADR3 ) LIKE ( '%' || IN_SEARCHFILTERS || '%' ) OR 
					UPPER ( TSTE ) LIKE ( '%' || IN_SEARCHFILTERS || '%' ) OR 
					UPPER ( TPOST ) LIKE ( '%' || IN_SEARCHFILTERS || '%' ) 
				) 
			) 
		) 
	; 
  
	 --================================================================ 
SET IN_SEARCHFILTERS = TRIM ( UPPER ( IN_SEARCHFILTERS ) ) ; 
  
	 -- Open result set cursor being returned. 
	OPEN C1 ; 
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_GET_PL_SHIPTO_SEARCH 
TO JVALANCE ; 
  
;
