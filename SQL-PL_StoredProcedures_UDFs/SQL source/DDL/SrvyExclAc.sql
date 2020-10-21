--  Generate SQL
--  Version:                   	V5R3M0 040528
--  Generated on:              	04/11/08 12:14:25
--  Relational Database:       	S650994F
--  Standards Option:          	DB2 UDB iSeries

CREATE TABLE JVALANCE/SRVYEXCLAC (
	SRVYEXCLACCTNO FOR COLUMN EXCLACCTNO DECIMAL(7, 0) NOT NULL ) ;

ALTER TABLE JVALANCE/SRVYEXCLAC
	ADD CONSTRAINT JVALANCE/SRVYEXCLACUNQ
	UNIQUE( SRVYEXCLACCTNO ) ;

LABEL ON TABLE JVALANCE/SRVYEXCLAC
	IS 'Accounts to be excluded from surveys' ;

LABEL ON COLUMN JVALANCE/SRVYEXCLAC
( SRVYEXCLACCTNO IS 'Excluded            Acct No' ) ;

LABEL ON COLUMN JVALANCE/SRVYEXCLAC
( SRVYEXCLACCTNO TEXT IS 'Exclude Acct No from Surveys' ) ;

/*
-- This was originally created to exclude Champlain Valley Weatherization
-- from all survey mailings.
select * from uact where upper(UMNAM) like '%WEATHERIZATION%';
insert into jvalance/srvyexclac values(93842	);
insert into jvalance/srvyexclac values(101884	);
insert into jvalance/srvyexclac values(102861	);
insert into jvalance/srvyexclac values(105898	);
insert into jvalance/srvyexclac values(105973	);
insert into jvalance/srvyexclac values(107057	);
insert into jvalance/srvyexclac values(113746	);
insert into jvalance/srvyexclac values(124557	);
insert into jvalance/srvyexclac values(124664	);
insert into jvalance/srvyexclac values(125221	);
insert into jvalance/srvyexclac values(127243	);
insert into jvalance/srvyexclac values(128383	);
insert into jvalance/srvyexclac values(131571	);
insert into jvalance/srvyexclac values(144711	);
insert into jvalance/srvyexclac values(145241	);
*/