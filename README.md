Very Simple PHP METAR Decoder
=======
### Usage Example

    <?php
    require_once 'Metar.php';
    
    $metar = new Metar('EDDL'); // change to your stationcode
    $metar->fetchMetarData();
    
    echo $metar->getMetarTime() . '<br>';
    echo $metar->getMetarString() . '<br>';

    foreach ($metar->getMetarDecoded() as $name => $value) {
        echo '<b>' . $name . ':</b> ' . $value . '<br>';
    }
    ?>
    
### Example Output
    2014-12-28 19:50:00
    EDDL 281950Z 26004KT 130V250 9999 BKN031 M03/M04 Q1033 NOSIG
    time: 19:50 UTC
    wind: 260° 7.4 km/h variable
    visibility: > 10 km
    clouds: 5/8-7/8 3100 ft 
    temperature: -3 °C
    qnh: 1033 hPa
