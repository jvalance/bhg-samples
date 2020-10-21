SET PATH *LIBL ;

CREATE OR REPLACE FUNCTION UDTF_GET_ITEMAUTH ( 
	IN_CUSTNO DECIMAL(8, 0) , 
	IN_SHIPTO DECIMAL(4, 0) ) 
	RETURNS TABLE ( 
	ITM_NUMBER CHAR(35) , 
	ITM_DESC CHAR(50) , 
	ITM_BRAND CHAR(5) , 
	BRAND_DESC CHAR(30) , 
	ITM_SIZE CHAR(5) , 
	SIZE_DESC CHAR(30) , 
	ITM_PRICE DECIMAL(14, 4) , 
	ITM_UOM CHAR(2) , 
	ITM_UOM_CONVERSION DECIMAL(11, 5) , 
	ITM_PACKAGE_CODE DECIMAL(3, 0) , 
	ITM_TYPE CHAR(5) , 
	ITM_CLASS CHAR(2) )   
	LANGUAGE SQL 
	SPECIFIC UDTF_GET_ITEMAUTH 
	NOT DETERMINISTIC 
	READS SQL DATA 
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
Created May 2016 by John Valance 
=================================================================================*/ 
  
-- Important info from Steve G.: 
-- Our reference to Pending status will now be referred to as PLC (product life cycle) logic: 
-- include item only when field ICPLC is either 'ACTIVE' or blank for the facility/item in CIC. 
-- Facility is retrieved by using the account's warehouse (STFWHS from EST) to access IWM 
-- where facility is in field WMFAC. 
	DECLARE WK_WHS_CODE CHAR ( 3 ) DEFAULT '' ; 
	DECLARE WK_FACILITY CHAR ( 3 ) DEFAULT '' ; 
  
	SELECT STFWHS INTO WK_WHS_CODE 
	FROM EST 
	WHERE TCUST = IN_CUSTNO 
	AND TSHIP = IN_SHIPTO ; 
  
	SELECT WMFAC INTO WK_FACILITY 
	FROM IWM 
	WHERE LWHS = WK_WHS_CODE ; 
  
	RETURN 
	WITH INCLUDE_ITEMS AS ( 
SELECT * 
  
		FROM VW_FINISHEDGOODS_ITEMS FGI 
  
		JOIN EIX AS EIX1 
			ON EIX1 . IXPROD = ITM_NUMBER 
			AND EIX1 . IXCUST = IN_CUSTNO 
			AND ( EIX1 . IXSHIP = 0 OR EIX1 . IXSHIP = IN_SHIPTO )  -- either customer level or ship-to level included items 
			AND EIX1 . IXEXCL = '1'  -- included items only 
			AND EIX1 . IXID = 'IX'	 -- active EIX record 
		WHERE 
			EIX1 . IXPROD IS NOT NULL  -- only items IN EIX 
	) 
  
	SELECT DISTINCT 
		ITM_NUMBER , 
		ITM_DESC , 
		ITM_BRAND , 
		BRAND_DESC , 
		ITM_SIZE , 
		SIZE_DESC , 
		ITM_PRICE , 
		ITM_UOM , 
		ITM_UOM_CONVERSION , 
		ITM_PACKAGE_CODE , 
		ITM_TYPE , 
		ITM_CLASS 
  
	FROM INCLUDE_ITEMS 
	LEFT JOIN EIX AS EIX2 
		ON EIX2 . IXPROD = ITM_NUMBER 
		AND EIX2 . IXCUST = IN_CUSTNO 
		AND EIX2 . IXSHIP = IN_SHIPTO  -- check for excluded items at ship-to level 
		AND EIX2 . IXEXCL = '0'	 -- excluded items only 
		AND EIX2 . IXID = 'IX'	 -- active EIX record 
	LEFT JOIN CIC  -- To filter out "Pending" status items 
		ON ICPROD = ITM_NUMBER 
		AND ICFAC = WK_FACILITY 
  
	WHERE EIX2 . IXPROD IS NULL  -- all include_items, minus the excluded ones for this ship-to 
	AND TRIM ( IFNULL ( ICPLC , '' ) ) IN ( '' , 'ACTIVE' , 'WATCH' )  -- Filter out "Pending" status items 
	ORDER BY ITM_DESC ; 
  
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC FUNCTION UDTF_GET_ITEMAUTH 
TO JVALANCE ; 
  
;
