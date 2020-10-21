drop procedure VGCUSTOM/spWriteWebUseLog;
--@#
create procedure VGCUSTOM/spWriteWebUseLog
(
	in inAcctNo char(7),
	in inIPAddress char(15),
	in inScriptName varchar(100),
	in inBrowser varchar(1000),
	in inSessionID varchar(50),
	in inViewYear char(4),
	in inLogin char(1)
)
result sets 0
language sql

begin
    INSERT INTO WebUseLog (
        WBACCOUNTNO,
        WBDATETIME,
        WBIPADDRESS,
        WBSCRIPTNAME,
        WBBROWSER,
        WBSESSIONID,
        WBVIEWYEAR,
        WBLOGIN)
    VALUES (
    	inAcctNo,
	    current timestamp,
	    inIPAddress,
    	inScriptName,
	    inBrowser,
	    inSessionID,
	    inViewYear,
	    inLogin)
	;
end
