SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_GET_ORDERSUBSTITUTEITEMS ( 
	IN IN_ORDERNUM DECIMAL(8, 0) , 
	OUT OUT_SUBSCOUNT DECIMAL(3, 0) , 
	OUT OUT_RESULT CHAR(1) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	DYNAMIC RESULT SETS 1 
	LANGUAGE SQL 
	SPECIFIC SP_GET_ORDERSUBSTITUTEITEMS 
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
Created August 2015 by John Valance 
  
Retrieve SUBSTITUTE items for display on substitute item search. 
  
Returns:  
	Result set with multiple rows  
=================================================================================*/ 
  
DECLARE C1 CURSOR WITH RETURN FOR 
	SELECT 
		PLS_ORDER_NO , 
		PLS_ITEM_NO , 
		IDESC AS ITEM_DESC , 
		PLS_PRIORITY 
	FROM		PLINK_SUBSTITUTES  -- PL order substitute items work file 
	LEFT JOIN	IIM  -- Item master 
	ON		PLS_ITEM_NO = IPROD 
WHERE		PLS_ORDER_NO = IN_ORDERNUM 
ORDER BY	PLS_PRIORITY , IDESC 
	; 
  
--================================================================ 
	 -- Initialize work variables 
	SET OUT_MESSAGE = '' ; 
	SET OUT_RESULT = '1' ; 
  
	 -- Return count of subs for this order 
	SELECT COUNT ( * ) INTO OUT_SUBSCOUNT 
	FROM PLINK_SUBSTITUTES 
	WHERE PLS_ORDER_NO = IN_ORDERNUM ; 
	 
	 -- Open result set cursor being returned. 
	OPEN C1 ; 
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_GET_ORDERSUBSTITUTEITEMS 
TO JVALANCE ; 
  
;
