drop procedure VGCUSTOM/spGetLastPmt;
--@#
create procedure VGCUSTOM/spGetLastPmt
(
	in inAcctNo char(7)
)
result sets 1
language sql

begin
	
	declare c1 cursor with return for

	SELECT  		PDPDT as PmtDate,
					sum(PDAM1) as PmtAmount
						
	FROM			UPHSD

	WHERE 			PDACT = inAcctNo
	AND             PDLTP in (20, 22, 150, 155)  -- Ledger types for payments only
	                -- Get last payment date
	AND 			PDPDT = (select max(pdpdt) from uphsd where pdact = inAcctNo
                            	AND PDLTP in (20, 22, 150, 155))
	GROUP BY		PDPDT
	;

	open c1;
end
