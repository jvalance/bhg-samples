SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_AUTHENTICATE_PLINK_USER ( 
	IN IN_USER_ID CHAR(15) , 
	IN IN_PASSWORD_ENCRYPTED VARCHAR(60) , 
	OUT OUT_RESULT CHAR(1) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	DYNAMIC RESULT SETS 1 
	LANGUAGE SQL 
	SPECIFIC SP_AUTHENTICATE_PLINK_USER 
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
Authenticates user as valid for Polar Link, 
and password matches record in PLINK_USER table. 
==================================================================================*/ 
	DECLARE WK_PLU_USER_ID CHAR ( 15 ) ; 
	DECLARE WK_PLU_PASSWORD VARCHAR ( 60 ) ; 
	DECLARE WK_PLU_CUSTNO DEC ( 8 , 0 ) ; 
	DECLARE WK_PLU_CUST_GROUP CHAR ( 10 ) ; 
	DECLARE WK_PLU_1ST_USE TIMESTAMP ; 
	DECLARE WK_PLU_STATUS CHAR ( 1 ) ; 
	DECLARE WK_PLU_POLAR_CSR CHAR ( 1 ) ; 
  
	DECLARE WK_PLC_STATUS CHAR ( 1 ) ; 
	DECLARE WK_PLC_1ST_USE TIMESTAMP ; 
  
DECLARE AT_END INT DEFAULT 0 ; 
DECLARE NOT_FOUND CONDITION FOR '02000' ; 
DECLARE C1 CURSOR FOR 
SELECT 
		PLU_USER_ID , PLU_PASSWORD , PLU_CUST_GROUP , 
		PLU_STATUS , PLU_1ST_USE , PLU_POLAR_CSR 
FROM PLINK_USER 
	WHERE UPPER ( TRIM ( PLU_USER_ID ) ) = UPPER ( TRIM ( IN_USER_ID ) ) ; 
  
DECLARE C2 CURSOR FOR 
SELECT PLC_STATUS , PLC_1ST_USE 
FROM PLINK_CUSTOMER 
	WHERE PLC_CUST_GRP = WK_PLU_CUST_GROUP ; 
  
DECLARE C3 CURSOR WITH RETURN FOR 
SELECT PLU . * , PLC . * , PLC_CUST_NAME AS CUST_NAME 
FROM PLINK_USER PLU 
LEFT JOIN PLINK_CUSTOMER PLC 
	ON PLC_CUST_GRP = PLU_CUST_GROUP 
	WHERE UPPER ( TRIM ( PLU_USER_ID ) ) = UPPER ( TRIM ( IN_USER_ID ) ) ; 
  
DECLARE CONTINUE HANDLER FOR NOT FOUND SET AT_END = 1 ; 
  
	 --================================================================ 
	 -- User validation 
	OPEN C1 ; 
FETCH NEXT FROM C1 
	INTO WK_PLU_USER_ID , WK_PLU_PASSWORD , WK_PLU_CUST_GROUP , 
		WK_PLU_STATUS , WK_PLU_1ST_USE , WK_PLU_POLAR_CSR ; 
  
IF AT_END = 1 THEN 
		SET OUT_MESSAGE = TRIM ( IN_USER_ID ) || ' is not a valid PolarLink user.' ; 
		SET OUT_RESULT = '0' ; 
		RETURN ; 
	END IF ; 
  
IF TRIM ( WK_PLU_PASSWORD ) <> TRIM ( IN_PASSWORD_ENCRYPTED ) THEN 
		SET OUT_MESSAGE = 'Password is not correct for user ' || IN_USER_ID ; 
		SET OUT_RESULT = '0' ; 
		RETURN ; 
	END IF ; 
  
IF WK_PLU_STATUS <> 'E' THEN 
		SET OUT_MESSAGE = 'User ' || TRIM ( IN_USER_ID ) || ' is not an active PolarLink user.' ; 
		SET OUT_RESULT = '0' ; 
		RETURN ; 
	END IF ; 
  
	CLOSE C1 ; 
  
	 --================================================================ 
	 -- Customer validation -- only if user is not a CSR (no customer assigned to CSRs) 
	IF WK_PLU_POLAR_CSR <> 'Y' THEN 
  
	OPEN C2 ; 
	FETCH NEXT FROM C2 INTO WK_PLC_STATUS , WK_PLC_1ST_USE ; 
  
	IF AT_END = 1 THEN 
			SET OUT_MESSAGE = 'Customer # for user ' || TRIM ( IN_USER_ID ) || ' is not a valid PolarLink customer.' ; 
			SET OUT_RESULT = '0' ; 
			RETURN ; 
		END IF ; 
  
	IF WK_PLC_STATUS <> 'E' THEN 
			SET OUT_MESSAGE = 'Customer for user ' || TRIM ( IN_USER_ID ) || ' is not an active PolarLink customer.' ; 
			SET OUT_RESULT = '0' ; 
			RETURN ; 
		END IF ; 
  
	CLOSE C2 ; 
  
END IF ; 
  
	 --================================================================ 
	 -- Set return values 
	SET OUT_MESSAGE = 'Success' ; 
	SET OUT_RESULT = '1' ; 
  
	 -- Update user record with activity timestamps 
	IF WK_PLU_1ST_USE = '0001-01-01-00.00.00.000000' THEN 
		UPDATE PLINK_USER 
		SET PLU_1ST_USE = CURRENT TIMESTAMP , PLU_LAST_USE = CURRENT TIMESTAMP 
		WHERE PLU_USER_ID = IN_USER_ID ; 
	ELSE 
		UPDATE PLINK_USER 
		SET PLU_LAST_USE = CURRENT TIMESTAMP 
		WHERE PLU_USER_ID = IN_USER_ID ; 
	END IF ; 
  
	 -- Update customer record with activity timestamps (external users only, not for CSR) 
	IF WK_PLU_POLAR_CSR <> 'Y' THEN 
		IF WK_PLC_1ST_USE = '0001-01-01-00.00.00.000000' THEN 
			UPDATE PLINK_CUSTOMER 
			SET PLC_1ST_USE = CURRENT TIMESTAMP , PLC_LAST_USE = CURRENT TIMESTAMP 
			WHERE PLC_CUST_GRP = WK_PLU_CUST_GROUP ; 
		ELSE 
			UPDATE PLINK_CUSTOMER 
			SET PLC_LAST_USE = CURRENT TIMESTAMP 
			WHERE PLC_CUST_GRP = WK_PLU_CUST_GROUP ; 
		END IF ; 
	END IF ; 
  
	 -- Open result set cursor being returned. 
	OPEN C3 ; 
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_AUTHENTICATE_PLINK_USER 
TO JVALANCE ; 
  
;
