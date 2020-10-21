SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_DELETE_PLINK_USER ( 
	IN IN_PLU_USER_ID CHAR(15) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	LANGUAGE SQL 
	SPECIFIC SP_DELETE_PLINK_USER 
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
Created Sept 2015 by John Valance 
Delete an announcement in the PLINK_ANNOUNCEMENTS table. 
=================================================================================*/ 
	 
	IF NOT EXISTS ( SELECT * FROM PLINK_USER WHERE PLU_USER_ID = IN_PLU_USER_ID ) THEN 
		SET OUT_MESSAGE = 
			'Record not deleted: User ' 
			|| TRIM ( IN_PLU_USER_ID ) 
			|| ' not found in the PolarLink Users table.' ; 
		RETURN ; 
	END IF ; 
	 
	DELETE FROM PLINK_USER 
	WHERE PLU_USER_ID = IN_PLU_USER_ID ; 
	 
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_DELETE_PLINK_USER 
TO JVALANCE ; 
  
;
