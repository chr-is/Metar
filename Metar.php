<?php

/**
 * Very Simple PHP METAR Decoder
 *
 * @author Christiaan Kocks
 * @link http://www.kocks.bz
 * @copyright Christiaan Kocks
 * @license MIT
 */
class Metar
{
    /**
     * @var string
     */
    private $stationCode;

    /**
     * @var int
     */
    private $metarTimeStamp;

    /**
     * @var string
     */
    private $metarString;

    /**
     * @param $stationCode
     * @throws Exception
     */
    public function __construct($stationCode = null)
    {
        if (isset($stationCode)) {
            if (!preg_match('/^[a-z]{4}$/i', $stationCode)) {
                throw new Exception('invalid station code ' . $stationCode);
            }

            $this->stationCode = strtoupper($stationCode);
        }
    }

    /**
     * @throws Exception
     */
    public function fetchMetarData()
    {
        $url = 'http://tgftp.nws.noaa.gov/data/observations/metar/stations/' . $this->stationCode . '.TXT';

        $metarData = @file($url);

        if ($metarData === false) {
            throw new Exception('could not fetch metar data for station ' . $this->stationCode);
        }

        if (!isset($metarData[0]) || !isset($metarData[1])) {
            throw new Exception('could not find metar data');
        }

        $this->metarTimeStamp = strtotime(trim($metarData[0]));
        $this->metarString = trim(str_replace('  ', ' ', $metarData[1]));
    }

    /**
     * @return int
     */
    public function getMetarTimeStamp()
    {
        return $this->metarTimeStamp;
    }

    /**
     * @return bool|string
     */
    public function getMetarTime()
    {
        return date('Y-m-d H:i:s', $this->metarTimeStamp);
    }

    /**
     * @return string
     */
    public function getMetarString()
    {
        return $this->metarString;
    }

    /**
     * @param null $metarString
     * @return array
     */
    public function getMetarDecoded($metarString = null)
    {
        $metarString = isset($metarString) ? $metarString : $this->metarString;
        $metarArray = explode(' ', $metarString);

        return array(
            'time' => $this->getTime($metarArray),
            'wind' => $this->getWind($metarArray),
            'visibility' => $this->getVisibility($metarArray),
            'clouds' => $this->getClouds($metarArray),
            'temperature' => $this->getTemperature($metarArray),
            'qnh' => $this->getQnh($metarArray),
        );
    }

    /**
     * @param $metarArray
     * @return null|string
     */
    private function getTime($metarArray)
    {
        if (!isset($metarArray[1])) {
            return null;
        }

        $updateTime = $metarArray[1];
        $updateTime = substr($updateTime, 2, 2) . ':' . substr($updateTime, 4, 2);

        if (preg_match('/^\d\d:\d\d$/', $updateTime)) {
            return $updateTime . ' UTC';
        }

        return null;
    }

    /**
     * @param $metarArray
     * @return null|string
     */
    private function getWind($metarArray)
    {
        if (!isset($metarArray[2])) {
            return null;
        }

        $wind = $metarArray[2];

        if ($wind == '00000KT') {
            return 'calm';
        } elseif (strstr($wind, 'VRB')) {
            if (preg_match('/^VRB([0-9]{2,3})KT$/', $wind, $windParts)) {
                $speed = $windParts[1] * 1.852;

                return 'variable ' . round($speed, 1) . ' km/h';
            }
        } else {
            if (preg_match('/^([0-9]{3})([0-9]{2,3})G?([0-9]{2,3})?KT$/', $wind, $windParts)) {
                $direction = $windParts[1];
                $speed = $windParts[2] * 1.852;

                $wind = $direction . '&deg; ' . round($speed, 1) . ' km/h';

                if (isset($windParts[3])) {
                    $wind .= ' Gusts ' . ($windParts[3] * 1.852) . ' km/h';
                }

                if (isset($metarArray[3]) && preg_match('/^\d\d\dV\d\d\d$/', $metarArray[3])) {
                    $wind .= ' variable';
                }

                return $wind;
            }
        }

        return null;
    }

    /**
     * @param $metarArray
     * @return null|string
     */
    private function getVisibility($metarArray)
    {
        if (in_array('CAVOK', $metarArray)) {
            return '> 10 km';
        }

        if (isset($metarArray[3]) && preg_match('/^\d{4}$/', $metarArray[3])) {
            $visibility = $metarArray[3];
        } elseif (isset($metarArray[4]) && preg_match('/^\d{4}$/', $metarArray[4])) {
            $visibility = $metarArray[4];
        }

        if (isset($visibility)) {
            return $visibility == '9999' ? '> 10 km' : $visibility . ' m';
        }

        return null;
    }

    /**
     * @param $metarArray
     * @return null|string
     */
    private function getClouds($metarArray)
    {
        if (in_array('CAVOK', $metarArray)) {
            return '> 5000 ft';
        }

        $clouds = null;
        $shortCodes = array(
            'FEW' => '1/8-2/8',
            'SCT' => '3/8-4/8',
            'BKN' => '5/8-7/8',
            'OVC' => '8/8',
        );

        foreach ($metarArray as $metarPart) {
            if (preg_match('/^Q\d{3,4}$/', $metarPart)) {
                break;
            }

            if (preg_match('/^(FEW|SCT|BKN|OVC)+(\d{3})/', $metarPart, $cloud)) {
                if (!isset($clouds)) {
                    $clouds = '';
                } else {
                    $clouds .= ' | ';
                }

                $clouds .= $shortCodes[$cloud[1]] . ' ' . (int)$cloud[2] * 100 . ' ft ';
            }
        }

        return $clouds;
    }

    /**
     * @param $metarArray
     * @return null|string
     */
    private function getTemperature($metarArray)
    {
        foreach ($metarArray as $metarPart) {
            if (preg_match('/^(M?\d{2})\/(M?\d{2})$/', $metarPart, $temp)) {
                return (int)str_replace('M', '-', $temp[1]) . ' &deg;C';
            }
        }

        return null;
    }

    /**
     * @param $metarArray
     * @return null|string
     */
    private function getQnh($metarArray)
    {
        foreach ($metarArray as $metarPart) {
            if (preg_match('/^Q(\d{3,4})$/', $metarPart, $qnh)) {
                return $qnh[1] . ' hPa';
            }
        }

        return null;
    }
} 