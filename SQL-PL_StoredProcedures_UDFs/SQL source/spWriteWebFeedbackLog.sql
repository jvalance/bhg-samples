drop procedure VGCUSTOM/spWriteWebFeedbackLog;
--@#
create procedure VGCUSTOM/spWriteWebFeedbackLog
(
	in inAccountNo DECIMAL(7, 0),
	in inName  VARCHAR(50),
	in inSubject  VARCHAR(50),
	in inEmailAddr VARCHAR(100),
	in inComments VARCHAR(5000)
)
result sets 0
language sql

begin
    declare currEmail varchar(100);

    INSERT INTO WebFeedbackLog (
    	wfbAccountNo,
	    wfbTime,
    	wfbName,
    	wfbSubject,
	    wfbEmailAddr,
    	wfbComments
    )
    VALUES (
    	inAccountNo,
	    current timestamp,
    	inName,
    	inSubject,
	    inEmailAddr,
    	inComments
	);
	
	-- Update customer's email address on UAPI (Additional Phone Information)
	
	-- Finf out if record already exists
	SELECT APFXA2
	INTO currEmail
	FROM UAPI
	WHERE APACT = inAccountNo;
	
	IF currEmail IS NULL THEN
	    -- Record not found - insert new record
	    INSERT INTO UAPI (
	        apact,
	        apfxa2,
	        apcus
	    ) VALUES (
	        inAccountNo,
	        inEmailAddr,
	        (SELECT umcus FROM uact WHERE umact = inAccountNo)
	    );
	ELSE
	    -- Record found - update existing record
    	UPDATE UAPI
    	SET APFXA2 = inEmailAddr
    	WHERE APACT = inAccountNo;
    END IF;
	
end
