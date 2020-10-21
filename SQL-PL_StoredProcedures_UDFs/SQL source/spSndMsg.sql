drop procedure VGCUSTOM/spSndMsg;
--@#
create procedure VGCUSTOM/spSndMsg
(
    inToUsr char (10),
    inMsg varchar(5000)
)
result sets 0
language sql

begin
    DECLARE cmd VARCHAR(5100);
    DECLARE cmdLen dec(15,5);

    -- Double-up any single quotes in the message
    set inMsg = replace(inMsg, '''', '''''');

    set cmd = 'SNDMSG TOUSR(' || trim(inToUsr) ||
              ') MSG(''' || inMsg || ''')';
    set cmdLen = length(cmd);

    call qcmdexc(cmd, cmdLen);
end