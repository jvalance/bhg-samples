drop function VGCUSTOM/fnPadLeft(varchar(2500), integer, varchar(1));
--@#
create function VGCUSTOM/fnPadLeft(
	inString varchar(2500),
	inResultLen integer,
	inPadChar varchar(1))
returns varchar(2500)
language sql

begin
    declare resultStr varchar(2500);
    declare stringLen integer;

    set stringLen = length(trim(inString));
    if (inResultLen > stringLen) then
        set resultStr = repeat(inPadChar, (inResultLen - stringLen)) || trim(inString);
    else
        set resultStr = trim(inString);
    end if;

    return resultStr;
end;

--=======================================================
-- Create default signature of fnPadLeft with
-- blank as the pad character
--=======================================================
drop function VGCUSTOM/fnPadLeft(varchar(2500), integer);
--@#
create function VGCUSTOM/fnPadLeft(
	inString varchar(2500),
	inResultLen integer)
returns varchar(2500)
language sql
begin
    return fnPadLeft(inString, inResultLen, ' ');
end;