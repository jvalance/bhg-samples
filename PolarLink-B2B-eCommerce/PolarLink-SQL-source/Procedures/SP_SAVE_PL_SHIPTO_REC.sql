SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_SAVE_PL_SHIPTO_REC ( 
	IN IN_USER_ID CHAR(15) , 
	IN IN_CUST_GRP CHAR(10) , 
	IN IN_CUSTNO DECIMAL(8, 0) , 
	IN IN_SHIPTO DECIMAL(4, 0) , 
	IN IN_EMAILS VARCHAR(1500) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	LANGUAGE SQL 
	SPECIFIC SP_SAVE_PL_SHIPTO_REC 
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
	DECLARE WK_CUSTCOUNT INTEGER ; 
  
	SET IN_CUST_GRP = UPPER ( TRIM ( IN_CUST_GRP ) ) ; 
  
	IF EXISTS ( SELECT * FROM PL_SHIPTO 
				WHERE PLST_CUST_GRP = IN_CUST_GRP AND 
						PLST_CUSTNO = IN_CUSTNO AND 
						PLST_SHIPTO = IN_SHIPTO 
	) THEN 
		 -- Record already exists - perform UPDATE 
		UPDATE PL_SHIPTO SET 
			PLST_EMAILS = IN_EMAILS , 
			PLST_CHG_TIME = CURRENT TIMESTAMP , 
			PLST_CHG_USER = IN_USER_ID 
		WHERE 
			PLST_CUST_GRP = IN_CUST_GRP AND 
			PLST_CUSTNO = IN_CUSTNO AND 
			PLST_SHIPTO = IN_SHIPTO 
		; 
  
	ELSE 
		 -- Record not found - perform INSERT 
		 -- Validate the customer group code before adding record 
		SELECT COUNT ( * ) INTO WK_CUSTCOUNT FROM RCME WHERE CEHHPF = IN_CUST_GRP GROUP BY CEHHPF ; 
  
		IF WK_CUSTCOUNT IS NULL OR WK_CUSTCOUNT = 0 THEN 
			SET OUT_MESSAGE = 
				'Record not added: Customer Group ' 
				|| TRIM ( IN_CUST_GRP ) 
				|| ' does not exist in the Customers Extension table (RCME).' ; 
			RETURN ; 
		END IF ; 
  
		INSERT INTO PL_SHIPTO ( 
			PLST_CUST_GRP , 
			PLST_CUSTNO , 
			PLST_SHIPTO , 
			PLST_EMAILS , 
			PLST_CRT_TIME , 
			PLST_CRT_USER 
		) VALUES ( 
			IN_CUST_GRP , 
			IN_CUSTNO , 
			IN_SHIPTO , 
			IN_EMAILS , 
			CURRENT TIMESTAMP , 
			IN_USER_ID 
		) ; 
  
	END IF ; 
  
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_SAVE_PL_SHIPTO_REC 
TO JVALANCE ; 
  
;
