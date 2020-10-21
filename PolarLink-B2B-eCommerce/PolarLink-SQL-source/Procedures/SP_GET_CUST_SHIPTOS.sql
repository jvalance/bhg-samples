SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_GET_CUST_SHIPTOS ( 
	IN IN_CUSTGROUP CHAR(10) , 
	IN IN_SEARCHFILTERS VARCHAR(100) ) 
	DYNAMIC RESULT SETS 1 
	LANGUAGE SQL 
	SPECIFIC SP_GET_CUST_SHIPTOS 
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
Created July 2015 by John Valance  
   
Retrieve ship-tos or this customer, with optional search filters applied.  
   
Returns:  
	Result set of cusomer ship-to records matching in_CustNo and  
	any filter terms provided in in_SearchFilters.  
   
Paramters:  
	in_CustNo:  
		Customer Number to limit ship-tos returned.  
	in_SearchFilters:  
		A search term to filter the search. The string of characters  
		can appear anywhere in the ship-to name or address fields.  
=================================================================================*/ 
  
DECLARE C1 CURSOR WITH RETURN FOR 
SELECT 
	TCUST AS ST_CUST , 
	TSHIP AS ST_NUM , 
	TNAME AS ST_NAME , 
	TATN AS ST_ATTN , 
	TADR1 AS ST_ADR1 , 
	TADR2 AS ST_ADR2 , 
	TADR3 AS ST_ADR3 , 
	TSTE AS ST_STATE , 
	TPOST AS ST_ZIP , 
	CASE WHEN TEPKDL = ' ' THEN 'D' 
			ELSE TEPKDL 
	END AS SHIP_METHOD ,  -- pickup/delivery flag 
	 -- CESUBS = 'Y' means always require substitutes 
	 -- CESUBS = ' ' means check ship-to field TESUBS 
	CASE WHEN ( CME . CESUBS IS NULL ) OR ( CME . CESUBS = ' ' ) 
			THEN IFNULL ( TESUBS , ' ' ) 
			ELSE IFNULL ( CME . CESUBS , ' ' ) 
	END AS SUBS_REQUIRED  -- require substitutes flag 
	FROM	EST  -- ship-to 
	LEFT JOIN	ESTE  -- ship-to extension 
	ON TCUST = TECUST 
	AND TSHIP = TESHIP 
  
	LEFT JOIN	RCME AS CME  -- customer master extension 
	ON TCUST = CME . CECUST 
  
	WHERE 
		( TCUST IN ( 
			SELECT CGRP . CECUST 
			FROM RCME AS CGRP 
			WHERE CGRP . CEHHPF = IN_CUSTGROUP 
		) ) 
		AND TEPOLLNK = 'Y' 
		AND CEPOLLNK = 'Y' 
		AND TID = 'ST' 
		AND ( 
			( IN_SEARCHFILTERS = '' ) 
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
	ORDER BY TCUST , TSHIP 
	; 
  
	 --================================================================ 
SET IN_SEARCHFILTERS = TRIM ( UPPER ( IN_SEARCHFILTERS ) ) ; 
  
-- Open result set cursor being returned. 
OPEN C1 ; 
  
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_GET_CUST_SHIPTOS 
TO JVALANCE ; 
  
;
