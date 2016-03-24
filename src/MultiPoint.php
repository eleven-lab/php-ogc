<?php

namespace LorenzoGiust\GeoSpatial;


use LorenzoGiust\GeoSpatial\Exceptions\GeoSpatialException;

class MultiPoint extends GeoSpatialObject implements \Countable
{
    public $points = [];

    /**
     * MultiPoint constructor.
     *
     *
     * 1) new MultiPoint(array Points)
     *
     * You can instantiate a MultiPoint directly with a Points array
     * es. [new Point(lat, lon), new Point(lat, lon)]
     *
     * 2) new MultiPoint(array $points)
     * from an array of pointarray
     * es. [[lat, lon], [lat, lon], [lat, lon], ..]
     *
     * 3) new MultiPoint(string $points, string $points_separator = ",", string $coordinates_separator = " ")
     *
     * By default a linestring could be instantiated using a string where points are divided by a "," and coordinates
     * are divided by " ". Separators must be different.
     * es. "lat lon, lat lon"
     *
     */
    public function __construct()
    {
        $arguments = func_get_args();

        if( sizeof($arguments) == 1 && is_array($arguments[0]) ){
            $points = array_map(function($p){
                if( sizeof($p) == 2 ){
                    return new Point($p[0], $p[1]);
                }elseif( $p instanceof Point ){
                    return $p;
                }else
                    throw new GeoSpatialException('A LineString instance should be constructed with Points array only.');
            }, $arguments[0]);

        }elseif( sizeof($arguments) == 1 && is_string($arguments[0]) ){
            $points = $this->parsePoints($arguments[0]);

        }elseif( sizeof($arguments) == 2 && is_string($arguments[0]) && is_string($arguments[1])){
            $points = $this->parsePoints($arguments[0], $arguments[1]);

        }elseif( sizeof($arguments) == 3 && is_string($arguments[0]) && is_string($arguments[1]) && is_string($arguments[2])){
            if($arguments[0] == $arguments[1])
                throw new GeoSpatialException('Error - Points and coordinates separators cannot be equals');

            $points = $this->parsePoints($arguments[0], $arguments[1], $arguments[2]);
        }else
            throw new GeoSpatialException('Cannot instantiate LineString object, wrong arguments');

        if( sizeof($points) < 2 )
            throw new GeoSpatialException("A LineString instance must be composed by at least 2 points.");

        $this->points = $points;
    }

    private function parsePoints($points, $points_separator = ",", $coords_separator = " ")
    {
        return array_map(function($p) use ($coords_separator){
            return new Point($p, [$coords_separator]);
        }, explode($points_separator, trim($points)));
    }


    /**
     * Implementazione dell'interfaccia countable
     *
     * @return int
     */
    public function count()
    {
        return count($this->points);
    }

}
