drop procedure VGCUSTOM/spGetCustomerSalesStatistic;
--@#
create procedure VGCUSTOM/spGetCustomerSalesStatistic(
in @inPeriod dec(6,0)
)
result sets 1
language sql

begin
    declare c1 cursor with return for

     SELECT     prm.UPSAD as "Service Address",
                prm.UPCTC as "Service City",
                act.UMNAM as "Acct Name",
                bh.BHYRVC as "Revenue Code",
                bh.BHYRYM as "Revenue Period",
                bh.BHYAMT as "Charge Amount",
                bh.BHYSCH as "Rate Schedule",
                bh.BHYACT as "Account No.",
                bh.BHYUSE as "Charge Usage",
                bh.BHYBLD as "Billing Date",
                bh.BHYPRM as "Premise Code",
                rvc.RVGLN as "G/L Number",
                substring(rvc.RVGLN, 1, 1) as Company,
                substring(rvc.RVGLN, 6, 4) as SubAcct,
                rvc.RVBDPT as "Budget Dept."

    FROM        UBLHUDDVG bh

    LEFT JOIN   UACT  act
    ON          bh.BHYACT = act.UMACT

    LEFT JOIN   UPRM  prm
    ON          bh.BHYPRM = prm.UPPRM

    JOIN        URVC  rvc
    ON          bh.BHYRVC = rvc.RVRVC

    LEFT JOIN   UBLHMC misc
    ON          bh.BHYRYM = misc.BHMAYM
    AND         bh.BHYRVC = misc.BHMRVC

    WHERE       bh.BHYRYM = @inPeriod
    AND         (bh.BHYRVC='*AWO'
                OR  bh.BHYRVC='*GREV'
                OR bh.BHYRVC='ESTG1'
                OR bh.BHYRVC='ESTG2'
                OR bh.BHYRVC='ESTG3'
                OR bh.BHYRVC='ESTG4'
                OR bh.BHYRVC='ESTR'
                OR bh.BHYRVC='G1ACS'
                OR bh.BHYRVC='G1D'
                OR bh.BHYRVC='G1GAS'
                OR bh.BHYRVC='G2ACS'
                OR bh.BHYRVC='G2D'
                OR bh.BHYRVC='G2GAS'
                OR bh.BHYRVC='G3ACS'
                OR bh.BHYRVC='G3D'
                OR bh.BHYRVC='G3GAS'
                OR bh.BHYRVC='G4ACS'
                OR bh.BHYRVC='G4D'
                OR bh.BHYRVC='G4GAS'
                OR bh.BHYRVC='GEOF'
                OR bh.BHYRVC='RSAC'
                OR bh.BHYRVC='RSD'
                OR bh.BHYRVC='RSGAS')
    AND         (bh.BHYSCH='R'
                OR bh.BHYSCH='RE'
                OR bh.BHYSCH='G1'
                OR bh.BHYSCH='G2'
                OR bh.BHYSCH='G3'
                OR bh.BHYSCH='G4'
                OR bh.BHYSCH='G4M'
                OR bh.BHYSCH='Z')
    ;

    open c1;
end