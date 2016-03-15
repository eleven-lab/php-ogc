<?php

namespace LorenzoGiust\LaravelGoogleMapsAPI\GeoSpatial;


class Polygon implements \Countable
{

    public $linestrings = [];


    /**
     * Polygon constructor.
     *
     * Could be constructed with:
     * - LineStrings array
     * - 
     */
    public function __construct()
    {
        $arguments = func_get_args();

    }


    /**
     * @param array LineString[]
     */
    public function __construct(array $linestrings)
    {

        if( count($linestrings) == 0 )
            throw new \Exception("A Polygon instance must be composed by at least 1 linestring.");

        array_walk($linestrings, [$this, "is_circular_linestring"]);


        $this->linestrings = $linestrings;

    }

    private function is_circular_linestring($linestring){
        if( ! $linestring instanceof LineString)
            throw new \Exception("A Polygon instance must be composed by LineString only.");

        if( ! $linestring->circular() )
            throw new \Exception("A LineString instance that compose a Polygon must be circular.");

    }

    public function count(){
        return count($this->linestrings);
    }


    /**
     * Importa un polygon con una stringa del tipo "lat lon, lat lon, ...."
     *
     * @param $string
     * @return Polygon
     */
    public static function import(array $linestrings){

        // TODO: controllo integrità dati in input
        // TODO: prevedere import di più linestring

        $ls = [];

        foreach($linestrings as $linestring){
            $tmp_points = explode(",", $linestring);
            $points = [];
            foreach($tmp_points as $point){
                $points[] = Point::import($point);
            }
            $ls[] = new LineString($points);
        }

        return new Polygon($ls);
    }

    /**
     * Importa un polygon con una stringa del tipo "lat lon, lat lon, ...."
     *
     * @param $string
     * @return Polygon
     */
    public static function importFromText($string){
        $tmp = substr(substr($string, 8), 0, -1); // ELIMINO POLYGON(...)
        $re = "/(?:([^()]+),?)*/";
        preg_match_all($re, $tmp, $matches)[0];
        $tmp = array_filter($matches[0], function($var){ return $var != "" && $var != ","; });

        return self::import($tmp);
    }

}