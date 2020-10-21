--  Generate SQL 
--  Version:                   	V5R3M0 040528 
--  Generated on:              	04/10/08 16:28:39 
--  Relational Database:       	S650994F 
--  Standards Option:          	DB2 UDB iSeries 
  
CREATE TABLE VGFILES/SRVYCMSAMP (
	UMACT DECIMAL(7, 0) NOT NULL DEFAULT 0 , 
	CMTYPE CHAR(5) CCSID 37 NOT NULL DEFAULT '' , 
	CMCLD DECIMAL(8, 0) NOT NULL DEFAULT 0 , 
	UMLNM CHAR(30) CCSID 37 NOT NULL DEFAULT '' , 
	UMFNM CHAR(20) CCSID 37 NOT NULL DEFAULT '' , 
	UMAD1 CHAR(35) CCSID 37 NOT NULL DEFAULT '' , 
	UMAD2 CHAR(35) CCSID 37 NOT NULL DEFAULT '' , 
	UMAD3 CHAR(35) CCSID 37 NOT NULL DEFAULT '' , 
	UMZIP CHAR(9) CCSID 37 NOT NULL DEFAULT '' ,
        SWCRW CHAR(5) CCSID 37 NOT NULL DEFAULT'') ;
  
LABEL ON COLUMN VGFILES/SRVYCMSAMP 
  ( UMACT IS 'Account             Number' , 
	CMTYPE IS 'Contact Type       Code' , 
	CMCLD IS 'Completed           Date' , 
	UMLNM IS 'Last                Name' , 
	UMFNM IS 'First               Name' , 
	UMAD1 IS 'Mailing Address     Line 1' , 
	UMAD2 IS 'Mailing Address     Line 2' , 
	UMAD3 IS 'Mailing Address     Line 3' , 
	UMZIP IS 'Mailing             Zip Code' ,
    SWCRW IS 'CSR ID              Entered By') ;

LABEL ON COLUMN VGFILES/SRVYCMSAMP
( UMACT TEXT IS 'Account Number' , 
	CMTYPE TEXT IS 'Contact Type Code' , 
	CMCLD TEXT IS 'Completed Date' , 
	UMLNM TEXT IS 'Last Name' , 
	UMFNM TEXT IS 'First Name' , 
	UMAD1 TEXT IS 'Mailing Address Line 1' , 
	UMAD2 TEXT IS 'Mailing Address Line 2' , 
	UMAD3 TEXT IS 'Mailing Address Line 3' , 
	UMZIP TEXT IS 'Mailing Zip Code',
        SWCRW TEXT IS 'CSR ID Entered By') ;
  
