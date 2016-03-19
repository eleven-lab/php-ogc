<?php

namespace LorenzoGiust\GeoSpatial;

use LorenzoGiust\GeoSpatial\Exceptions\GeoException;
use LorenzoGiust\GeoSpatial\LineString;
use LorenzoGiust\GeoSpatial\Point;
use LorenzoGiust\GeoSpatial\Polygon;

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
            if( get_class($point) != 'LorenzoGiust\GeoSpatial\Point' || $point->lat != 1.234 || $point->lon != 2.345)
                throw new GeoException('Error instantiating Points');
        }
    }

    public function testLinestringSuccess()
    {
        $linestrings = [];

        $p1 = new Point(1, 2);
        $p2 = new Point(3, 4);
        $p3 = new Point(5, 6);

        $linestrings[] = new LineString([$p1, $p2, $p3]);
        $linestrings[] = new LineString("1 2, 3 4, 5 6");
        $linestrings[] = new LineString("1 2: 3 4: 5 6", ":");
        $linestrings[] = new LineString("1_2: 3_4: 5_6", ":", "_");
        $linestrings[] = new LineString([ [1, 2], [2, 3], [3, 4] ]);

        foreach($linestrings as $ls){
            if( get_class($ls) != 'LorenzoGiust\GeoSpatial\LineString' )
                throw new GeoException("Error instantianting Linestring");
            foreach($ls->points as $point){
                if( get_class($point) != 'LorenzoGiust\GeoSpatial\Point' )
                    throw new GeoException("LineString does not contains Points");
            }
        }
    }

    public function testLineStringCircular()
    {
        $linestring1 = new LineString([new Point(1, 2), new Point(3, 4), new Point(5, 6)]);
        $linestring2 = new LineString([new Point(1, 2), new Point(3, 4), new Point(5, 6), new Point(1, 2)]);

        $this->assertFalse($linestring1->isCircular());
        $this->assertTrue($linestring2->isCircular());
    }

    public function testLineStringSplit()
    {
        $p1 = new Point(1,1);
        $p2 = new Point(2,2);
        $p3 = new Point(3,3);
        $p4 = new Point(4,4);
        $p5 = new Point(5,5);

        $linestring1 = new LineString([$p1, $p2, $p3, $p4, $p5]);
        $linestring2 = new LineString([$p1, $p2]);


        $splitted = $linestring1->split($p2);
        $this->assertEquals($splitted[0], $linestring2);
        $this->assertEquals($splitted[1], new LineString([ $p2, $p3, $p4, $p5] ));

        $splitted = $linestring1->split($p1);
        $this->assertEquals($splitted[0], $p1 );
        $this->assertEquals($splitted[1], $linestring1);

        $splitted = $linestring1->split($p5);
        $this->assertEquals($splitted[0], $linestring1);
        $this->assertEquals($splitted[1], $p5 );

        $splitted = $linestring2->split($p1);
        $this->assertEquals($splitted[0], $p1 );
        $this->assertEquals($splitted[1], $linestring2 );

        $splitted = $linestring2->split($p2);
        $this->assertEquals($splitted[0], $linestring2 );
        $this->assertEquals($splitted[1], $p2 );

        $splitted = $linestring2->split($p3);
        $this->assertEquals($splitted[0], $linestring2 );
        $this->assertEquals($splitted[1], null );

    }

    public function testPolygonSuccess()
    {
        $polygons = [];
        $p1 = new Point(1,1);
        $p2 = new Point(2,2);
        $p3 = new Point(3,3);
        $p4 = new Point(4,4);
        $p5 = new Point(5,5);

        $linestring1 = new LineString([$p1, $p2, $p3, $p4, $p5, $p1]);
        $linestring2 = new LineString([$p1, $p2, $p3, $p1]);
        $linestring3 = "1 2, 3 4, 5 6, 1 2";
        $linestring4 = "1 2: 3 4: 5 6: 1 2";
        $linestring5 = "1_2: 3_4: 5_6: 1_2";

        $polygons[] = new Polygon([$linestring1]);
        $polygons[] = new Polygon([$linestring1, $linestring2]);
        $polygons[] = new Polygon([$linestring3]);
        $polygons[] = new Polygon([$linestring3, $linestring3]);
        $polygons[] = new Polygon([$linestring4, $linestring4], ":");
        $polygons[] = new Polygon([$linestring5, $linestring5], ":", "_");

        foreach($polygons as $poly){
            if( get_class($poly) != 'LorenzoGiust\GeoSpatial\Polygon' )
                throw new GeoException("Error instantianting Polygon");
            foreach($poly->linestrings as $ls){
                if( get_class($ls) != 'LorenzoGiust\GeoSpatial\LineString' )
                    throw new GeoException("Error instantianting Linestring");
                foreach($ls->points as $point){
                    if( get_class($point) != 'LorenzoGiust\GeoSpatial\Point' )
                        throw new GeoException("LineString does not contains Points");
                }
            }
        }
    }

    /**
     * @expectedException \LorenzoGiust\GeoSpatial\Exceptions\GeoException
     * @expectedExceptionMessage A LineString instance that compose a Polygon must be circular (min 4 points, first and last equals).
     */
    public function testPolygonFails1()
    {
        $polygons = [];
        $p1 = new Point(1,1);
        $p2 = new Point(2,2);
        $p3 = new Point(3,3);
        $p4 = new Point(4,4);
        $p5 = new Point(5,5);

        $linestring1 = new LineString([$p1, $p2, $p3, $p4, $p5]);
        $poly = new Polygon([$linestring1]);
    }




}