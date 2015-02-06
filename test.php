<?php
/**
 * Created by PhpStorm.
 * User: Chris
 */

require_once 'Metar.php';

$metar = new Metar('EDDL'); // change to your stationcode
$metar->fetchMetarData();

echo $metar->getMetarTime() . '<br>';
echo $metar->getMetarString() . '<br>';

foreach ($metar->getMetarDecoded() as $name => $value) {
    echo '<b>' . $name . ':</b> ' . $value . '<br>';
}


/*
// test the decoder with some metar strings

$metarStringArray = array(
    'EDDL 091920Z 17011KT 9999 SCT015 BKN130 04/01 Q1027 NOSIG',
    'EDAC 091650Z 29010G30KT CAVOK M01/M01 Q1029',
    'EDAH 091550Z VRB03KT 9999 SCT017 03/M00 Q1027',
    'EDBC 091650Z 19003KT 9999 SCT027 01/M01 Q1027',
    'EDDB 091950Z 17008KT CAVOK M02/M02 Q1028 NOSIG',
    'EDDH 091950Z 20012KT 4584 BKN013 03/01 Q1024 BECMG 21015G25KT',
    'EDDP 091950Z 17007KT 9999 FEW030 M02/M02 Q1028 NOSIG',
    'EDLN 091920Z 18005KT 110V220 CAVOK 03/02 Q1027 RMK ATIS A',
    'EEDF 010500Z 16006KT CAVOK 25/18 Q1014 NOSIG'
);

foreach ($metarStringArray as $metarString) {
    echo '<hr>' . $metarString . '<br>';

    foreach ($metar->getMetarDecoded($metarString) as $name => $value) {
        echo '<b>' . $name . ':</b> ' . $value . '<br>';
    }
}
*/
