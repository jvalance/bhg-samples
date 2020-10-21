SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_SAVE_ORDERNOTES ( 
	IN IN_ORDERNUM DECIMAL(8, 0) , 
	IN IN_NOTES VARCHAR(5000) , 
	IN IN_USER VARCHAR(15) , 
	OUT OUT_RESULT CHAR(1) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	LANGUAGE SQL 
	SPECIFIC SP_SAVE_ORDERNOTES 
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
	DECLARE WK_NOTES VARCHAR ( 5000 ) ; 
	DECLARE WK_SEQNUM DEC ( 4 , 0 ) DEFAULT 0 ; 
	DECLARE WK_DATE8 DEC ( 8 , 0 ) ; 
	DECLARE WK_TIME6 DEC ( 6 , 0 ) ; 
  
	 --================================================================ 
	 -- Initialize work variables 
	SET OUT_MESSAGE = '' ; 
	SET OUT_RESULT = '1' ; 
	SET WK_NOTES = TRIM ( IN_NOTES ) ; 
	SET WK_DATE8 = FN_CURRDATE8 ( ) ; 
	SET WK_TIME6 = FN_CURRTIME6 ( ) ; 
  
	 -- Remove any existing order notes records for this order 
	DELETE FROM ESN_PLNK 
		WHERE	SNTYPE = 'O' 
		AND SNCUST = IN_ORDERNUM 
		AND SNSHIP = 0 ; 
  
	 -- Split notes into 50 char chunks and save each 
	 -- 50 char segment in the ESN file format. 
	WHILE LENGTH ( WK_NOTES ) > 0 DO 
		 -- Get next 50 char segment 
		SET WK_NOTES_LINE = SUBSTRING ( WK_NOTES , 1 , 50 ) ; 
		 -- Increment sequence number for ESN file key 
		SET WK_SEQNUM = WK_SEQNUM + 1 ; 
  
		 -- add record for each 50 char notes segment 
		INSERT INTO ESN_PLNK ( 
			SNID , 
			SNTYPE , 
			SNCUST , 
			SNSHIP , 
			SNSEQ , 
			SNDESC , 
			SNPRT , 
			SNPIC , 
			SNINV , 
			SNSTMT , 
			SNDOCR , 
			SNENDT , 
			SNENTM , 
			SNENUS , 
			SNMNDT , 
			SNMNTM , 
			SNMNUS 
		) VALUES ( 
			'SN' , 
			'O' , 
			IN_ORDERNUM , 
			0 , 
			WK_SEQNUM , 
			UPPER ( WK_NOTES_LINE ) , 
			' ' , 
			' ' , 
			' ' , 
			' ' , 
			' ' , 
			WK_DATE8 , 
			WK_TIME6 , 
			IN_USER , 
			WK_DATE8 , 
			WK_TIME6 , 
			IN_USER 
		) ; 
  
		IF LENGTH ( WK_NOTES ) > 50 THEN 
			 -- Remove the 50 chars that were just processed from the notes input string 
			SET WK_NOTES = SUBSTRING ( WK_NOTES , 51 ) ; 
		ELSE 
			 -- All done. This will end the while loop. 
			SET WK_NOTES = '' ; 
		END IF ; 
  
	END WHILE ; 
  
  
	RETURN ; 
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_SAVE_ORDERNOTES 
TO JVALANCE ; 
  
;
