<?php

namespace LorenzoGiust\LaravelGoogleMapsAPI;

use LorenzoGiust\GeoSpatial\Exceptions\GeoException;
use LorenzoGiust\GeoSpatial\LineString;
use LorenzoGiust\GeoSpatial\Point;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testPointSuccess()
    {
        $points = [];
        $points[] = new Point(1.234, 2.345);
        $points[] = new Point([1.234, 2.345]);
        $points[] = new Point("1.234, 2.345");
        $points[] = new Point("1.234 2.345", [" "]);
        $points[] = new Point("1.234", "2.345");

        foreach($points as $point){
            print_r($point);
            if( get_class($point) != 'LorenzoGiust\GeoSpatial\Point' || $point->lat != 1.234 || $point->lon != 2.345)
                throw new \Exception('Error instantiating Points');
        }
    }

    public function testLinestringSuccess()
    {
        $linestrings = [];
        $points = [];
        $points[] = new Point(1, 2);
        $points[] = new Point(3, 4);
        $points[] = new Point(5, 6);

        $ls = new LineString($points);

        if( get_class($ls) != 'LorenzoGiust\GeoSpatial\LineString' )
            throw new GeoException("Error instantianting Linestring");



        $points[] = new Point([1.234, 2.345]);
        $points[] = new Point("1.234, 2.345");
        $points[] = new Point("1.234 2.345", [" "]);
        $points[] = new Point("1.234", "2.345");

        foreach($points as $point){
            print_r($point);
            if($point->lat != 1.234 || $point->lon != 2.345)
                throw new \Exception('Error instantiating Points');
        }
    }
}