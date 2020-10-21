SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_GET_CURRENT_ANNOUNCEMENTS ( 
	IN IN_CUST DECIMAL(8, 0) , 
	IN IN_SHIP_TO DECIMAL(4, 0) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	DYNAMIC RESULT SETS 1 
	LANGUAGE SQL 
	SPECIFIC SP_GET_CURRENT_ANNOUNCEMENTS 
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
Retrieve currently active announcements. 
  
Input parameters in_Cust and in_Ship_To are optional. 
  
This procedure can be called from 2 different places in PolarLink: 
	 
	Home page:  
		in_Cust and in_Ship_To will be zero, and procedure will return all  
		announcements currently active for all users (i.e. not specific to a customer or shipto) 
		 
	Place Order shipping info page, after selection of customer/ship-to: 
		in_Cust and in_Ship_To will be non-zero, and procedure will return all  
		announcements currently active for either the customer type for the selected customer, 
		or the ship-from warehouse facility associated with the selected ship-to. 
	 
=================================================================================*/ 
	DECLARE WK_FACILITY CHAR ( 3 ) DEFAULT '' ; 
	DECLARE WK_WAREHOUSE CHAR ( 3 ) DEFAULT '' ; 
	DECLARE WK_CUST_TYPE CHAR ( 4 ) DEFAULT '' ; 
  
	DECLARE AT_END INT DEFAULT 0 ; 
	DECLARE NOT_FOUND CONDITION FOR '02000' ; 
  
DECLARE CSR_ALL CURSOR WITH RETURN FOR 
SELECT * 
FROM PLINK_ANNOUNCEMENTS 
	WHERE PLA_START_DATE <= CURRENT DATE 
	AND	PLA_END_DATE >= CURRENT DATE 
	AND	PLA_CUST_TYPE = '' 
	AND	PLA_FACILITY = '' 
	ORDER BY PLA_START_DATE , PLA_END_DATE 
	; 
  
DECLARE CSR_SHIPTO CURSOR WITH RETURN FOR 
SELECT PLA_ID , PLA_CUST_TYPE , PLA_FACILITY , 
		PLA_START_DATE , PLA_END_DATE , PLA_MESSAGE 
FROM PLINK_ANNOUNCEMENTS 
	WHERE ( PLA_START_DATE <= CURRENT DATE ) 
	AND	( PLA_END_DATE >= CURRENT DATE ) 
	AND	( PLA_CUST_TYPE = WK_CUST_TYPE 
			OR	 
			PLA_FACILITY = WK_FACILITY ) 
	ORDER BY PLA_START_DATE , PLA_END_DATE 
	; 
  
	 
DECLARE CONTINUE HANDLER FOR NOT FOUND SET AT_END = 1 ; 
	 
	 --================================================================ 
	 -- Get customer type for this customer if customer # passed 
	IF IN_CUST <> 0 THEN 
		SELECT CTYPE 
		INTO WK_CUST_TYPE 
		FROM RCM 
	WHERE CCUST = IN_CUST ; 
  
	IF AT_END = 1 THEN 
			SET OUT_MESSAGE = 'Customer # ' || CHAR ( IN_CUST ) || ' not found in Customer Master table (RCM).' ; 
			RETURN ; 
		END IF ; 
END IF ; 
  
	 
	 -- Get facility for ship-to if ship to # passed 
	IF IN_CUST <> 0 AND IN_SHIP_TO <> 0 THEN 
		SELECT STFWHS 
		INTO WK_WAREHOUSE 
		FROM EST 
	WHERE TCUST = IN_CUST AND TSHIP = IN_SHIP_TO ; 
  
	IF AT_END = 1 THEN 
			SET OUT_MESSAGE = 'Customer/ShipTo ' || CHAR ( IN_CUST ) || '/' || CHAR ( IN_SHIP_TO ) || ' not found in Customer ShipTo table (EST).' ; 
			RETURN ; 
		END IF ; 
		 
		SELECT WMFAC 
		INTO WK_FACILITY 
		FROM IWM 
	WHERE LWHS = WK_WAREHOUSE ; 
		 
END IF ; 
  
	 -- Open result set cursor being returned. 
	IF IN_CUST <> 0 AND IN_SHIP_TO <> 0 THEN 
		OPEN CSR_SHIPTO ; 
	ELSE 
		OPEN CSR_ALL ; 
	END IF ; 
	 
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_GET_CURRENT_ANNOUNCEMENTS 
TO JVALANCE ; 
  
;
