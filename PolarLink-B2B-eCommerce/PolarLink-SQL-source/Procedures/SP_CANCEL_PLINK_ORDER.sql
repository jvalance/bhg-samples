SET PATH *LIBL ;

CREATE OR REPLACE PROCEDURE SP_CANCEL_PLINK_ORDER ( 
	IN IN_ORDERNUM DECIMAL(8, 0) , 
	IN IN_ACTION CHAR(4) , 
	OUT OUT_RESULT CHAR(1) , 
	OUT OUT_MESSAGE VARCHAR(100) ) 
	LANGUAGE SQL 
	SPECIFIC SP_CANCEL_PLINK_ORDER 
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
  
Handle cancel action from Polar Link ordering pages. 
When user clicks the "Cancel" button from within the PolarLink ordering screens,  
they will have options as follows: 
	1. Save current order for later:  
		This will update order header status (field PLINK_STATUS) to 'CURR' for current order.    
	2. Cancel current order: 
		This will update order header status (field PLINK_STATUS) to 'CNCL', 
		which will render it invisible to the PolarLink system and no further processing of  
		this order will occur.    
  
Input Paramters: 
	in_OrderNum: Order Number to save or cancel. 
	in_action; Action to perform, either SAVE or CNCL 
  
Returns:  
	OUT out_result: 1 = Success; 0 = Failure 
	OUT out_message: Either 'Success' or an error message on failure. 
	 
=================================================================================*/ 
	 
	SET OUT_RESULT = '1' ;  -- default to 1 = Success 
	SET OUT_MESSAGE = '' ; 
	 
	IF IN_ACTION = 'SAVE' THEN 
		UPDATE ECH_PLNK SET PLINK_STATUS = 'CURR' WHERE HORD = IN_ORDERNUM ; 
		RETURN ; 
	END IF ; 
	 
	IF IN_ACTION = 'CNCL' THEN 
		UPDATE ECH_PLNK SET PLINK_STATUS = 'CNCL' WHERE HORD = IN_ORDERNUM ; 
		RETURN ; 
	END IF ; 
	 
END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC PROCEDURE SP_CANCEL_PLINK_ORDER 
TO JVALANCE ; 
  
;
