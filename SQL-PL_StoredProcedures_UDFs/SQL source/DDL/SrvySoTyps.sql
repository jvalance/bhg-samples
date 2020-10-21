--  Generate SQL 
--  Version:                   	V5R3M0 040528 
--  Generated on:              	04/10/08 16:28:00 
--  Relational Database:       	S650994F 
--  Standards Option:          	DB2 UDB iSeries 
  
CREATE TABLE JVALANCE/SRVYSOTYPS ( 
	SURVEYSOTYPE FOR COLUMN SOTYPE     CHAR(5) CCSID 37 DEFAULT NULL , 
	NEWSOTYPE FOR COLUMN SONEW      CHAR(3) CCSID 37 DEFAULT NULL , 
	INITIATEDBY FOR COLUMN INITI00001 CHAR(4) CCSID 37 DEFAULT NULL ) ; 
  
LABEL ON COLUMN JVALANCE/SRVYSOTYPS 
( NEWSOTYPE IS 'New                 code' , 
	INITIATEDBY IS 'Initiated           By' ) ; 
  
LABEL ON COLUMN JVALANCE/SRVYSOTYPS 
( NEWSOTYPE TEXT IS 'New survey code?' , 
	INITIATEDBY TEXT IS 'Initiated By' ) ; 
  
