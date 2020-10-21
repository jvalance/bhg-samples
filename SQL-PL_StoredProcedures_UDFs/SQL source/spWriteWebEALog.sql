drop procedure VGCUSTOM/spWriteWebEALog;
--@#
create procedure VGCUSTOM/spWriteWebEALog
(
	in_ACCOUNTNO DECIMAL(7, 0),
	in_IPADDRESS  CHAR(15),
	in_SCRIPTNAME VARCHAR(100),
	in_SESSIONID VARCHAR(50),
    in_EMAIL VARCHAR(40),
    in_FIRSTNAME VARCHAR(40),
    in_LASTNAME VARCHAR(40),
    in_STREETADDRESS VARCHAR(125),
    in_CITYTOWN VARCHAR(40),
    in_STATE VARCHAR(2),
    in_ZIP VARCHAR(10),
    in_PHONE VARCHAR(15),
    in_YEARBUILT  DECIMAL(4,0),
    in_AGEOFHOME DECIMAL(3,0),
    in_CCFS12MOS VARCHAR(25),
    in_SQUAREFOOTAGE DECIMAL(5,0),
    in_BESTTIMETOCONTACT VARCHAR(500)

)
result sets 0
language sql

begin
    declare wkPremise char(15);

    select ifnull(umprm, '')
    into wkPremise
    from uact
    where umact = in_ACCOUNTNO;

    INSERT INTO WebEALog (
    	EA_ACCOUNTNO,
        EA_DATETIME,
    	EA_IPADDRESS,
    	EA_SCRIPTNAME,
	    EA_SESSIONID,
        EA_EMAIL,
        EA_FIRSTNAME,
        EA_LASTNAME,
        EA_STREETADDRESS,
        EA_CITYTOWN,
        EA_STATE,
        EA_ZIP,
        EA_PHONE,
        EA_PREMISE,
        EA_YEARBUILT,
        EA_AGEOFHOME,
        EA_CCFS12MOS,
        EA_SQUAREFOOTAGE,
        EA_BESTTIMETOCONTACT
    ) VALUES (
    	in_ACCOUNTNO,
        current timestamp,
    	in_IPADDRESS,
    	in_SCRIPTNAME,
	    in_SESSIONID,
        in_EMAIL,
        in_FIRSTNAME,
        in_LASTNAME,
        in_STREETADDRESS,
        in_CITYTOWN,
        in_STATE,
        in_ZIP,
        in_PHONE,
        wkPremise,
        in_YEARBUILT,
        in_AGEOFHOME,
        in_CCFS12MOS,
        in_SQUAREFOOTAGE,
        in_BESTTIMETOCONTACT
    );

    update slsapp
    set slssqf = in_SQUAREFOOTAGE
    where slsbk$ = wkPremise;

end
