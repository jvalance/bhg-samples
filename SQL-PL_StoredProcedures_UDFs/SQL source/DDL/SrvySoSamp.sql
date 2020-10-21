  
CREATE TABLE VGFILES/SRVYSOSAMP (
	UMACT DECIMAL(7, 0) NOT NULL DEFAULT 0 , 
	SMSCD CHAR(5) CCSID 37 NOT NULL DEFAULT '' , 
	SMCPD DECIMAL(8, 0) NOT NULL DEFAULT 0 , 
	UMLNM CHAR(30) CCSID 37 NOT NULL DEFAULT '' , 
	UMFNM CHAR(20) CCSID 37 NOT NULL DEFAULT '' , 
	UMAD1 CHAR(35) CCSID 37 NOT NULL DEFAULT '' , 
	UMAD2 CHAR(35) CCSID 37 NOT NULL DEFAULT '' , 
	UMAD3 CHAR(35) CCSID 37 NOT NULL DEFAULT '' , 
	UMZIP CHAR(9) CCSID 37 NOT NULL DEFAULT '' , 
	SMCPB CHAR(15) CCSID 37 NOT NULL DEFAULT '',
        SWCRW CHAR(5) CCSID 37 NOT NULL DEFAULT '' ) ;
  
LABEL ON COLUMN VGFILES/SRVYSOSAMP
( UMACT IS 'Account             Number' , 
	SMSCD IS 'S/O Type            Code' , 
	SMCPD IS 'Completed           Date' , 
	UMLNM IS 'Last                Name' , 
	UMFNM IS 'First               Name' , 
	UMAD1 IS 'Mailing Address     Line 1' , 
	UMAD2 IS 'Mailing Address     Line 2' , 
	UMAD3 IS 'Mailing Address     Line 3' , 
	UMZIP IS 'Mailing             Zip Code' , 
	SMCPB IS 'Completed           By',
    SWCRW IS 'CSR ID Entered By' ) ;
  
LABEL ON COLUMN VGFILES/SRVYSOSAMP
( UMACT TEXT IS 'Account Number' , 
	SMSCD TEXT IS 'S/O Type Code' , 
	SMCPD TEXT IS 'Completed Date' , 
	UMLNM TEXT IS 'Last Name' , 
	UMFNM TEXT IS 'First Name' , 
	UMAD1 TEXT IS 'Mailing Address Line 1' , 
	UMAD2 TEXT IS 'Mailing Address Line 2' , 
	UMAD3 TEXT IS 'Mailing Address Line 3' , 
	UMZIP TEXT IS 'Mailing Zip Code' , 
	SMCPB TEXT IS 'Completed By' ,
    SWCRW TEXT IS 'CSR ID Entered By') ;
