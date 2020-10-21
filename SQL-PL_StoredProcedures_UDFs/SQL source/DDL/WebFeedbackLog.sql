DROP TABLE VGFILES/WebFeedbackLog;
--@#
CREATE TABLE VGFILES/WebFeedbackLog (
	wfbACCOUNTNO FOR COLUMN WFBACCT DECIMAL(7, 0) DEFAULT NULL ,
	wfbTime  TIMESTAMP DEFAULT NULL ,
	wfbName  VARCHAR(50) DEFAULT NULL ,
	wfbSubject  VARCHAR(50) DEFAULT NULL ,
	wfbEmailAddr FOR COLUMN WFBEMAIL  VARCHAR(100) CCSID 37 DEFAULT NULL ,
	wfbComments FOR COLUMN WFBCOMMNTS VARCHAR(5000) CCSID 37 DEFAULT NULL)
 ;
--@#
LABEL ON TABLE VGFILES/WebFeedbackLog
	IS 'Web account access feedback log';
--@#
LABEL ON COLUMN VGFILES/WebFeedbackLog
( WFBACCOUNTNO IS   'Acct                No' ,
	wfbTime IS      'Comment Submit      Date/Time' ,
	wfbName IS      'Name of Person      Submitting Comment' ,
	wfbSubject  IS  'Subject of          Comment' ,
	wfbEmailAddr IS 'Email Address' ,
	wfbComments IS  'Comments' ) ;
--@#
LABEL ON COLUMN VGFILES/WebFeedbackLog
( WFBACCOUNTNO TEXT IS   'Account No.' ,
	wfbTime TEXT IS      'Comment submit Date/Time' ,
	wfbName TEXT IS      'Name of person submitting comment' ,
	wfbSubject  TEXT IS  'Subject of comment' ,
	wfbEmailAddr TEXT IS 'Email address' ,
	wfbComments TEXT IS  'Comments' ) ;
