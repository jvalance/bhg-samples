
--ALTER TABLE VGFILES/SRVYCNVDET
--DROP UNIQUE VGFILES/SRVYCNVDET_UNQSEQ RESTRICT;

--DROP TABLE VGFILES/SRVYCNVDET;
-- CL: CHGCURLIB VGFILES;

CREATE TABLE VGFILES/SRVYCNVDET (
	CD_SEQUENCE INTEGER GENERATED ALWAYS AS IDENTITY (
	START WITH 1 INCREMENT BY 1
	NO MINVALUE NO MAXVALUE
	NO CYCLE NO ORDER
	CACHE 20 )
	,
	CD_BATCHNO FOR COLUMN CDBATCHNO DECIMAL(5, 0) DEFAULT NULL ,
	CD_RUNDATE FOR COLUMN CDRUNDATE DATE DEFAULT NULL ,
	CD_RUNTIME FOR COLUMN CDRUNTIME TIME DEFAULT NULL ,
	CD_TURNON_DATE FOR COLUMN CDTURNON DECIMAL(8, 0) DEFAULT NULL ,
	CD_ACCTNO DECIMAL(7, 0) DEFAULT NULL ,
	CD_LASTNAME FOR COLUMN CDLASTNAME CHAR(30) CCSID 37 DEFAULT NULL ,
	CD_FIRSTNAME FOR COLUMN CDFRSTNAME CHAR(20) CCSID 37 DEFAULT NULL ,
	CD_ADDRESS1 FOR COLUMN CDADDR1 CHAR(35) CCSID 37 DEFAULT NULL ,
	CD_ADDRESS2 FOR COLUMN CDADDR2 CHAR(35) CCSID 37 DEFAULT NULL ,
	CD_ADDRESS3 FOR COLUMN CDADDR3 CHAR(35) CCSID 37 DEFAULT NULL ,
	CD_ZIPCODE FOR COLUMN CDZIP CHAR(9) CCSID 37 DEFAULT NULL ,
	CD_PREMISE FOR COLUMN CDPREMISE CHAR(15) CCSID 37 DEFAULT NULL ,
	CD_PREM_ADDR FOR COLUMN CDPREMADDR VARCHAR(100) CCSID 37 DEFAULT NULL ,
	CD_COMPOSITE_CODE FOR COLUMN CDCOMPCODE   CHAR(30) CCSID 37 DEFAULT NULL,
	CD_RESPONSE_DATE FOR COLUMN CDRESPDATE TIMESTAMP DEFAULT NULL
) ;


ALTER TABLE VGFILES/SRVYCNVDET
	ADD CONSTRAINT VGFILES/SRVYCNVDET_UNQSEQ
	UNIQUE( CD_SEQUENCE ) ;

LABEL ON COLUMN VGFILES/SRVYCNVDET
(   CD_SEQUENCE       IS 'Survey record       number' ,
	CD_BATCHNO        IS 'Survey              batch#' ,
	CD_RUNDATE        IS 'Date                created' ,
	CD_COMPOSITE_CODE IS 'Composite           code',
	CD_RESPONSE_DATE IS 'Response entry      date/time'
) ;

LABEL ON COLUMN VGFILES/SRVYCNVDET
( CD_SEQUENCE TEXT IS 'Unique sequence ID' ,
	CD_TURNON_DATE TEXT IS 'Turn on date' ,
	CD_ACCTNO TEXT IS 'Account number' ,
	CD_LASTNAME TEXT IS 'Last name' ,
	CD_FIRSTNAME TEXT IS 'First name' ,
	CD_ADDRESS1 TEXT IS 'Address 1' ,
	CD_ADDRESS2 TEXT IS 'Address 2' ,
	CD_ADDRESS3 TEXT IS 'Address 3' ,
	CD_ZIPCODE TEXT IS 'Zip code' ,
	CD_BATCHNO TEXT IS 'Survey batch number' ,
	CD_RUNDATE TEXT IS 'Date record created' ,
	CD_RUNTIME TEXT IS 'Time record created' ,
	CD_COMPOSITE_CODE TEXT IS 'Composite code',
	CD_PREMISE text is 'Premise Number',
	CD_PREM_ADDR text is 'Premise Address',
	CD_RESPONSE_DATE TEXT IS 'Response entry date/time'
) ;

