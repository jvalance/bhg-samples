
DROP TABLE JVALANCE/SRVYBATCH;

CREATE TABLE JVALANCE/SRVYBATCH (
	SB_BATCHNO FOR COLUMN BATCHNO    INTEGER GENERATED ALWAYS AS IDENTITY (
	START WITH 1 INCREMENT BY 1
	NO MINVALUE NO MAXVALUE
	NO CYCLE NO ORDER
	CACHE 20 )
	,
	SB_RUNDATE FOR COLUMN SBRUNDATE  DATE DEFAULT NULL ,
	SB_RUNTIME FOR COLUMN SBRUNTIME  TIME DEFAULT NULL ,
	SB_COUNT FOR COLUMN SBCOUNT    INTEGER DEFAULT NULL ,
	SB_CMCOUNT FOR COLUMN SBCMCOUNT  INTEGER DEFAULT NULL ,
	SB_SOCOUNT FOR COLUMN SBSOCOUNT  INTEGER DEFAULT NULL ,
	SB_CUSTCOUNT FOR COLUMN SBVGSCOUNT  INTEGER DEFAULT NULL ,
	SB_VGSCOUNT FOR COLUMN SBCUSCOUNT  INTEGER DEFAULT NULL ,
	SB_STATUS FOR COLUMN SBSTATUS   CHAR(6) CCSID 37 DEFAULT NULL ,
	SB_EST_RESPONSES_VGS FOR COLUMN VGSRESP    INTEGER NOT NULL DEFAULT 0 ,
	SB_EST_RESPONSES_CUST FOR COLUMN CUSTRESP   INTEGER NOT NULL DEFAULT 0 ,
	SB_EST_RESPONSE_RATE FOR COLUMN RESPRATE   DECIMAL(2, 2) DEFAULT NULL ,
	SB_DROPS_PER_QUARTER FOR COLUMN DROPSQTR   DECIMAL(3, 0) DEFAULT NULL ,
	SB_STARTDATE FOR COLUMN STARTDATE  DATE DEFAULT NULL ,
	SB_ENDDATE FOR COLUMN ENDDATE    DATE DEFAULT NULL
) ;

ALTER TABLE JVALANCE/SRVYBATCH
	ADD CONSTRAINT JVALANCE/SRVYBATCHL1
	UNIQUE( SB_BATCHNO ) ;

LABEL ON TABLE JVALANCE/SRVYBATCH
	IS 'Survey batch header' ;

LABEL ON COLUMN JVALANCE/SRVYBATCH
( SB_BATCHNO IS 'Survey              batch#' ,
	SB_RUNDATE IS 'Survey batch        run date' ,
	SB_RUNTIME IS 'Survey batch        run time' ,
	SB_COUNT IS 'Survey batch        record              count' ,
	SB_CMCOUNT IS   'Survey batch        contact record      count' ,
	SB_SOCOUNT IS   'Survey batch        service record      count' ,
	SB_CUSTCOUNT IS 'Survey batch        Cust initiated      count' ,
	SB_VGSCOUNT  IS 'Survey batch        VGS initiated       count' ,
	SB_STATUS    IS 'Survey              batch               status' ,
	SB_EST_RESPONSES_VGS IS 'Anticipated         Responses           VGS' ,
	SB_EST_RESPONSES_CUST IS 'Anticipated         Responses           Cust' ,
	SB_EST_RESPONSE_RATE IS 'Est. Resp.          Rate' ,
	SB_DROPS_PER_QUARTER IS 'Drops per           Quarter' ,
	SB_STARTDATE IS 'Start               Date' ,
	SB_ENDDATE IS 'End                 Date'
) ;

LABEL ON COLUMN JVALANCE/SRVYBATCH
( SB_BATCHNO TEXT IS 'Survey batch number' ,
	SB_RUNDATE TEXT IS 'Survey batch run date' ,
	SB_RUNTIME TEXT IS 'Survey batch run time' ,
	SB_COUNT TEXT IS 'Survey batch record count' ,
	SB_CMCOUNT TEXT IS 'Survey batch contact record count' ,
	SB_SOCOUNT TEXT IS 'Survey batch service record count' ,
	SB_CUSTCOUNT TEXT IS 'Survey batch customer initiated count' ,
	SB_VGSCOUNT TEXT IS 'Survey batch VGS initiated count' ,
	SB_STATUS TEXT IS 'Survey batch process status' ,
	SB_EST_RESPONSES_VGS TEXT IS 'Anticipated responses - VGS initiated contacts' ,
	SB_EST_RESPONSES_CUST TEXT IS 'Anticipated responses - CUST initiated contacts' ,
	SB_EST_RESPONSE_RATE TEXT IS 'Estimated response rate' ,
	SB_DROPS_PER_QUARTER TEXT IS 'Mail drops per quarter' ,
	SB_STARTDATE TEXT IS 'Selection Start date' ,
	SB_ENDDATE TEXT IS 'Selection end date'
) ;

