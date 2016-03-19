<?php

namespace LorenzoGiust\GeoSpatial;

use LorenzoGiust\GeoSpatial\Exceptions\GeoException;


/**
 * Class LineString
 * @package App
 */
class LineString extends GeoSpatial implements \Countable
{
    /**
     * @var array
     */
    public $points = [];

    /**
     * LineString constructor.
     *
     *
     * 1) new LineString(array Points)
     *
     * You can instantiate a LineString directly with a Points array
     * es. [new Point(lat, lon), new Point(lat, lon)]
     *
     * 2) new LineString(array $points)
     * from an array of pointarray
     * es. [[lat, lon], [lat, lon], [lat, lon], ..]
     *
     * 3) new LineString(string $points, string $points_separator = ",", string $coordinates_separator = " ")
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
                    throw new GeoException('A LineString instance should be constructed with Points array only.');
            }, $arguments[0]);

        }elseif( sizeof($arguments) == 1 && is_string($arguments[0]) ){
            $points = $this->parsePoints($arguments[0]);

        }elseif( sizeof($arguments) == 2 && is_string($arguments[0]) && is_string($arguments[1])){
            $points = $this->parsePoints($arguments[0], $arguments[1]);

        }elseif( sizeof($arguments) == 3 && is_string($arguments[0]) && is_string($arguments[1]) && is_string($arguments[2])){
            if($arguments[0] == $arguments[1])
                throw new GeoException('Error - Points and coordinates separators cannot be equals');

            $points = $this->parsePoints($arguments[0], $arguments[1], $arguments[2]);
        }else
            throw new GeoException('Cannot instantiate LineString object, wrong arguments');

        if( sizeof($points) < 2 )
            throw new GeoException("A LineString instance must be composed by at least 2 points.");

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

    /**
     * Check if the LineString is circular, that is the first and the last Point are the same.
     *
     * @return bool
     */
    public function isCircular()
    {
        return count($this->points) > 3 &&  $this->points[0] == $this->points[ count($this->points) -1 ];
    }


    /**
     * Return the lenght of the linestring expressed in meters.
     *
     * @return int
     */
    public function lenght($provider = "haversine")
    {
        $length = 0;
        for($i = 0; $i < count($this)-2 ; $i++ ){
            $length += Point::distance($this->points[$i], $this->points[$i+1], $provider);
        }
        return $length;

    }


    /*
     * Insert a Point into the LineString,
     *
     *
     * Per ogni step p1-p2 dei punti che compongono la LineString, calcolo d1=d(p,p1) e d2=d(p,p2).
     * Seleziono lo step come candidato all'inserimento se d1+d2 <= d(p1,p2)*soglia_di_tolleranza.
     *
     * TODO: aggiungere condizione più forte oltre a quella con la coppia di punti in osservazione, es. quella con
     *      coppie di punti adiacenti
     * TODO: valutare implementazione di ricerca binaria
     *
     * @param Point $p
     *
     */
    public function insertPoint(Point $p)
    {
         //echo "aggiungo punto p:$p\n";
         //echo "lunghezz attuale ls: " . count($this)."\n";
         // Se il punto è già presente nella LineString non faccio modifiche
         if(array_search($p, $this->points) !== false){
             //echo "punto già presente";
             return;
         }

        $threshold = 0.02;
        for( $i = 0 ; $i < count($this->points) - 2 ; $i++ ) {

            //echo "valuto $i-esima coppia di punti: " .$this->points[$i]. " " .$this->points[$i+1]. "\n";

            $distance       = Point::distance($this->points[$i], $p) + Point::distance($p, $this->points[$i + 1]);
            $step_distance  = Point::distance($this->points[$i], $this->points[$i + 1]);

            //echo "distanza totale: \t\t $distance\n";
            //echo "step distance: \t\t\t $step_distance\n";

            if( $distance > $step_distance - ($step_distance*$threshold) && $distance < ($step_distance + $step_distance*$threshold) ){
                $newpoints = array_slice( $this->points, 0, $i+1, true );
                $newpoints = array_merge( $newpoints, [$p] );
                $newpoints = array_merge( $newpoints, array_slice($this->points, $i+1, count($this->points) - ($i+1)) );
                $this->points = $newpoints;
                break;

            }
        }

         //echo "lunghezza finale ls: " . count($this)."\n";
    }


    /**
     * Split the LineString object on the given Point, if present, and returns a tuple (array with two object) composed
     * by Points, LineStrings or null..
     *
     * If the given Point is the first of the LineString, it returns the Point as first element of the tuple, and the
     * remaining LineString as second element of the tuple. Same behavior if the Point is the last, but the returned
     * tuple is reversed.
     *
     * If the LineString is composed by two Points and the given Point is one of those, we get a tuple of two Points.
     *
     * If the Point is not present, we get a tuple composed by the LineString itself and a null element in the second
     * position of the tuple.
     *
     *
     * es.
     * given the LineString(Point(1,1), Point(2,2), Point(3,3), Point(4,4), Point(5,5)) split by Point(2,2) we obtain
     * - an array with two LineString
     * 1) LineString(Point(1,1), Point(2,2))
     * 2) LineString(Point(2,2), Point(3,3), Point(4,4), Point(5,5))
     *
     * es.
     * given the LineString(Point(1,1), Point(2,2), Point(3,3), Point(4,4), Point(5,5)) split by Point(1,1) we obtain
     * - an array with a Point and a LineString
     * 1) Point(1,1)
     * 2) LineString(Point(1,1), Point(2,2), Point(3,3), Point(4,4), Point(5,5))
     *
     * es.
     * given the LineString(Point(1,1), Point(2,2), Point(3,3), Point(4,4), Point(5,5)) split by Point(5,5) we obtain
     * - an array with a Point and a LineString
     * 1) LineString(Point(1,1), Point(2,2), Point(3,3), Point(4,4), Point(5,5))
     * 2) Point(5,5)
     *
     * es.
     * given the LineString(Point(1,1), Point(2,2)) split by Point(1,1) we obtain
     * - an array with a Point and a LineString
     * 1) Point(1,1)
     * 2) LineString(Point(1,1), Point(2,2))
     *
     * es.
     * given the LineString(Point(1,1), Point(2,2)) split by Point(5,5) we obtain
     * - an array with a LineString and a null element
     * 1) LineString(Point(1,1), Point(2,2))
     * 2) null
     *
     * @param $split
     * @return array
     */
    public function split(Point $split)
    {

        // if the point split is the first or the last, we returns the whole linestring
        reset($this->points);
        if( $this->points[0] == $split){
            return [$split, $this];
        }

        if ($this->points[count($this->points)-1] == $split){
            return [$this, $split];
        }

        $splitted = [];
        $position = array_search($split, $this->points);

        // if the split point is not found, we return the whole linestring
        if($position === false){
            return [$this, null];
        }

        else{
            array_push( $splitted, new LineString(array_slice($this->points, 0, $position+1)) );
            array_push( $splitted, new LineString(array_slice($this->points, $position, count($this->points) - $position ) ) );
        }
        return $splitted;
    }


    /**
     * @param LineString $l1
     * @param LineString $l2
     * @return array Point
     */
    public static function diff(LineString $l1, LineString $l2)
    {
        $diffs = array_diff($l1->points, $l2->points);
        foreach($diffs as $diff){
            $pos = array_search($diff, $l1->points);
            echo "Il point $diff è presente solo nella prima LineString, in posizione $pos\n";
        }
        return $diffs;
    }

}