SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_GET_ORDERNOTES ( 
	IN IN_ORDERNUM DECIMAL(8, 0) , 
	OUT OUT_NOTES VARCHAR(5000) , 
	OUT OUT_RESULT CHAR(1) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	LANGUAGE SQL 
	SPECIFIC SP_GET_ORDERNOTES 
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
  
Retrieve Order notes from ESN_PLNK, formatted as a single varchar string. 
  
Parameters: 
	in_OrderNum: Current order number for this session. 
	out_notes: A varchar string, which is a concatenation of all notes records 
		from ESN_PLNK for this order#.   
	out_result: 0 = error, 1 = success  
	out_message: error message if any (i.e., if out_result = 0)  
=================================================================================*/ 
	DECLARE WK_NOTES_LINE CHAR ( 50 ) ; 
DECLARE AT_END INT DEFAULT 0 ; 
DECLARE NOT_FOUND CONDITION FOR '02000' ; 
  
DECLARE C1 CURSOR FOR 
	SELECT	SNDESC 
		FROM	ESN_PLNK 
		WHERE	SNTYPE = 'O' 
			AND SNCUST = IN_ORDERNUM 
			AND SNSHIP = 0 
		ORDER BY SNSEQ 
	; 
  
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET AT_END = 1 ; 
	 
	 --================================================================ 
	SET OUT_MESSAGE = '' ; 
	SET OUT_RESULT = '1' ; 
	 
	OPEN C1 ; 
	FETCH NEXT FROM C1 INTO WK_NOTES_LINE ; 
	 
IF AT_END = 1 THEN 
		SET OUT_MESSAGE = 'No notes found for PLINK order# ' || TRIM ( CHAR ( IN_ORDERNUM ) ) || '.' ; 
		SET OUT_RESULT = '0' ; 
		RETURN ; 
	END IF ; 
  
	SET OUT_NOTES = '' ; 
	WHILE AT_END <> 1 DO 
		SET OUT_NOTES = OUT_NOTES || WK_NOTES_LINE ; 
		FETCH NEXT FROM C1 INTO WK_NOTES_LINE ; 
	END WHILE ; 
	 
	 -- Remove repeating blanks from output string 
	WHILE POSITION ( '  ' IN OUT_NOTES ) > 0 DO 
		SET OUT_NOTES = REPLACE ( OUT_NOTES , '  ' , ' ' ) ; 
	END WHILE ; 
	 
	RETURN ; 
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_GET_ORDERNOTES 
TO JVALANCE ; 
  
;
