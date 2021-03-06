DROP TABLE VGFILES/WEBEALOG;

CREATE TABLE VGFILES/WEBEALOG (
	EA_ACCOUNTNO FOR COLUMN EAACCT     DECIMAL(7, 0) DEFAULT NULL ,
	EA_DATETIME FOR COLUMN EATIME     TIMESTAMP DEFAULT NULL ,
	EA_IPADDRESS FOR COLUMN EAIPADDR   CHAR(15) CCSID 37 DEFAULT NULL ,
	EA_SCRIPTNAME FOR COLUMN EASCRIPT   VARCHAR(100) CCSID 37 DEFAULT NULL ,
	EA_SESSIONID FOR COLUMN EASESSID   VARCHAR(50) CCSID 37 DEFAULT NULL ,
    EA_EMAIL FOR COLUMN EAEMAIL VARCHAR(40) CCSID 37 DEFAULT NULL,
    EA_FIRSTNAME FOR COLUMN EAFIRST VARCHAR(40) CCSID 37 DEFAULT NULL,
    EA_LASTNAME FOR COLUMN EALAST VARCHAR(40) CCSID 37 DEFAULT NULL,
    EA_STREETADDRESS FOR COLUMN EASTREET VARCHAR(125) CCSID 37 DEFAULT NULL,
    EA_CITYTOWN FOR COLUMN EACITY VARCHAR(40) CCSID 37 DEFAULT NULL,
    EA_STATE FOR COLUMN EASTATE VARCHAR(2) CCSID 37 DEFAULT NULL,
    EA_ZIP FOR COLUMN EAZIP VARCHAR(10) CCSID 37 DEFAULT NULL,
    EA_PHONE FOR COLUMN EAPHONE VARCHAR(15) CCSID 37 DEFAULT NULL,
	EA_PREMISE FOR COLUMN EAPREMISE  CHAR(15) CCSID 37 DEFAULT NULL ,
    EA_YEARBUILT FOR COLUMN EAHOMEYEAR DECIMAL(4,0)DEFAULT NULL,
    EA_AGEOFHOME FOR COLUMN EAAGEHOME DECIMAL(3,0) DEFAULT NULL,
    EA_CCFS12MOS FOR COLUMN EA12MONUSG DECIMAL(7,0) DEFAULT NULL,
    EA_SQUAREFOOTAGE FOR COLUMN EASQFEET DECIMAL(5,0) DEFAULT NULL,
    EA_BESTTIMETOCONTACT FOR COLUMN EABESTTIME VARCHAR(500) CCSID 37 DEFAULT NULL
) ;

LABEL ON TABLE VGFILES/WEBEALOG
	IS 'Web energy audit request log' ;

LABEL ON COLUMN VGFILES/WEBEALOG
( 	
	EA_ACCOUNTNO TEXT IS 'Account Number' ,
	EA_DATETIME TEXT IS 'Record Created Timestamp' ,
	EA_IPADDRESS TEXT IS 'Remote IP Address' ,
	EA_SCRIPTNAME TEXT IS 'PHP Script Name' ,
	EA_SESSIONID TEXT IS 'Session ID',
    EA_EMAIL text is 'Email Address',
    EA_FIRSTNAME text is 'First Name',
    EA_LASTNAME text is 'Last Name',
    EA_STREETADDRESS text is 'Street Address',
    EA_CITYTOWN text is 'City/Town',
    EA_STATE text is 'State',
    EA_ZIP text is 'Zip Code',
    EA_PHONE text is 'Phone#',
    EA_PREMISE text is 'Premise Number',
    EA_YEARBUILT text is 'Year Built',
    EA_AGEOFHOME text is 'Age of Home',
    EA_CCFS12MOS text is 'CCFs used past 12 months',
    EA_SQUAREFOOTAGE text is 'Square footage of home',
    EA_BESTTIMETOCONTACT text is 'Best time to contact'
) ;

