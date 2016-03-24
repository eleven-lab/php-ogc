<?php

namespace LorenzoGiust\GeoSpatial;


use LorenzoGiust\GeoSpatial\Exceptions\GeoSpatialException;

class MultiPolygon extends GeoSpatialObject implements \Countable
{
    public $polygons = [];

    /**
     * MultiPolygon constructor.
     *
     *
     * 1) new MultiPolygon(array Polygon)
     *
     * You can instantiate a MultiPolygon directly with a Polygon array
     *
     * TODO: improve constructor with more signature
     *
     */
    public function __construct()
    {
        $arguments = func_get_args();

        if( sizeof($arguments) == 1 && is_array($arguments[0]) ) {
            $polygons = $arguments[0];
        }else{
            throw new GeoSpatialException('Missing constructor signature');
        }

        $this->polygons = $polygons;
    }

    /**
     * Implementazione dell'interfaccia countable
     *
     * @return int
     */
    public function count()
    {
        return count($this->polygons);
    }

}
