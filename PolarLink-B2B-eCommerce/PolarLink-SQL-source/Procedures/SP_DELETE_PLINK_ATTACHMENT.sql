SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_DELETE_PLINK_ATTACHMENT ( 
	IN IN_PLAT_ORDER_NO DECIMAL(8, 0) , 
	IN IN_PLAT_ATTACH_NO INTEGER , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	LANGUAGE SQL 
	SPECIFIC SP_DELETE_PLINK_ATTACHMENT 
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
Created June 2016 by John Valance 
Delete an attachment record in the PLINK_ANNOUNCEMENTS table. 
=================================================================================*/ 
  
	IF NOT EXISTS ( 
		SELECT * FROM PLINK_ATTACHMENT 
		WHERE PLAT_ORDER_NO = IN_PLAT_ORDER_NO 
		AND PLAT_ATTACH_NO = IN_PLAT_ATTACH_NO 
	) THEN 
		SET OUT_MESSAGE = 
			'Record not deleted: Attachment #' 
			|| TRIM ( CHAR ( IN_PLAT_ATTACH_NO ) ) 
			|| ' not found for order # ' || CHAR ( IN_PLAT_ORDER_NO ) || '.' ; 
		RETURN ; 
	END IF ; 
  
	DELETE FROM PLINK_ATTACHMENT 
	WHERE PLAT_ORDER_NO = IN_PLAT_ORDER_NO 
	AND PLAT_ATTACH_NO = IN_PLAT_ATTACH_NO ; 
  
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_DELETE_PLINK_ATTACHMENT 
TO JVALANCE ; 
  
;
