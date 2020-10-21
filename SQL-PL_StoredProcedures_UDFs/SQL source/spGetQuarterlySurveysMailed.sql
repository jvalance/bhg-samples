DROP PROCEDURE VGCUSTOM/spGetQuarterlySurveysMailed;
--@#
CREATE PROCEDURE VGCUSTOM/spGetQuarterlySurveysMailed(
	in inFromDate char(8),
	in inToDate char(8)
)
RESULT SETS 1
LANGUAGE SQL

BEGIN
	DECLARE c1 CURSOR WITH RETURN FOR
	
    select dec((qstmal / 10000),4,0) as survey_year,
        case
            when substr(digits(qstmal),5,2) between '01' and '03'
            then digits(dec(qstmal / 10000,4,0)) || 'Q1'
            when substr(digits(qstmal),5,2) between '04' and '06'
            then digits(dec(qstmal / 10000,4,0)) || 'Q2'
            when substr(digits(qstmal),5,2) between '07' and '09'
            then digits(dec(qstmal / 10000,4,0)) || 'Q3'
            when substr(digits(qstmal),5,2) between '10' and '12'
            then digits(dec(qstmal / 10000,4,0)) || 'Q4'
            else digits(dec(qstmal / 10000,4,0)) || '??'
        end as quarter,
        q1.*
    from quaire q1
    where qstmal between inFromDate and inToDate
    ;	

	OPEN c1;
END

