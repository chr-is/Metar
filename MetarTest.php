<?php
/**
 * Created by PhpStorm.
 * User: Chris
 */

require_once 'Metar.php';

/**
 * Class MetarTest
 */
class MetarTest extends PHPUnit_Framework_TestCase
{
    protected $metar;
    protected $stationCode;

    protected function setUp()
    {
        $this->stationCode = 'EDDL';
        $this->metar = new Metar($this->stationCode);
        $this->metar->fetchMetarData();
    }

    /**
     * @expectedException Exception
     */
    public function testExceptionIsRaisedForInvalidStationCode()
    {
        $metar = new Metar('INVALID');
        $metar->fetchMetarData();
    }

    /**
     * @expectedException Exception
     */
    public function testExceptionIsRaisedForInvalidStationCode2()
    {
        $metar = new Metar('ABCD');
        $metar->fetchMetarData();
    }

    public function testMetar()
    {
        $time = $this->metar->getMetarTime();
        $this->assertStringStartsWith('20', $time);

        $metarString = $this->metar->getMetarString();
        $this->assertStringStartsWith($this->stationCode, $metarString);

        $metarDecoded = $this->metar->getMetarDecoded();
        $this->assertArrayHasKey('time', $metarDecoded);
    }

    public function testMetarDecode()
    {
        $metarDecoded = $this->metar->getMetarDecoded('EDDL 091920Z 17011KT 9999 SCT015 BKN130 04/01 Q1027 NOSIG');

        $this->assertArrayHasKey('time', $metarDecoded);
        $this->assertArrayHasKey('wind', $metarDecoded);
        $this->assertArrayHasKey('clouds', $metarDecoded);
        $this->assertArrayHasKey('temperature', $metarDecoded);
        $this->assertArrayHasKey('qnh', $metarDecoded);
    }

}
 