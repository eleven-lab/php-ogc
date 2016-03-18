<?php

namespace LorenzoGiust\GeoSpatial;


use LorenzoGiust\GeoSpatial\Exceptions\GeoException;

class Polygon implements \Countable
{

    public $linestrings = [];


    /**
     * Polygon constructor.
     *
     * Could be constructed with:
     * 1) new Polygon(array LineString)
     * 2) new Polygon(array string, string points_separator, string coords_separator )
     *
     *
     */
    public function __construct()
    {
        $arguments = func_get_args();

        if( sizeof($arguments) >= 1 && is_array($arguments[0]) )
            $arraType = $this->determineArrayType($arguments[0]);
        else
            throw new GeoException('Wrong parameters for Polygon instantiation.');

        //strings
        if( $arraType == 0 ){
            foreach($arguments[0] as $linestring){
                if( !isset($arguments[1]) && !isset($arguments[2]) )
                    $this->linestrings[] = new LineString($linestring);

                elseif(isset($arguments[1]) && !isset($arguments[2]))
                    $this->linestrings[] = new LineString($linestring, $arguments[1]);

                elseif(isset($arguments[1]) && isset($arguments[2]))
                    $this->linestrings[] = new LineString($linestring, $arguments[1], $arguments[2]);

                else
                    $this->linestrings[] = new LineString($linestring, $arguments[1], $arguments[2]); // should throw exception

            }
            // linestrings
        }elseif( $arraType == 1 ){
            $this->linestrings = $arguments[0];

        }else{
            throw new GeoException('Wrong parameters for Polygon instantiation.');
        }

        if( count($this->linestrings) == 0 )
            throw new \Exception("A Polygon instance must be composed by at least 1 linestring.");

        array_walk($this->linestrings, [$this, "is_circular_linestring"]);

    }

    /**
     * Determine the type of array elements. Returns:
     * 0    all elements are strings
     * 1    all elements are LineStrings
     * -1   else
     *
     * @param array $array
     * @return int
     */
    private function determineArrayType(array $array)
    {
        $strings = false;
        $linestrings = false;

        foreach($array as $element) {
            if ($element instanceof LineString) $linestrings = true;
            elseif (is_string($element)) $strings = true;
            else return -1;
        }

        return ! ( $strings xor $linestrings ) ? -1 : ( $strings ? 0 : 1 );
    }

    private function is_circular_linestring($linestring){
        if( ! $linestring instanceof LineString)
            throw new GeoException("A Polygon instance must be composed by LineString only.");

        if( ! $linestring->isCircular() )
            throw new GeoException("A LineString instance that compose a Polygon must be circular (min 4 points, first and last equals).");
    }

    public function count(){
        return count($this->linestrings);
    }
}