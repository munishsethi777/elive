<?php
abstract class Enumeration
{
    public static function enum() 
    {
        $reflect = new ReflectionClass( get_called_class() );
        return $reflect->getConstants();
    }
}
class ParserType extends Enumeration {
    const APPCP_HYD = 'ParserAppcbHyd';
    const DUPLICATE_AQMS = 'ParserDuplicateAQMS';
    const WADAPPCB = 'ParserWADAPPCB';
    const LSI = 'ParserStackLsi';
    const BHOOMI = 'ParserBhoomiFiles';
    const WQD = 'ParserWQD';
    const BHOOMI_M2M_CSV = 'ParserBoomiM2MCSV';
    const M2M_CSV_SCALED = 'ParserScaledM2MCSV';
    const RAVE_AQMS = 'ParserRaveAQMS';
    const ENVIDAS = 'ParserEnvidas';
}
?>
